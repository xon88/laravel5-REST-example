<?php

namespace App\Models;

use Validator;
use DB;

use Carbon\Carbon;

class ElegantModel
{
    /**
     * The database table name.
     *
     * @var string
     */
    protected $table;

    /**
     * The primary key column name.
     *
     * @var string
     */
    protected $key = 'id';

    /**
     * An array of column names that are fillable on insert.
     *
     * @var array
     */
    protected $columns;

    /**
     * The attributes to append to the model's array form.
     *
     * @var array
     */
    protected $appends;

    /**
     * The array form of the model.
     *
     * @var array
     */
    protected $data;

    /**
     * An array containing any validation errors which have occurred.
     *
     * @var array
     */
    protected $errors;

    /**
     * Indicates whether the table contains created_at and updated_at timestamps
     * which should be filled on insert and updated on update.
     *
     * @var boolean
     */
    protected $hasTimestamps = true;

    /**
     * Indicates whether the table contains a version column
     * which should be checked and updated on update (optimistic locking).
     *
     * @var boolean
     */
    protected $optimisticLock = false;

    /**
     * An array of validation rules per column to be checked before inserting.
     *
     * @var array
     */
    protected $insert_rules = array();

    /**
     * An array of validation rules per column to be checked before updating.
     *
     * @var array
     */
    protected $update_rules = array();

    /**
     * Gets the desired value of the appended attribute value.
     * This function must be overriden if model contains appended values.
     *
     * @param  string $extra
     * @return mixed
     */
    protected function getExtra($extra)
    {
        return null;
    }

    /**
     * Extracts the valid attributes which can be inserted
     * (from $this->columns) from the array $attributes.
     *
     * @param  array $attributes
     * @return array
     */
    public function extractValidColumns(array $attributes)
    {
        return array_intersect_key($attributes, array_flip($this->columns));
    }

    /**
     * Fills the model's array representation.
     *
     * @param  array $data
     */
    public function fillData(array $data)
    {
        foreach ($data as $key => $value) {
            $this->data[$key] = $value;
        }
    }

    /**
     * Fills the desired attribute of the model's array representation.
     *
     * @param  string $key
     * @param  mixed $value
     */
    public function setData($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Queries the database for a specific row in the current model's table.
     * If lockForUpdate is true, starts a new DB transaction and locks the row for update.
     *
     * @param  string $key
     * @param  boolean $lockForUpdate
     */
    public function getByKey($key, $lockForUpdate = false)
    {
        $query = "SELECT * FROM ".$this->table." WHERE ".$this->key." = :key";
        $placeholders = ['key' => $key];

        if ($lockForUpdate) {
            DB::beginTransaction();
            $query .= " FOR UPDATE";
        }

        $result = DB::select(DB::raw($query), $placeholders);

        if (count($result) !== 1) {
            return null;
        }

        $this->fillData($result[0]);

        return $this;
    }

    /**
     * Returns the array representation of the model.
     * If $column is set, returns just the desired attribute.
     *
     * @param  string $column
     * @return array
     */
    public function getData($column = null)
    {
        if ($column !== null) {
            return $this->data[$column];
        }

        if ($this->appends !== NULL) {
            $extras = array();

            foreach ($this->appends as $extra) {
                $extras[$extra] = $this->getExtra($extra);
            }

            return array_merge($this->data, $extras);
        }

        return $this->data;
    }

    /**
     * Validates the given array of $data against the set of $rules specified.
     * Returns boolean for the result of the validation and saves any validation errors to $errors array.
     *
     * @param  array  $data
     * @param  array  $rules
     * @return boolean
     */
    protected function valid(array $data, array $rules)
    {
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
     * Validates the given array of $data against the instance's $insert_rules.
     * Returns boolean for the result of the validation and saves any validation errors to $errors array.
     *
     * @param  array  $data
     * @return boolean
     */
    public function validateInsert(array $data)
    {
        return $this->valid($data, $this->insert_rules);
    }

    /**
     * Validates the given array of $data against the instance's $update_rules.
     * Returns boolean for the result of the validation and saves any validation errors to $errors array.
     *
     * @param  array  $data
     * @return boolean
     */
    public function validateUpdate(array $data)
    {
        return $this->valid($data, $this->update_rules);
    }

    /**
     * Executes an insert of a new row using the instance's $data.
     * If insert is successful, retrieves it's new id and queries the
     * database to refresh the $data array representation.
     * Returns the success of the operation.
     *
     * @return boolean
     */
    public function insertRecord()
    {
        $this->data = $this->extractValidColumns($this->data);

        if ($this->hasTimestamps) {
            $this->data['created_at'] = Carbon::now();
            $this->data['updated_at'] = Carbon::now();
        }

        $columns = implode(', ',array_keys($this->data));
        $values = ":".implode(', :',array_keys($this->data));

        $query = "INSERT INTO ".$this->table." (".$columns.") VALUES (".$values.")";
        $placeholders = $this->data;
        $result = DB::statement(DB::raw($query), $placeholders);

        if (!$result) {
            return false;
        }

        $key = DB::getPdo()->lastInsertId();
        $this->getByKey($key);

        return true;
    }

    /**
     * Executes an update of a the current row using the instance's updated $data.
     * If update is successful, queries the database to refresh the $data array representation.
     * Returns the success of the operation.
     *
     * @return boolean
     */
    public function updateRecord()
    {
        if ($this->hasTimestamps) {
            $this->data['updated_at'] = Carbon::now();
        }

        $versionQuery = "";
        $placeholders = [];
        if ($this->optimisticLock) {
            $versionQuery = " AND version = :oldversion";
            $placeholders['oldversion'] = $this->getData('version');
            $this->data['version'] += 1;
        }

        $placeholders['key'] = $this->getData($this->key);

        $set = array();
        foreach ($this->data as $key => $value) {
            $set[] = $key." = :".$key;
        }
        $set = implode(', ',$set);

        $query = "UPDATE ".$this->table." SET ".$set." WHERE ".$this->key." = :key".$versionQuery;
        $placeholders = array_merge($this->data, $placeholders);
        $result = DB::affectingStatement(DB::raw($query), $placeholders);

        if ($result !== 1) {
            return false;
        }

        $this->getByKey($this->getData($this->key));

        return true;
    }

    /**
     * Exposes the array containing any validation errors which have occurred
     *
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }

}
