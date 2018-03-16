<?php

namespace App\Models;

class Customer extends ElegantModel
{
 	/**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'customers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['first_name', 'last_name', 'email', 'gender', 'country_code', 'bonus_parameter','real_money_balance','bonus_balance'];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['full_name','balance'];

    public function getFullNameAttribute()
    {
        $pad = function($text) {
            if (strlen($text) > 0) {
                return $text.' ';
            }
            return '';
        };

        return $pad($this->first_name).$this->last_name;
    }

    public function getBalanceAttribute()
    {
        return $this->real_money_balance + $this->bonus_balance;
    }

    public function country()
    {
        return $this->belongsTo('App\Models\Country');
    }

    public function deposits()
    {
        return $this->hasMany('App\Models\Deposit');
    }

    public function withdrawals()
    {
        return $this->hasMany('App\Models\Withdrawal');
    }

    protected $rules = [
        'first_name' => 'required|string|min:2',
        'last_name' => 'required|string|min:2',
        'gender' => 'required|in:M,F,O,U', //Male/Female/Other/Unknown
        'email' => 'required|email',//|unique:customers,email',
        'bonus_parameter' => 'required|numeric|between:5.00,20.00',
        'country_code' => 'required|string|size:2|exists:countries,id',
        'real_money_balance' => 'numeric|between:0.00,9999999999999.99',
        'bonus_balance' => 'numeric|between:0.00,9999999999999.99'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'real_money_balance' => 'real',
        'bonus_balance' => 'real',
        'bonus_parameter' => 'real'
    ];
}
