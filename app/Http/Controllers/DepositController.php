<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
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
        $this->middleware('getcustomer', ['only' => [
            'index'
        ]]);
        $this->middleware('getcustomer:true', ['only' => [
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
        
        return Response::json($customer->getMyDeposits(), 200);
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
        
        $customer = $request->attributes->get('customer');

        $deposit = new Deposit();

        $input = $deposit->extractValidColumns(Input::all());

        $input['customer_id'] = $customer->getData('id');

        if (!$deposit->validateInsert($input)) {
            return Response::json(array('error' => 'Parameters failed validation!', 'validation' => $deposit->errors()), 422);
        }

        //apply bonus for every 3rd deposit of the customer
        $input['bonus_applied'] = 0;
        if (($customer->countMyDeposits()+1) % 3 == 0)
        {
            $input['bonus_applied'] = $input['amount'] * ($customer->getData('bonus_parameter') / 100);
        }

        $deposit->fillData($input);

        $new_real_money_balance = $customer->getData('real_money_balance') + $deposit->getData('amount');
        $customer->setData('real_money_balance', $new_real_money_balance);

        $new_bonus_balance = $customer->getData('bonus_balance') + $deposit->getData('bonus_applied');
        $customer->setData('bonus_balance', $new_bonus_balance);

        try{
            //DB::beginTransaction();

            $success = $deposit->insertRecord() && $customer->updateRecord();

            if (!$success) {
                DB::rollBack();
                return Response::json(array('error' => 'Error occurred while inserting or updating record. Transaction has been cancelled and rolled back.'),400);
            }

            DB::commit();
            return Response::json($customer->getData(),200);
        }
        catch (\Exception $e){
            DB::rollBack();
            return Response::json(array('error' => $e->getMessage()),406); //'Data Not Acceptable'), 406);
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
