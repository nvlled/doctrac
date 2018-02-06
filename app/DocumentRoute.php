<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class DocumentRoute extends Model
{
    public $appends = ["status", "office_name"];
    public $hidden = ["office", "prevRoute", "nextRoute"];

    public function office() {
        return $this->hasOne("App\Office", "id", "officeId");
    }

    public function prevRoute() {
        return $this->hasOne("App\DocumentRoute", "id", "prevId");
    }

    public function nextRoute() {
        return $this->hasOne("App\DocumentRoute", "id", "nextId");
    }

    public function getOfficeNameAttribute() {
        $office  = $this->office;
        if (!$office)
            return "";
        return $office->name . " " . $office->campus;
    }

    public function getStatusAttribute() {
        if ($this->arrivalTime) {
            $nextRoute = $this->nextRoute;
            if (!$nextRoute)
                return "done";

            if (!$this->senderId)
                return "processing";

            if (!$nextRoute->arrivalTime)
                return "delivering";
            return "done";
        } else {
            $prevRoute = $this->prevRoute;
            if (!$prevRoute)
                return "preparing";
            if ($prevRoute->senderId && $prevRoute->arrivalTime)
                return "waiting";
        }

        return "*";
    }

}
