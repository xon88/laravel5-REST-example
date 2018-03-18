<?php

namespace App\Models;

class Country extends ElegantModel
{
    protected $table = 'countries';

    protected $key = 'id';

    protected $columns = ['id', 'name'];

    protected $hasTimestamps = false;

    protected $insert_rules = [
        'id' => 'required|string|size:2|unique:countries,id',
        'name' => 'required|string|min:2'
    ];

    protected $update_rules = [
        'id' => 'required|string|size:2',//|unique:countries,id',
        'name' => 'required|string|min:2'
    ];
}