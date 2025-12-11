<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonTransaction extends Model
{
    protected $fillable = [
        'person_id',
        'group_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'balance_type',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    /**
     * Relation avec Person
     */
    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    /**
     * Relation avec Group
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
