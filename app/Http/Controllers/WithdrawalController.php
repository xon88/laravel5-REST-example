<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
//use DB;
use Response;

use App\Models\Customer;
use App\Models\Withdrawal;
use Input;

class WithdrawalController extends Controller
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
                
        $withdrawals = $customer->withdrawals()->get();
        
        return $withdrawals;
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

        $withdrawal = new Withdrawal();

        $input = $withdrawal->getFillableFromArray(Input::all());
        $input['customer_id'] = $customer->id;

        if (!$withdrawal->validate($input)) {
            return Response::json(array('error' => 'Parameters failed validation!', 'validation' => $withdrawal->errors()), 422);
        };

        $withdrawal->fill($input);
        //$withdrawal->customer_id = $customer->id;
        //$withdrawal->customer()->associate($customer);

        if ($withdrawal->amount > $customer->real_money_balance) {
            return Response::json(array('error' => 'Insufficient funds. Customer can only withdraw up to '.$customer->real_money_balance), 400);
        };

        $customer->real_money_balance -= $withdrawal->amount;

        try{
            $withdrawal->save();
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