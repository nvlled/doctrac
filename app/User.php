<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    protected $appends = [
        "campus_id",
        "position_name",
        "office_name",
        "privilege_name",
        "fullname",
        "gateway",
    ];
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

    public function getFullnameAttribute() {
        $mi = $this->middlename;
        if ($mi)
            return title_case("{$this->firstname} {$mi[0]}. {$this->lastname}");
        return title_case("{$this->firstname} {$this->lastname}");
    }

    public function getCampusIdAttribute() {
        return optional($this->office)->campusId;
    }

    public function getGatewayAttribute() {
        return optional($this->office)->gateway;
    }

    public function getPositionNameAttribute() {
        return optional($this->position)->name;
    }

    public function getPrivilegeNameAttribute() {
        return optional($this->privilege)->name;
    }
    public function getOfficeNameAttribute() {
        return optional($this->office)->complete_name;
    }

    public function seenRoutes() {
        return SeenRoute::where("userId", $this->id)->get();
    }

    public function isKeeper() {
        return optional($this->office)->gateway;
    }

    public function validate() {
        $data = $this->toArray();
        $data["password"] = $this->password;
        return Validator::make($data, [
            'username'  => 'required|unique:users,username',
            'password'  => 'required',
            'firstname'  => 'required',
            'lastname'   => 'required',
            //'positionId' => 'required|exists:positions,id',
            //'privilegeId' => 'required|exists:privileges,id',
            'officeId' => 'required|exists:offices,id',
        ]);
    }
}
