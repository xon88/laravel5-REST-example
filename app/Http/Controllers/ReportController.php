<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use Response;

use Carbon\Carbon;

use Input;

class ReportController extends Controller
{
    /**
     * Returns a new report counting and summing the deposits and withdrawals
     * of unique customers who have made at least 1 transaction during the period
     * of days specified in the 'to' and 'from' parameters (defaults to last 7 days if not passed).
     * Groups by date and customer's country.
     *
     * @return \Illuminate\Http\Response
     */
    public function transactions(Request $request)
    {
        $input = Input::all();

        if (!isset($input['from'])) {
            $dateFrom = Carbon::now()->subDays(7);
        } else {
            $dateFrom = Carbon::createFromFormat('d/m/Y', $input['from']);
        }

        if (!isset($input['to'])) {
            $dateTo = Carbon::now();
        } else {
            $dateTo = Carbon::createFromFormat('d/m/Y', $input['to']);
        }

        //check if dates are correct (from before to and no future dates)
        if (($dateFrom->diffInDays($dateTo, false) < 0) || ($dateTo->diffInDays(Carbon::now(), false) < 0)) {
            return Response::json(array('error' => 'Error in dates.'), 400);
        }

        $query = "SELECT u.date as Date
                       , co.name as CountryName
                       , co.id as CountryCode
                       , count(distinct(u.customer_id)) as UniqueCustomers
                       , count(u.deposit) as NoOfDeposits
                       , ifnull(sum(u.deposit),0) as TotalDepositsAmount
                       , ifnull(sum(u.bonus_applied),0) as TotalBonusesAppliedOnDeposits
                       , count(u.withdrawal) as NoOfWithdrawals
                       , ifnull(sum(u.withdrawal),0) as TotalWithdrawalsAmount
                    FROM customers cu
                       , countries co
                       , (SELECT customer_id
                               , amount as deposit
                               , null as withdrawal
                               , bonus_applied
                               , DATE(created_at) as date
                            FROM deposits
                           WHERE DATE(created_at) BETWEEN :from AND :to
                          UNION ALL
                          SELECT customer_id
                               , null as deposit
                               , amount as withdrawal
                               , null as bonus_applied
                               , DATE(created_at) as date
                            FROM withdrawals
                           WHERE DATE(created_at) BETWEEN :from2 AND :to2
                         ) u
                   WHERE co.id = cu.country_code
                     AND cu.id = u.customer_id
                   GROUP BY u.date
                          , co.name
                          , co.id
                   ORDER BY u.date DESC
                          , co.id";
                     // -- AND u.date BETWEEN ':from' AND ':to'

        $placeholders = ['from' => $dateFrom->format('Y/m/d'),
            'to' => $dateTo->format('Y/m/d'),
            'from2' => $dateFrom->format('Y/m/d'),
            'to2' => $dateTo->format('Y/m/d')];
        $results = DB::select(DB::raw($query), $placeholders);

        return Response::json($results, 200);
    }
}
