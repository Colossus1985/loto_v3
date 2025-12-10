<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Person extends Model
{
    use SoftDeletes;

    protected $table = 'persons';
    
    protected $fillable = ['name', 'total_balance', 'floating_balance'];

    /**
     * Relation many-to-many avec Group via la table pivot group_person
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_person')
                    ->withPivot('balance')
                    ->withTimestamps();
    }

    /**
     * Calculer le solde total de la personne (somme de tous les balances dans les groupes)
     */
    public function calculateTotalBalance()
    {
        return $this->groups()->sum('group_person.balance');
    }

    /**
     * Calculer le solde global (groupes + flottant)
     */
    public function getTotalBalanceWithFloatingAttribute()
    {
        return $this->total_balance + $this->floating_balance;
    }
}
