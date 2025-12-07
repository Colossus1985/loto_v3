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
        $group->load('persons');
        return view('groups.show', compact('group'));
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
            return [
                'game_date' => $game->game_date->format('d/m/Y'),
                'amount' => number_format($game->amount, 2) . '€',
                'cost_per_person' => number_format($game->cost_per_person, 2) . '€',
                'winnings' => $game->is_winner ? number_format($game->winnings, 2) . '€' : '-',
                'winnings_per_person' => $game->is_winner ? number_format($game->winnings / $group->persons->count(), 2) . '€' : '-',
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
        $totalCostPerPerson = $personsCount > 0 ? $allGames->sum('cost_per_person') : 0;
        $totalWinningsPerPerson = $personsCount > 0 && $totalWinnings > 0 
            ? $allGames->where('is_winner', true)->sum(function($game) use ($personsCount) {
                return $game->winnings / $personsCount;
            })
            : 0;

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
        $group->delete();

        return redirect()->route('groups.index')
            ->with('success', 'Groupe supprimé avec succès.');
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

        $group->persons()->attach($validated['person_id'], ['balance' => 0]);

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
            
            // Transférer le solde vers le floating_balance
            $person->update([
                'floating_balance' => $person->floating_balance + $balanceToTransfer
            ]);
            
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

        // Mettre à jour le total_balance de la personne
        $person->update([
            'total_balance' => $person->calculateTotalBalance()
        ]);

        // Mettre à jour le total_budget du groupe
        $group->update([
            'total_budget' => $group->calculateTotalBudget()
        ]);

        return redirect()->back()
            ->with('success', "Fonds ajoutés avec succès! {$person->name} : +{$validated['amount']}€");
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
        $person->update([
            'floating_balance' => $person->floating_balance - $validated['amount']
        ]);

        // Ajouter au solde dans le groupe
        $currentBalance = $group->persons()->where('person_id', $person->id)->first()->pivot->balance;
        $newBalance = $currentBalance + $validated['amount'];

        $group->persons()->updateExistingPivot($person->id, [
            'balance' => $newBalance
        ]);

        // Mettre à jour le total_balance de la personne
        $person->update([
            'total_balance' => $person->calculateTotalBalance()
        ]);

        // Mettre à jour le total_budget du groupe
        $group->update([
            'total_budget' => $group->calculateTotalBudget()
        ]);

        return redirect()->back()
            ->with('success', "Transfert effectué : {$validated['amount']}€ du budget flottant vers le groupe.");
    }
}
