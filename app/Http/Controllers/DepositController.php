<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
//use DB;
use Response;

use App\Models\Customer;
use App\Models\Deposit;
use Input;

class DepositController extends Controller
{
    /**
     * Instantiate a new instance.
     *
     * @return void
     */
    public function __construct()
    {
        //apply middleware
        $this->middleware('checkcustomer', ['only' => [
            'index',
            'store'
        ]]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $customer = $request->attributes->get('customer');
                
        $deposits = $customer->deposits()->get();
        
        return $deposits;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //todo: lock db
        
        $customer = $request->attributes->get('customer');

        $deposit = new Deposit();

        $input = $deposit->getFillableFromArray(Input::all());
        $input['customer_id'] = $customer->id;

        if (!$deposit->validate($input)) {
            return Response::json(array('error' => 'Parameters failed validation!', 'validation' => $deposit->errors()), 422);
        };

        $deposit->fill($input);
        //$deposit->customer_id = $customer->id;
        //$deposit->customer()->associate($customer);

        //apply bonus for every 3rd deposit of the customer
        $deposit->bonus_applied = 0;
        if (($customer->deposits()->count()+1) % 3 == 0)
        {
            $deposit->bonus_applied = $deposit->amount * ($customer->bonus_parameter / 100);
        }

        $customer->real_money_balance += $deposit->amount;
        $customer->bonus_balance += $deposit->bonus_applied;

        try{
            $deposit->save();
            $customer->save();
            return Response::json($customer, 201);
        }
        catch (\Exception $e){
            return Response::json(array('error' => 'Data Not Acceptable'), 406);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
