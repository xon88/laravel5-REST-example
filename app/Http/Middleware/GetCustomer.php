<?php

namespace App\Http\Middleware;

use Closure;
use Response;

use App\Models\Customer;

/**
 *
 * This middleware queries the database to check if the customer instance
 * exists, using the customer_id provided in the request.
 *
 * Returns an error if the customer is not found.
 *
 * If customer is retrieved successfully, loads the Customer instance into
 * the request attributes, to be used later on in the controller.
 *
 * If lockUpdate parameter is 'true', starts a new DB transaction and locks
 * the row for update.
 * This is done to maintain data integrity since the customer table contains
 * the monetary balances.
 */

class GetCustomer
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $lockUpdate = 'false')
    {
        $routeId = $request->route('customer_id');
        $customerId = (!empty($routeId) ? $routeId : $request->route()->parameter('customer'));

        $customer = new Customer();

        $lockForUpdate = ($lockUpdate === 'true');

        $customer = $customer->getByKey($customerId, $lockForUpdate);

        if(!isset($customer)){
            return Response::json(array('error' => 'Customer not found.'), 404);
        }
        
        $request->attributes->add(['customer' => $customer]);

        return $next($request);
    }
}
