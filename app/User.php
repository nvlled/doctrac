<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    protected $appends = ["position_name", "office_name", "privilege_name"];
    protected $hidden = ["position", "office", "privilege", "password"];

    public function position() {
        return $this->hasOne("App\Position", "id", "positionId");
    }
    public function office() {
        return $this->hasOne("App\Office", "id", "officeId");
    }
    public function privilege() {
        return $this->hasOne("App\Privilege", "id", "privilegeId");
    }

    public function getPositionNameAttribute() {
        return optional($this->position)->name;
    }
    public function getPrivilegeNameAttribute() {
        return optional($this->privilege)->name;
    }
    public function getOfficeNameAttribute() {
        $office = $this->office;
        if (!$office)
            return "";
        return $office->campus . " " . $office->name;
    }

    public function validate() {
        return Validator::make($this->toArray(), [
            'email'  => 'required|unique:users|email',
            'firstname'  => 'required',
            'middlename'   => 'required',
            'lastname'   => 'required',
            'positionId' => 'required|exists:positions,id',
            'privilegeId' => 'required|exists:privileges,id',
            'officeId' => 'required|exists:offices,id',
        ]);
    }
}
