<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class Campus extends Model
{
    protected $guarded = [];

    public function validate() {
        return Validator::make($this->toArray(), [
            'code'     => 'required|unique:campuses,code',
            'name' => 'required|unique:campuses,name',
        ]);
    }
}
