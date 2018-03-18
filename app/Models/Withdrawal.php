<?php

namespace App\Models;

class Withdrawal extends ElegantModel
{
    protected $table = 'withdrawals';
    protected $key = 'id';

    protected $columns = ['customer_id', 'amount'];

    // public function getCustomer()
    // {
    // }

    protected $insert_rules = [
        'amount' => 'required|numeric|between:0.01,9999999999999.99',
        'customer_id' => 'required|exists:customers,id'
    ];

    protected $update_rules = [
        'amount' => 'required|numeric|between:0.01,9999999999999.99',
        'customer_id' => 'required|exists:customers,id'
    ];
}
