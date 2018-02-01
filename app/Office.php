<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    public function validate() {
        return Validator::make($this->toArray(), [
            'name'   => 'required',
            'campus' => 'required',
        ]);
    }
}
