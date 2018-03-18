<?php

namespace App\Models;
use DB;

class Customer extends ElegantModel
{
    protected $table = 'customers';
    protected $key = 'id';
    protected $optimisticLock = true;
    
    protected $columns = ['first_name', 'last_name', 'email', 'gender', 'country_code', 'bonus_parameter', 'real_money_balance', 'bonus_balance'];

    protected $appends = ['full_name', 'balance'];

    protected function getExtra($extra)
    {
        switch ($extra) {
            case 'full_name':
                return $this->getData('first_name').' '.$this->getData('last_name');
                //break;
            case 'balance':
                return $this->getData('real_money_balance') + $this->getData('bonus_balance');
                //break;
            default:
               return null;
        }
    }

    /**
     * Queries the withdrawals table to retrieve the withdrawals of the current customer.
     *
     * @return array
     */
    public function getMyWithdrawals()
    {
        $query = "SELECT * FROM withdrawals WHERE customer_id = :customer_id";
        $placeholders = ['customer_id'=>$this->getData('id')];

        $withdrawals = DB::select(DB::raw($query), $placeholders);
        
        return $withdrawals;
    }

    /**
     * Queries the deposits table to retrieve the deposits of the current customer.
     *
     * @return array
     */
    public function getMyDeposits()
    {
        $query = "SELECT * FROM deposits WHERE customer_id = :customer_id";
        $placeholders = ['customer_id'=>$this->getData('id')];

        $deposits = DB::select(DB::raw($query), $placeholders);
        
        return $deposits;
    }

    /**
     * Queries the deposits table to retrieve a count of the number
     * of deposits the current customer has made.
     *
     * @return array
     */
    public function countMyDeposits()
    {
        $query = "SELECT count(*) AS total FROM deposits WHERE customer_id = :customer_id";
        $placeholders = ['customer_id'=>$this->getData('id')];

        $result = DB::select(DB::raw($query), $placeholders);
        
        return $result[0]['total'];
    }

    protected $insert_rules = [
        'first_name' => 'required|string|min:2',
        'last_name' => 'required|string|min:2',
        'gender' => 'required|in:M,F,O,U', //Male/Female/Other/Unknown
        'email' => 'required|email|unique:customers,email',
        'bonus_parameter' => 'required|numeric|between:5.00,20.00',
        'country_code' => 'required|string|size:2|exists:countries,id',
        'real_money_balance' => 'numeric|between:0.00,9999999999999.99',
        'bonus_balance' => 'numeric|between:0.00,9999999999999.99'
    ];

    protected $update_rules = [
        'first_name' => 'required|string|min:2',
        'last_name' => 'required|string|min:2',
        'gender' => 'required|in:M,F,O,U', //Male/Female/Other/Unknown
        'email' => 'required|email',//|unique:customers,email',
        'bonus_parameter' => 'required|numeric|between:5.00,20.00',
        'country_code' => 'required|string|size:2|exists:countries,id',
        'real_money_balance' => 'numeric|between:0.00,9999999999999.99',
        'bonus_balance' => 'numeric|between:0.00,9999999999999.99'
    ];
    
}
