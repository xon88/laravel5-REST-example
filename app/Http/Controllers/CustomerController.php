<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
//use DB;
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
        $this->middleware('checkcustomer', ['only' => [
            'show',
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
        $customers = Customer::all();
        return $customers;
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
        // get country object
        //$country = Country::find($request['country_code']);

        $customer = new Customer();

        $input = $customer->getFillableFromArray(Input::all());        

        if (!isset($input['bonus_parameter'])) {
            $input['bonus_parameter'] = rand(5,20);
        }

        if (!$customer->validate($input)) {
            return Response::json(array('error' => 'Parameters failed validation!', 'validation' => $customer->errors()), 422);
        };
              
        if (Customer::where(['email' => $request['email']])->count() > 0) {
           return Response::json(array('error' => 'There is already an account associated with that e-mail address.'), 406);
        }

        $customer->fill($input);
        //$customer->email = $request['email'];
        //$customer->country_id = $country->id;

        try{
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
        $customer = $request->attributes->get('customer');

        if(!isset($customer)){
            return Response::json(array('error' => 'Customer Not found.'), 404);
        }

        return Response::json($customer, 200);
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

        if (!isset($customer)) {
            return Response::json(array('error' => 'Customer Not Found'), 404);
        }

        $updated = Input::all();

        if (!$customer->validate($updated)) {
            return Response::json(array('error' => 'Parameters failed validation!', 'validation' => $customer->errors()), 422);
        }

        //if updating email (must be unique)
        if ($customer->email !== $updated['email']) {
            if (Customer::where(['email' => $updated['email']])->count() > 0) {
                return Response::json(array('error' => 'There is already an account associated with that e-mail address.'), 406);
            }
        }

        $customer->fill($customer->getFillableFromArray($updated));

        if (!$customer->isDirty()) {
            return Response::json(array('error' => 'Nothing to update!'), 422);
        }

        try{
            $customer->save();
            return Response::json($customer, 200);
        }
        catch (\Exception $e){
            return Response::json(array('error' => 'Data Not Acceptable.'), 406);
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
