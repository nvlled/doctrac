<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class Privilege extends Model
{
    public function validate() {
        return Validator::make($this->toArray(), [
            'name' => 'required',
        ]);
    }
}
