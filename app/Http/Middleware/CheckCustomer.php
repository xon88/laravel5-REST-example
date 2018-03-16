<?php

namespace App\Http\Middleware;

use Closure;
use Response;

use App\Models\Customer;

class CheckCustomer
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $routeId = $request->route('customer_id');
        $customerId = (!empty($routeId) ? $routeId : $request->route()->parameter('customer'));

        $customer = Customer::find($customerId);

        if(!isset($customer)){
            return Response::json(array('error' => 'Customer not found.'), 404);
        }
        
        $request->attributes->add(['customer' => $customer]);

        return $next($request);
    }
}
