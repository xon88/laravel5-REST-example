<?php

namespace App\Models;

class Country extends ElegantModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'countries';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];

    protected $rules = [
        'id' => 'required|string|size:2',//|unique:countries,id',
        'name' => 'required|string|min:2'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [];
}