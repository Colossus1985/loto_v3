<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\Person;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $groups = Group::withCount('persons')->with('persons')->get();
        return view('groups.index', compact('groups'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('groups.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Group::create($validated);

        return redirect()->route('groups.index')
            ->with('success', 'Groupe créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Group $group)
    {
        // Rafraîchir la relation persons depuis la base de données
        $group->load('persons');
        $group->refresh();
        $group->load('persons');
        
        // Récupérer les personnes qui ne sont pas dans ce groupe
        $personIdsInGroup = $group->persons->pluck('id')->toArray();
        $availablePersons = Person::whereNotIn('id', $personIdsInGroup)->get();
        
        // Récupérer les transactions liées au groupe
        $transactions = \App\Models\PersonTransaction::where('group_id', $group->id)
            ->with('person')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('groups.show', compact('group', 'availablePersons', 'transactions'));
    }

    /**
     * Get games data for DataTable (server-side)
     */
    public function getGamesData(Group $group, Request $request)
    {
        $query = $group->games()->with('group');

        // Handle search
        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->whereDate('game_date', 'like', "%{$search}%")
                  ->orWhere('amount', 'like', "%{$search}%")
                  ->orWhere('winnings', 'like', "%{$search}%");
            });
        }

        // Total records
        $totalRecords = $group->games()->count();
        $filteredRecords = $query->count();

        // Handle ordering
        if ($request->has('order')) {
            $orderColumn = $request->order[0]['column'];
            $orderDir = $request->order[0]['dir'];
            
            $columns = ['game_date', 'amount', 'cost_per_person', 'winnings', 'is_winner'];
            if (isset($columns[$orderColumn])) {
                $query->orderBy($columns[$orderColumn], $orderDir);
            }
        } else {
            $query->orderBy('game_date', 'desc');
        }

        // Handle pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $games = $query->skip($start)->take($length)->get();

        // Format data
        $data = $games->map(function($game) use ($group) {
            $personsCount = $group->persons->count();
            $winningsPerPerson = ($game->is_winner && $personsCount > 0) 
                ? number_format($game->winnings / $personsCount, 2) . '€' 
                : '-';
            
            return [
                'game_date' => $game->game_date->format('d/m/Y'),
                'amount' => number_format($game->amount, 2) . '€',
                'cost_per_person' => number_format($game->cost_per_person, 2) . '€',
                'winnings' => $game->is_winner ? number_format($game->winnings, 2) . '€' : '-',
                'winnings_per_person' => $winningsPerPerson,
                'status' => $game->is_winner ? 'Gagné' : 'Pas gagné',
                'is_winner' => $game->is_winner,
                'actions' => $game->is_winner ? '' : route('games.win', $game),
                'game_id' => $game->id
            ];
        });

        // Calculate totals for all games (not just current page)
        $allGames = $group->games;
        $totalAmount = $allGames->sum('amount');
        $totalWinnings = $allGames->where('is_winner', true)->sum('winnings');
        $personsCount = $group->persons->count();
        
        // Toujours afficher le coût historique total par personne (somme des cost_per_person de chaque jeu)
        $totalCostPerPerson = $allGames->sum('cost_per_person');
        
        // Pour les gains par personne, calculer en fonction du nombre de personnes actuel s'il y en a
        // Sinon, utiliser la somme des gains divisés par le nombre de personnes au moment du jeu
        $totalWinningsPerPerson = 0;
        if ($totalWinnings > 0) {
            foreach ($allGames->where('is_winner', true) as $game) {
                // Calculer combien de personnes étaient dans le groupe lors de ce jeu
                $personsAtGameTime = $game->amount > 0 ? $game->amount / $game->cost_per_person : 0;
                if ($personsAtGameTime > 0) {
                    $totalWinningsPerPerson += $game->winnings / $personsAtGameTime;
                }
            }
        }

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
            'totals' => [
                'amount' => number_format($totalAmount, 2) . '€',
                'cost_per_person' => number_format($totalCostPerPerson, 2) . '€',
                'winnings' => number_format($totalWinnings, 2) . '€',
                'winnings_per_person' => number_format($totalWinningsPerPerson, 2) . '€'
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Group $group)
    {
        return view('groups.edit', compact('group'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Group $group)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $group->update($validated);

        return redirect()->route('groups.index')
            ->with('success', 'Groupe mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Group $group)
    {
        // Vérifier si le groupe a des personnes
        if ($group->persons()->count() > 0) {
            return redirect()->route('groups.index')
                ->with('error', 'Impossible de supprimer un groupe qui contient des personnes. Veuillez retirer toutes les personnes du groupe d\'abord.');
        }

        $group->delete();

        return redirect()->route('groups.index')
            ->with('success', 'Groupe supprimé avec succès.');
    }

    /**
     * Restore a soft deleted group
     */
    public function restore($id)
    {
        $group = Group::withTrashed()->findOrFail($id);
        $group->restore();

        return redirect()->route('groups.index')
            ->with('success', 'Groupe restauré avec succès.');
    }

    /**
     * Permanently delete a group
     */
    public function forceDestroy($id)
    {
        $group = Group::withTrashed()->findOrFail($id);

        // Vérifier si le groupe a des personnes
        if ($group->persons()->count() > 0) {
            return redirect()->route('groups.index')
                ->with('error', 'Impossible de supprimer définitivement un groupe qui contient des personnes. Veuillez retirer toutes les personnes du groupe d\'abord.');
        }

        $group->forceDelete();

        return redirect()->route('groups.index')
            ->with('success', 'Groupe supprimé définitivement.');
    }

    /**
     * Ajouter une personne au groupe
     */
    public function addPerson(Request $request, Group $group)
    {
        $validated = $request->validate([
            'person_id' => 'required|exists:persons,id',
        ]);

        if ($group->persons()->where('person_id', $validated['person_id'])->exists()) {
            return redirect()->back()
                ->with('error', 'Cette personne est déjà dans le groupe.');
        }

        $person = Person::find($validated['person_id']);
        $group->persons()->attach($validated['person_id'], ['balance' => 0]);

        // Enregistrer la transaction
        $person->logTransaction(
            'join_group',
            0,
            $person->total_balance,
            $person->total_balance,
            'group',
            "Adhésion au groupe {$group->name}",
            $group->id
        );

        return redirect()->back()
            ->with('success', 'Personne ajoutée au groupe avec succès.');
    }

    /**
     * Retirer une personne du groupe
     */
    public function removePerson(Group $group, Person $person)
    {
        // Récupérer le solde de la personne dans le groupe
        $personInGroup = $group->persons()->where('person_id', $person->id)->first();
        
        if ($personInGroup) {
            $balanceToTransfer = $personInGroup->pivot->balance;
            $balanceBefore = $person->floating_balance;
            
            // Transférer le solde vers le floating_balance
            $person->update([
                'floating_balance' => $person->floating_balance + $balanceToTransfer
            ]);
            
            // Enregistrer la transaction
            if ($balanceToTransfer != 0) {
                $person->logTransaction(
                    'leave_group',
                    $balanceToTransfer,
                    $balanceBefore,
                    $person->floating_balance,
                    'floating',
                    "Départ du groupe {$group->name} - Transfert du solde vers flottant",
                    $group->id
                );
            } else {
                $person->logTransaction(
                    'leave_group',
                    0,
                    $person->total_balance,
                    $person->total_balance,
                    'group',
                    "Départ du groupe {$group->name}",
                    $group->id
                );
            }
            
            // Retirer la personne du groupe
            $group->persons()->detach($person->id);
            
            // Mettre à jour le total_balance de la personne
            $person->update([
                'total_balance' => $person->calculateTotalBalance()
            ]);
            
            // Mettre à jour le total_budget du groupe
            $group->update([
                'total_budget' => $group->calculateTotalBudget()
            ]);
            
            return redirect()->back()
                ->with('success', "Personne retirée du groupe. Solde de {$balanceToTransfer}€ transféré vers le budget flottant.");
        }

        return redirect()->back()
            ->with('error', 'Personne non trouvée dans le groupe.');
    }

    /**
     * Ajouter des fonds au solde d'une personne dans le groupe
     */
    public function addFunds(Request $request, Group $group, Person $person)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        // Vérifier que la personne est dans le groupe
        if (!$group->persons()->where('person_id', $person->id)->exists()) {
            return redirect()->back()
                ->with('error', 'Cette personne n\'est pas dans le groupe.');
        }

        // Récupérer le solde actuel
        $currentBalance = $group->persons()->where('person_id', $person->id)->first()->pivot->balance;
        $newBalance = $currentBalance + $validated['amount'];

        // Mettre à jour le solde dans la table pivot
        $group->persons()->updateExistingPivot($person->id, [
            'balance' => $newBalance
        ]);

        // Enregistrer la transaction
        $totalBalanceBefore = $person->total_balance;
        
        // Mettre à jour le total_balance de la personne
        $person->update([
            'total_balance' => $person->calculateTotalBalance()
        ]);
        
        $person->logTransaction(
            'add_group_funds',
            $validated['amount'],
            $totalBalanceBefore,
            $person->total_balance,
            'group',
            "Ajout de fonds dans le groupe {$group->name}",
            $group->id
        );

        // Mettre à jour le total_budget du groupe
        $group->update([
            'total_budget' => $group->calculateTotalBudget()
        ]);

        return redirect()->back()
            ->with('success', "Fonds ajoutés avec succès! {$person->display_name} : +{$validated['amount']}€");
    }

    /**
     * Transférer des fonds du floating_balance vers le groupe
     */
    public function transferFromFloating(Request $request, Group $group, Person $person)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        // Vérifier que la personne est dans le groupe
        if (!$group->persons()->where('person_id', $person->id)->exists()) {
            return redirect()->back()
                ->with('error', 'Cette personne n\'est pas dans le groupe.');
        }

        // Vérifier que la personne a assez de fonds flottants
        if ($person->floating_balance < $validated['amount']) {
            return redirect()->back()
                ->with('error', 'Fonds flottants insuffisants. Disponible : ' . number_format($person->floating_balance, 2) . '€');
        }

        // Déduire du floating_balance
        $floatingBalanceBefore = $person->floating_balance;
        $person->update([
            'floating_balance' => $person->floating_balance - $validated['amount']
        ]);
        
        // Enregistrer la transaction de retrait du flottant
        $person->logTransaction(
            'transfer_to_group',
            -$validated['amount'],
            $floatingBalanceBefore,
            $person->floating_balance,
            'floating',
            "Transfert vers le groupe {$group->name}",
            $group->id
        );

        // Ajouter au solde dans le groupe
        $currentBalance = $group->persons()->where('person_id', $person->id)->first()->pivot->balance;
        $newBalance = $currentBalance + $validated['amount'];
        $totalBalanceBefore = $person->total_balance;

        $group->persons()->updateExistingPivot($person->id, [
            'balance' => $newBalance
        ]);

        // Mettre à jour le total_balance de la personne
        $person->update([
            'total_balance' => $person->calculateTotalBalance()
        ]);
        
        // Enregistrer la transaction d'ajout au groupe
        $person->logTransaction(
            'transfer_to_group',
            $validated['amount'],
            $totalBalanceBefore,
            $person->total_balance,
            'group',
            "Transfert depuis le budget flottant vers le groupe {$group->name}",
            $group->id
        );

        // Mettre à jour le total_budget du groupe
        $group->update([
            'total_budget' => $group->calculateTotalBudget()
        ]);

        return redirect()->back()
            ->with('success', "Transfert effectué : {$validated['amount']}€ du budget flottant vers le groupe.");
    }

    /**
     * Retirer des fonds du solde d'une personne dans le groupe
     */
    public function withdrawFundsFromGroup(Request $request, Group $group, Person $person)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        // Vérifier que la personne est dans le groupe
        if (!$group->persons()->where('person_id', $person->id)->exists()) {
            return redirect()->back()
                ->with('error', 'Cette personne n\'est pas dans le groupe.');
        }

        // Récupérer le solde actuel
        $currentBalance = $group->persons()->where('person_id', $person->id)->first()->pivot->balance;

        // Vérifier que le solde est suffisant
        if ($currentBalance < $validated['amount']) {
            return redirect()->back()
                ->with('error', 'Solde insuffisant dans le groupe. Disponible : ' . number_format($currentBalance, 2) . '€');
        }

        $newBalance = $currentBalance - $validated['amount'];
        $totalBalanceBefore = $person->total_balance;

        // Mettre à jour le solde dans la table pivot
        $group->persons()->updateExistingPivot($person->id, [
            'balance' => $newBalance
        ]);

        // Mettre à jour le total_balance de la personne
        $person->update([
            'total_balance' => $person->calculateTotalBalance()
        ]);
        
        // Enregistrer la transaction
        $person->logTransaction(
            'withdraw_group_funds',
            -$validated['amount'],
            $totalBalanceBefore,
            $person->total_balance,
            'group',
            "Retrait de fonds du groupe {$group->name}",
            $group->id
        );

        // Mettre à jour le total_budget du groupe
        $group->update([
            'total_budget' => $group->calculateTotalBudget()
        ]);

        return redirect()->back()
            ->with('success', "Fonds retirés avec succès! {$person->display_name} : -{$validated['amount']}€");
    }
}
