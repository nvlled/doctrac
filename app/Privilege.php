<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Privilege extends Model
{
    public function validate() {
        return Validator::make($this->toArray(), [
            'name' => 'required',
        ]);
    }
}
