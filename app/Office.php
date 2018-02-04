<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class Office extends Model
{
    public function holdsDoc($doc) {
        foreach ($doc->currentOffices() as $office) {
            if ($this->id == $office->id)
                return true;
        }
        return false;
    }

    public function isFinal($doc) {
        foreach ($doc->finalRoutes() as $route) {
            if ($this->officeId == $route->officeId)
                return true;
        }
        return false;
    }

    public function canSendDoc($doc) {
        return $this->holdsDoc($doc) && !$this->isFinal($doc);
    }

    public function canReceiveDoc($doc) {
        foreach ($doc->currentRoutes() as $route) {
            $next = $route->nextRoute;
            if (optional($next)->officeId == $this->id)
                return true;
        }
        return false;
    }

    public function validate() {
        return Validator::make($this->toArray(), [
            'name'   => 'required',
            'campus' => 'required',
        ]);
    }
}
