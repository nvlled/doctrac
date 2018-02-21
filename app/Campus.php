<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class Campus extends Model
{
    public function validate() {
        return Validator::make($this->toArray(), [
            'code'     => 'required',
            'name' => 'required',
        ]);
    }
}
