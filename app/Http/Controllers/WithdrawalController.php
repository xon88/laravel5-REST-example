<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
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

        return Response::json($customer->getMyWithdrawals(), 200);
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

        $withdrawal = new Withdrawal();

        $input = $withdrawal->extractValidColumns(Input::all());

        $input['customer_id'] = $customer->getData('id');

        if (!$withdrawal->validateInsert($input)) {
            return Response::json(array('error' => 'Parameters failed validation!', 'validation' => $withdrawal->errors()), 422);
        }

        if ($input['amount'] > $customer->getData('real_money_balance')) {
            return Response::json(array('error' => 'Insufficient funds. Customer can only withdraw up to '.$customer->getData('real_money_balance')), 400);
        }

        $withdrawal->fillData($input);

        $new_real_money_balance = $customer->getData('real_money_balance') - $withdrawal->getData('amount');
        $customer->setData('real_money_balance', $new_real_money_balance);

        try{
            //DB::beginTransaction();

            $success = $withdrawal->insertRecord() && $customer->updateRecord();

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
