<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Person;
use Illuminate\Support\Facades\DB;

class PersonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $persons = Person::with('groups')->get();
        return view('persons.index', compact('persons'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('persons.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Person::create($validated);

        return redirect()->route('persons.index')
            ->with('success', 'Personne créée avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Person $person)
    {
        $person->load('groups');
        return view('persons.show', compact('person'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Person $person)
    {
        return view('persons.edit', compact('person'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Person $person)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $person->update($validated);

        return redirect()->route('persons.index')
            ->with('success', 'Personne mise à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Person $person)
    {
        // Vérifier si la personne appartient à un groupe
        if ($person->groups()->count() > 0) {
            return redirect()->route('persons.index')
                ->with('error', 'Impossible de supprimer une personne qui appartient à un groupe. Veuillez la retirer de tous ses groupes d\'abord.');
        }

        $person->delete();

        return redirect()->route('persons.index')
            ->with('success', 'Personne supprimée avec succès.');
    }

    /**
     * Rejoindre un groupe
     */
    public function joinGroup(Request $request, Person $person)
    {
        $validated = $request->validate([
            'group_id' => 'required|exists:groups,id',
        ]);

        // Vérifier si la personne n'est pas déjà dans le groupe
        if ($person->groups()->where('group_id', $validated['group_id'])->exists()) {
            return redirect()->back()
                ->with('error', 'Cette personne est déjà dans ce groupe.');
        }

        // Ajouter la personne au groupe avec un solde initial de 0
        $person->groups()->attach($validated['group_id'], ['balance' => 0]);

        // Mettre à jour le total_balance de la personne
        $person->update([
            'total_balance' => $person->calculateTotalBalance()
        ]);

        // Mettre à jour le total_budget du groupe
        $group = \App\Models\Group::find($validated['group_id']);
        $group->update([
            'total_budget' => $group->calculateTotalBudget()
        ]);

        return redirect()->back()
            ->with('success', 'Groupe rejoint avec succès!');
    }

    /**
     * Restore a soft deleted person
     */
    public function restore($id)
    {
        $person = Person::withTrashed()->findOrFail($id);
        $person->restore();

        return redirect()->route('persons.index')
            ->with('success', 'Personne restaurée avec succès.');
    }

    /**
     * Permanently delete a person
     */
    public function forceDestroy($id)
    {
        $person = Person::withTrashed()->findOrFail($id);

        // Vérifier que la personne a bien été soft deleted
        if (!$person->trashed()) {
            return redirect()->route('persons.index')
                ->with('error', 'La personne doit d\'abord être supprimée (soft delete) avant de pouvoir être supprimée définitivement.');
        }

        // Vérifier si la personne a des fonds
        if ($person->floating_balance != 0) {
            return redirect()->route('persons.index')
                ->with('error', 'Impossible de supprimer définitivement une personne qui a des fonds (solde flottant: ' . number_format($person->floating_balance, 2) . ' €).');
        }

        $person->forceDelete();

        return redirect()->route('persons.index')
            ->with('success', 'Personne supprimée définitivement.');
    }

    /**
     * Add funds to person's floating balance
     */
    public function addFloatingFunds(Request $request, Person $person)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $amount = $validated['amount'];

        // Ajouter les fonds
        $person->floating_balance += $amount;
        $person->save();

        return redirect()->back()
            ->with('success', 'Ajout de ' . number_format($amount, 2) . ' € effectué avec succès. Nouveau solde flottant: ' . number_format($person->floating_balance, 2) . ' €');
    }

    /**
     * Withdraw funds from person's floating balance
     */
    public function withdrawFunds(Request $request, Person $person)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $amount = $validated['amount'];

        // Vérifier que la personne a suffisamment de fonds flottants
        if ($person->floating_balance < $amount) {
            return redirect()->back()
                ->with('error', 'Fonds insuffisants. Solde flottant actuel: ' . number_format($person->floating_balance, 2) . ' €');
        }

        // Retirer les fonds
        $person->floating_balance -= $amount;
        $person->save();

        return redirect()->back()
            ->with('success', 'Retrait de ' . number_format($amount, 2) . ' € effectué avec succès. Nouveau solde flottant: ' . number_format($person->floating_balance, 2) . ' €');
    }

    /**
     * Get transactions data for DataTables
     */
    public function getTransactionsData(Person $person, Request $request)
    {
        $query = DB::table('games')
            ->join('groups', 'games.group_id', '=', 'groups.id')
            ->join('group_person', 'groups.id', '=', 'group_person.group_id')
            ->where('group_person.person_id', $person->id)
            ->select(
                'games.id',
                'games.game_date',
                'groups.name as group_name',
                'groups.id as group_id',
                'games.amount',
                'games.cost_per_person',
                'games.winnings',
                'games.is_winner',
                'games.created_at'
            )
            ->orderBy('games.game_date', 'desc');

        // Search
        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('groups.name', 'like', "%{$search}%")
                  ->orWhere('games.game_date', 'like', "%{$search}%");
            });
        }

        // Total records
        $totalRecords = DB::table('games')
            ->join('groups', 'games.group_id', '=', 'groups.id')
            ->join('group_person', 'groups.id', '=', 'group_person.group_id')
            ->where('group_person.person_id', $person->id)
            ->count();

        $filteredRecords = $query->count();

        // Ordering
        if ($request->has('order')) {
            $orderColumn = $request->order[0]['column'];
            $orderDir = $request->order[0]['dir'];
            
            $columns = ['games.game_date', 'groups.name', 'games.amount', 'games.cost_per_person', 'games.winnings'];
            if (isset($columns[$orderColumn])) {
                $query->orderBy($columns[$orderColumn], $orderDir);
            }
        }

        // Pagination
        $start = $request->start ?? 0;
        $length = $request->length ?? 10;
        $data = $query->skip($start)->take($length)->get();

        // Format data
        $formattedData = $data->map(function($game) use ($person) {
            $personsCount = DB::table('group_person')
                ->where('group_id', $game->group_id)
                ->count();
            
            $winningsPerPerson = $game->is_winner && $personsCount > 0 
                ? $game->winnings / $personsCount 
                : 0;
            
            $impact = $game->is_winner 
                ? $winningsPerPerson - $game->cost_per_person
                : -$game->cost_per_person;

            return [
                'game_date' => \Carbon\Carbon::parse($game->game_date)->format('d/m/Y'),
                'group_name' => $game->group_name,
                'group_id' => $game->group_id,
                'amount' => number_format($game->amount, 2) . '€',
                'cost_per_person' => number_format($game->cost_per_person, 2) . '€',
                'winnings' => $game->is_winner ? number_format($game->winnings, 2) . '€' : '-',
                'winnings_per_person' => $game->is_winner ? number_format($winningsPerPerson, 2) . '€' : '-',
                'impact' => number_format($impact, 2) . '€',
                'impact_value' => $impact,
                'is_winner' => $game->is_winner,
            ];
        });

        // Calculate totals for all transactions
        $allGames = DB::table('games')
            ->join('groups', 'games.group_id', '=', 'groups.id')
            ->join('group_person', 'groups.id', '=', 'group_person.group_id')
            ->where('group_person.person_id', $person->id)
            ->select('games.*', 'groups.id as group_id')
            ->get();

        $totalAmount = $allGames->sum('amount');
        $totalCostPerPerson = $allGames->sum('cost_per_person');
        $totalWinnings = $allGames->where('is_winner', true)->sum('winnings');
        
        $totalWinningsPerPerson = 0;
        $totalImpact = 0;
        
        foreach ($allGames as $game) {
            $personsCount = DB::table('group_person')
                ->where('group_id', $game->group_id)
                ->count();
            
            $winningsPerPerson = $game->is_winner && $personsCount > 0 
                ? $game->winnings / $personsCount 
                : 0;
            
            $totalWinningsPerPerson += $winningsPerPerson;
            
            $impact = $game->is_winner 
                ? $winningsPerPerson - $game->cost_per_person
                : -$game->cost_per_person;
            
            $totalImpact += $impact;
        }

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $formattedData,
            'totals' => [
                'amount' => number_format($totalAmount, 2) . '€',
                'cost_per_person' => number_format($totalCostPerPerson, 2) . '€',
                'winnings' => number_format($totalWinnings, 2) . '€',
                'winnings_per_person' => number_format($totalWinningsPerPerson, 2) . '€',
                'impact' => number_format($totalImpact, 2) . '€',
                'impact_value' => $totalImpact
            ]
        ]);
    }
}
