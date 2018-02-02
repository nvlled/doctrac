<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class Document extends Model
{
    public function create($user, $data) {
        // creates document and an initial dispatch
    }

    public function validate() {
        return Validator::make($this->toArray(), [
            'title'  => 'required',
            'trackingId'  => 'required',
            'userId' => 'required|exists:users,id',
        ]);
    }
}
