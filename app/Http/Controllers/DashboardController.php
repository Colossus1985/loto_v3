<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\Person;
use App\Models\Game;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistiques par groupe (dépenses et gains par mois)
        $groupStats = Group::with(['games' => function($query) {
            $query->select('group_id', 'amount', 'winnings', 'is_winner', 'game_date')
                  ->orderBy('game_date');
        }])->get();

        // Préparer les données pour les graphiques
        $groupsData = [];
        foreach ($groupStats as $group) {
            $monthlyData = [];
            
            foreach ($group->games as $game) {
                $month = $game->game_date->format('Y-m');
                
                if (!isset($monthlyData[$month])) {
                    $monthlyData[$month] = ['spent' => 0, 'won' => 0];
                }
                
                $monthlyData[$month]['spent'] += $game->amount;
                if ($game->is_winner) {
                    $monthlyData[$month]['won'] += $game->winnings;
                }
            }
            
            $groupsData[] = [
                'name' => $group->name,
                'data' => $monthlyData
            ];
        }

        // Statistiques par personne
        $persons = Person::with(['groups' => function($query) {
            $query->withPivot('balance');
        }])->get()->map(function($person) {
            return [
                'id' => $person->id,
                'name' => $person->display_name,
                'total_balance' => $person->total_balance,
                'floating_balance' => $person->floating_balance,
            ];
        });

        // Obtenir tous les mois uniques
        $allMonths = Game::select(DB::raw('DATE_FORMAT(game_date, "%Y-%m") as month'))
            ->distinct()
            ->orderBy('month')
            ->pluck('month')
            ->toArray();

        return view('dashboard', compact('groupsData', 'persons', 'allMonths'));
    }
}
