<?php

namespace App\Models;

class Withdrawal extends ElegantModel
{
 	/**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'withdrawals';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['customer_id', 'amount'];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [];

    public function customer()
    {
        return $this->belongsTo('App\Models\Customer');
    }

    protected $rules = [
        'amount' => 'required|numeric|between:0.01,9999999999999.99',
        'customer_id' => 'required|exists:customers,id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'customer_id' => 'integer'
    ];
}
