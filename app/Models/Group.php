<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use SoftDeletes;

    protected $table = 'groups';
    
    protected $fillable = ['name', 'total_budget', 'total_winnings'];

    /**
     * Relation many-to-many avec Person via la table pivot group_person
     */
    public function persons()
    {
        return $this->belongsToMany(Person::class, 'group_person')
                    ->withPivot('balance')
                    ->withTimestamps();
    }

    /**
     * Relation one-to-many avec Game
     */
    public function games()
    {
        return $this->hasMany(Game::class);
    }

    /**
     * Calculer le budget total du groupe (somme de tous les balances des personnes)
     */
    public function calculateTotalBudget()
    {
        return $this->persons()->sum('group_person.balance');
    }

    /**
     * Calculer le total dépensé (somme de tous les montants des jeux)
     */
    public function getTotalSpentAttribute()
    {
        return $this->games()->sum('amount');
    }

    /**
     * Calculer le total des gains (somme de tous les gains des jeux gagnants)
     */
    public function getTotalWonAttribute()
    {
        return $this->games()->where('is_winner', true)->sum('winnings');
    }
}
