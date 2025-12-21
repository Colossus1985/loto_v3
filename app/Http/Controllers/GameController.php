<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\Game;
use Illuminate\Support\Facades\DB;

class GameController extends Controller
{
    /**
     * Afficher le formulaire pour jouer
     */
    public function create(Group $group)
    {
        $group->load('persons');
        return view('games.create', compact('group'));
    }

    /**
     * Enregistrer un jeu et débiter les personnes du groupe
     */
    public function store(Request $request, Group $group)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'game_date' => 'required|date',
        ]);

        $personCount = $group->persons()->count();

        if ($personCount == 0) {
            return redirect()->back()
                ->with('error', 'Aucune personne dans le groupe. Impossible de jouer.');
        }

        $costPerPerson = $validated['amount'] / $personCount;

        DB::transaction(function () use ($group, $validated, $costPerPerson, $personCount) {
            // Créer l'enregistrement du jeu
            $game = Game::create([
                'group_id' => $group->id,
                'amount' => $validated['amount'],
                'cost_per_person' => $costPerPerson,
                'game_date' => $validated['game_date'],
            ]);

            // Débiter chaque personne du groupe
            foreach ($group->persons as $person) {
                $currentBalance = $person->pivot->balance;
                $newBalance = $currentBalance - $costPerPerson;

                $group->persons()->updateExistingPivot($person->id, [
                    'balance' => $newBalance
                ]);
                
                $totalBalanceBefore = $person->total_balance;

                // Mettre à jour le total_balance de la personne
                $person->update([
                    'total_balance' => $person->calculateTotalBalance()
                ]);
                
                // Enregistrer la transaction
                $person->logTransaction(
                    'game_played',
                    -$costPerPerson,
                    $totalBalanceBefore,
                    $person->total_balance,
                    'group',
                    "Jeu joué dans le groupe {$group->name} - Mise: {$validated['amount']}€",
                    $group->id
                );
            }

            // Mettre à jour le total_budget du groupe
            $group->update([
                'total_budget' => $group->calculateTotalBudget()
            ]);
        });

        return redirect()->route('groups.show', $group)
            ->with('success', "Jeu enregistré! Montant: {$validated['amount']}€, Coût par personne: " . number_format($costPerPerson, 2) . "€");
    }

    /**
     * Afficher le formulaire pour enregistrer un gain
     */
    public function showWinForm(Game $game)
    {
        $game->load('group.persons');
        return view('games.win', compact('game'));
    }

    /**
     * Enregistrer un gain et créditer les personnes du groupe
     */
    public function recordWin(Request $request, Game $game)
    {
        if ($game->is_winner) {
            return redirect()->back()
                ->with('error', 'Ce jeu a déjà un gain enregistré.');
        }

        $validated = $request->validate([
            'winnings' => 'required|numeric|min:0.01',
        ]);

        $group = $game->group;
        $personCount = $group->persons()->count();

        if ($personCount == 0) {
            return redirect()->back()
                ->with('error', 'Aucune personne dans le groupe.');
        }

        $winningsPerPerson = $validated['winnings'] / $personCount;

        DB::transaction(function () use ($game, $group, $validated, $winningsPerPerson) {
            // Mettre à jour le jeu avec les gains
            $game->update([
                'winnings' => $validated['winnings'],
                'is_winner' => true,
            ]);

            // Créditer chaque personne du groupe
            foreach ($group->persons as $person) {
                $currentBalance = $person->pivot->balance;
                $newBalance = $currentBalance + $winningsPerPerson;

                $group->persons()->updateExistingPivot($person->id, [
                    'balance' => $newBalance
                ]);
                
                $totalBalanceBefore = $person->total_balance;

                // Mettre à jour le total_balance de la personne
                $person->update([
                    'total_balance' => $person->calculateTotalBalance()
                ]);
                
                // Enregistrer la transaction
                $person->logTransaction(
                    'game_won',
                    $winningsPerPerson,
                    $totalBalanceBefore,
                    $person->total_balance,
                    'group',
                    "Gain dans le groupe {$group->name} - Total gains: {$validated['winnings']}€",
                    $group->id
                );
            }

            // Mettre à jour le total_budget et total_winnings du groupe
            $group->update([
                'total_budget' => $group->calculateTotalBudget(),
                'total_winnings' => $group->total_winnings + $validated['winnings']
            ]);
        });

        return redirect()->route('groups.show', $group)
            ->with('success', "Gain enregistré! Montant: {$validated['winnings']}€, Gain par personne: " . number_format($winningsPerPerson, 2) . "€");
    }

    /**
     * Supprimer un jeu et effectuer les corrections nécessaires
     */
    public function destroy(Game $game)
    {
        $group = $game->group;
        
        // Récupérer les personnes qui étaient dans le groupe au moment du jeu
        // On va utiliser les personnes actuelles du groupe pour simplifier
        $persons = $group->persons;
        
        if ($persons->count() == 0) {
            return redirect()->back()
                ->with('error', 'Impossible de supprimer ce jeu: aucune personne dans le groupe.');
        }

        DB::transaction(function () use ($game, $group, $persons) {
            $costPerPerson = $game->cost_per_person;
            $hasWinnings = $game->is_winner;
            $winningsPerPerson = $hasWinnings ? ($game->winnings / $persons->count()) : 0;

            // Traiter chaque personne
            foreach ($persons as $person) {
                // 1. Rembourser le coût du jeu en fonds flottants
                $floatingBalanceBefore = $person->floating_balance;
                $floatingBalanceAfter = $floatingBalanceBefore + $costPerPerson;
                
                $person->update([
                    'floating_balance' => $floatingBalanceAfter
                ]);

                // Enregistrer la transaction de correction pour le remboursement
                $person->logTransaction(
                    'correction',
                    $costPerPerson,
                    $floatingBalanceBefore,
                    $floatingBalanceAfter,
                    'floating',
                    "Correction: Remboursement du jeu supprimé (Groupe: {$group->name}, Date: {$game->game_date}, Mise: {$game->amount}€)",
                    null
                );

                // 2. Si le jeu avait des gains, les retirer des fonds flottants
                // NOTE: On ne retire PAS les gains des fonds flottants car ils n'y ont jamais été ajoutés
                // Les gains ont été crédités directement au balance du groupe

                // 3. Ajuster le balance du groupe en retirant le coût
                $balanceBeforeGroupAdjustment = $person->pivot->balance;
                $balanceAfterGroupAdjustment = $balanceBeforeGroupAdjustment + $costPerPerson;
                
                $group->persons()->updateExistingPivot($person->id, [
                    'balance' => $balanceAfterGroupAdjustment
                ]);

                // Enregistrer la transaction de correction pour l'ajustement du groupe
                $totalBalanceBefore = $person->total_balance;
                
                // Mettre à jour le total_balance de la personne
                $person->update([
                    'total_balance' => $person->calculateTotalBalance()
                ]);

                $person->logTransaction(
                    'correction',
                    $costPerPerson,
                    $totalBalanceBefore,
                    $person->total_balance,
                    'group',
                    "Correction: Ajustement du solde groupe suite à la suppression du jeu (Date: {$game->game_date}, Mise: {$game->amount}€)",
                    $group->id
                );

                // 4. Si le jeu avait des gains, les retirer aussi du balance du groupe
                if ($hasWinnings && $winningsPerPerson > 0) {
                    $balanceBeforeWinningsAdjustment = $person->pivot->balance;
                    $balanceAfterWinningsAdjustment = $balanceBeforeWinningsAdjustment - $winningsPerPerson;
                    
                    $group->persons()->updateExistingPivot($person->id, [
                        'balance' => $balanceAfterWinningsAdjustment
                    ]);

                    // Enregistrer la transaction de correction pour le retrait des gains du groupe
                    $totalBalanceBefore = $person->total_balance;
                    
                    // Mettre à jour le total_balance de la personne
                    $person->update([
                        'total_balance' => $person->calculateTotalBalance()
                    ]);

                    $person->logTransaction(
                        'correction',
                        -$winningsPerPerson,
                        $totalBalanceBefore,
                        $person->total_balance,
                        'group',
                        "Correction: Retrait des gains du groupe suite à la suppression du jeu (Date: {$game->game_date}, Gains: {$game->winnings}€)",
                        $group->id
                    );
                }
            }

            // Mettre à jour les totaux du groupe
            $group->update([
                'total_budget' => $group->calculateTotalBudget(),
                'total_winnings' => $hasWinnings ? ($group->total_winnings - $game->winnings) : $group->total_winnings
            ]);

            // Supprimer le jeu
            $game->delete();
        });

        return redirect()->route('groups.show', $group)
            ->with('success', 'Jeu supprimé avec succès. Les corrections ont été appliquées aux comptes des participants.');
    }
}
