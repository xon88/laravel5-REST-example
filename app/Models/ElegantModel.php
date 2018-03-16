<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Validator;

//http://daylerees.com/trick-validation-within-models/

class ElegantModel extends Model
{

    /**
     * Get the fillable attributes of a given array. (exposes fillableFromArray)
     *
     * @param  array  $attributes
     * @return array
     */
    public function getFillableFromArray(array $attributes)
    {
        return $this->fillableFromArray($attributes);
    }

    protected $rules = array();
    protected function updateRules($params = null)
    {
        return $this->rules;
    }

    /**
     * If creating (primary key is not set), returns $rules
     * If updating (primary key is set), returns the updateRules
     * passing $updateParams will force updateRules to be returned
     *
     * @return array
     */
    protected function getRules($updateParams = null)
    {
        if ( ($this->getKey()) || ($updateParams) ) {
            return $this->updateRules($updateParams);
        }
        
        return $this->rules;
    }

    protected $errors;

    /**
     * Validates the given array of data against a set of $rules
     * If updating, uses a subset of rules, based on the update data provided
     * Returns boolean for the result of the validation and saves any validation errors to $errors array
     *
     * @param  array  $data
     * @param  boolean  $update
     * @return boolean
     */
    public function validate($data, $updateParams = null)
    {
        //https://laracasts.com/discuss/channels/requests/laravel-5-validation-request-how-to-handle-validation-on-update

        $rules = $this->getRules($updateParams);

        // make a new validator object
        $v = Validator::make($data, $rules);

        // check for failure
        if ($v->fails())
        {
            // set errors and return false
            $this->errors = $v->messages();
            return false;
        }

        // validation pass
        return true;
    }

    /**
     * Get the array containing any validation errors which have occurred
     *
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }

    public function scopeFilter($query, $params)
    {
        return $query;
    }

    public function toFormattedArray()
    {
        return $this->toArray();
    }

}
