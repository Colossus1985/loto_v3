<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Person extends Model
{
    use SoftDeletes;

    protected $table = 'persons';
    
    protected $fillable = ['firstname', 'lastname', 'pseudo', 'total_balance', 'floating_balance'];

    /**
     * Mutateur pour convertir le nom de famille en majuscules
     */
    public function setLastnameAttribute($value)
    {
        $this->attributes['lastname'] = $value ? strtoupper($value) : null;
    }

    /**
     * Obtenir le nom d'affichage (pseudo ou nom complet)
     */
    public function getDisplayNameAttribute()
    {
        if ($this->pseudo) {
            return $this->pseudo;
        }
        
        if ($this->firstname && $this->lastname) {
            return $this->firstname . ' ' . $this->lastname;
        }
        
        if ($this->firstname) {
            return $this->firstname;
        }
        
        if ($this->lastname) {
            return $this->lastname;
        }
        
        return 'Sans nom';
    }

    /**
     * Obtenir le nom complet
     */
    public function getFullNameAttribute()
    {
        if ($this->firstname && $this->lastname) {
            return $this->firstname . ' ' . $this->lastname;
        }
        
        if ($this->firstname) {
            return $this->firstname;
        }
        
        if ($this->lastname) {
            return $this->lastname;
        }
        
        return 'Sans nom';
    }

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
     * Relation avec les transactions
     */
    public function transactions()
    {
        return $this->hasMany(PersonTransaction::class)->orderBy('created_at', 'desc');
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

    /**
     * Enregistrer une transaction
     */
    public function logTransaction($type, $amount, $balanceBefore, $balanceAfter, $balanceType, $description = null, $groupId = null)
    {
        return $this->transactions()->create([
            'type' => $type,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'balance_type' => $balanceType,
            'description' => $description,
            'group_id' => $groupId,
        ]);
    }
}
