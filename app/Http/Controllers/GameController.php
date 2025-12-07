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
            Game::create([
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

                // Mettre à jour le total_balance de la personne
                $person->update([
                    'total_balance' => $person->calculateTotalBalance()
                ]);
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

                // Mettre à jour le total_balance de la personne
                $person->update([
                    'total_balance' => $person->calculateTotalBalance()
                ]);
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
}
