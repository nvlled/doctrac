<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class Position extends Model
{
    public function validate() {
        return Validator::make($this->toArray(), [
            'name' => 'required',
        ]);
    }
}
