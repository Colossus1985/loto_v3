<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $table = 'games';
    
    protected $fillable = ['group_id', 'amount', 'cost_per_person', 'game_date', 'winnings', 'is_winner'];

    protected $casts = [
        'game_date' => 'date',
        'is_winner' => 'boolean',
    ];

    /**
     * Relation many-to-one avec Group
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
