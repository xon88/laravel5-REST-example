<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use Response;

use App\Models\Customer;
use Input;

class CustomerController extends Controller
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
            'show'
        ]]);
        $this->middleware('getcustomer:true', ['only' => [
            'update'
            //'destroy'
        ]]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = "SELECT * FROM customers";

        $customers = DB::select(DB::raw($query));

        return Response::json($customers, 200);
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
        
        $customer = new Customer();

        $input = $customer->extractValidColumns(Input::all());

        if (!isset($input['bonus_parameter'])) {
            $input['bonus_parameter'] = rand(5,20);
        }

        if (!$customer->validateInsert($input)) {
            return Response::json(array('error' => 'Parameters failed validation!', 'validation' => $customer->errors()), 422);
        }

        $customer->fillData($input);

        try{
            //DB::beginTransaction();

            $success = $customer->insertRecord();
            
            if (!$success) {
                DB::rollBack();
                return Response::json(array('error' => 'Error occurred while inserting record. Transaction has been cancelled and rolled back.'),400);
            }

            DB::commit();
            return Response::json($customer->getData(),201);
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
        $customer = $request->attributes->get('customer');

        if(!isset($customer)){
            return Response::json(array('error' => 'Customer Not found.'), 404);
        }

        return Response::json($customer->getData(), 200);
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
        $customer = $request->attributes->get('customer');

        // if (!isset($customer)) {
        //     return Response::json(array('error' => 'Customer Not Found'), 404);
        // }

        $updated = $customer->extractValidColumns(Input::all());

        if (!$customer->validateUpdate($updated)) {
            return Response::json(array('error' => 'Parameters failed validation!', 'validation' => $customer->errors()), 422);
        }

        //if updating email (must be unique)
        if ($customer->getData('email') !== $updated['email']) {

            $query = "SELECT count(*) AS total FROM customers WHERE email = :email";
            $placeholders = ['email' => $updated['email']];
            $result = DB::select(DB::raw($query), $placeholders);

            if ($result[0]['total'] > 0) {
               return Response::json(array('error' => 'There is already an account associated with that e-mail address.'), 406);
            }
        }

        $customer->fillData($updated);

        try{
            DB::beginTransaction();

            $success = $customer->updateRecord();

            if (!$success) {
                DB::rollBack();
                return Response::json(array('error' => 'Error occurred while updating record. Transaction has been cancelled and rolled back.'),400);
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
