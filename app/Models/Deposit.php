<?php

namespace App\Models;

class Deposit extends ElegantModel
{
    protected $table = 'deposits';
    protected $key = 'id';

    protected $columns = ['customer_id', 'amount', 'bonus_applied'];

    // public function getCustomer()
    // {
    // }

    protected $insert_rules = [
        'amount' => 'required|numeric|between:0.01,9999999999999.99',
        'bonus_applied' => 'numeric|between:0.00,9999999999999.99',
        'customer_id' => 'required|exists:customers,id'
    ];

    protected $update_rules = [
        'amount' => 'required|numeric|between:0.01,9999999999999.99',
        'bonus_applied' => 'numeric|between:0.00,9999999999999.99',
        'customer_id' => 'required|exists:customers,id'
    ];
}
