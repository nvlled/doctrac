<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class Office extends Model
{
    protected $appends = [
        "campus_name", "campus_code",
        "primary_email", "primary_phone_number",
        "other_emails", "other_phone_numbers"
    ];
    protected $hidden = ["campus"];

    function campus() {
        return $this->hasOne("App\Campus", "id", "campusId");
    }

    function user() {
        return $this->hasOne("\App\User", "officeId");
    }

    function setPrimaryContactInfo($email, $phoneNo) {
        $user = $this->user;
        if (!$user)
            return user;
        $user->email = $email;
        $user->phone_number = $phoneNo;
        $user->save();
    }

    function getPrimaryEmailAttribute() {
        return $this->user->email;
    }

    function getPrimaryPhoneNumberAttribute() {
        return $this->user->phone_number;
    }

    function getOtherEmailsAttribute() {
        return \App\OfficeEmail::where("officeId", $this->id)
            ->get()
            ->map(function($row) {
                return $row->data;
            });
    }

    function setOtherEmails($emails) {
        if (!$emails)
            return;

        \App\OfficeEmail::where("officeId", $this->id)->delete();

        foreach ($emails as $data) {
            $email = new \App\OfficeEmail();
            $email->officeId = $this->id;
            if (is_string($data)) {
                $email->data = $data;
            } else if (is_array($data)) {
                $email->name = $data["name"];
                $email->data = $data["data"];
            }
            try {
                $email->save();
            } catch (\Exception $e) { $e->getMessage(); /* ignore */ }
        }
    }

    function getOtherPhoneNumbersAttribute() {
        return \App\OfficeMobileNumber::where("officeId", $this->id)
            ->get()
            ->map(function($row) {
                return $row->data;
            });
    }

    function setOtherPhoneNumbers($phoneNumbers) {
        if (!$phoneNumbers)
            return;

        \App\OfficeMobileNumber::where("officeId", $this->id)->delete();

        foreach ($phoneNumbers as $data) {
            $phoneNo = new \App\OfficeMobileNumber();
            $phoneNo->officeId = $this->id;
            if (is_string($data)) {
                $phoneNo->data = $data;
            } else if (is_array($data)) {
                $phoneNo->name = $data["name"];
                $phoneNo->data = $data["data"];
            }
            try {
                $phoneNo->save();
            } catch (\Exception $e) { echo $e->getMessage(); /* ignore */ }
        }
    }

    function getCampusCodeAttribute() {
        return optional($this->campus)->code;
    }

    function getCampusNameAttribute() {
        return optional($this->campus)->name;
    }

    function holdsDoc($doc) {
        foreach ($doc->currentOffices() as $office) {
            if ($this->id == $office->id)
                return true;
        }
        return false;
    }

    function actionForStatus($status) {
        switch ($status) {
            case "processing": return "send";
            case "waiting":    return "recv";
        }
        return "";
    }

    public function actionForRoute($route) {
        if ($route->officeId == $this->id)
            return $this->actionForStatus(optional($route)->status);
        return "";
    }

    public function actionFor($doc) {
        foreach($doc->currentRoutes() as $route) {
            if ($this->id == $route->officeId) {
                $action = $this->actionForStatus($route->status);
                if ($action)
                    return $action;
            }
        }
        foreach($doc->nextRoutes() as $route) {
            if ($this->id == $route->officeId) {
                $action = $this->actionForStatus($route->status);
                if ($action)
                    return $action;
            }
        }
        return "";
    }

    public function getReceivingRoutes() {
        $routes = \App\DocumentRoute::where("officeId", $this->id)->get();
        $filtered = collect();
        $routes->each(function($route) use ($filtered) {
            if ($route->status == "waiting")
                return $filtered->push($route);
        });
        return $filtered;
    }

    public function getDispatchedRoutes() {
        $routes = \App\DocumentRoute::where("officeId", $this->id)->get();
        $filtered = collect();
        $routes->each(function($route) use ($filtered) {
            if ($route->status == "delivering")
                return $filtered->push($route);
        });
        return $filtered;
    }

    public function getFinalRoutes() {
        $routes = \App\DocumentRoute::where("officeId", $this->id)->get();
        $filtered = collect();
        $routes->each(function($route) use ($filtered) {
            if ($route->final && $route->status == "done")
                return $filtered->push($route);
        });
        return $filtered;
    }

    public function getActiveRoutes() {
        $routes = \App\DocumentRoute::where("officeId", $this->id)->get();
        $filtered = collect();
        $routes->each(function($route) use ($filtered) {
            if ($route->status == "processing")
                return $filtered->push($route);
        });
        return $filtered;
    }

    public function isFinal($doc) {
        foreach ($doc->finalRoutes() as $route) {
            if ($this->officeId == $route->officeId)
                return true;
        }
        return false;
    }

    public function canAbortSend($doc) {
        foreach ($doc->currentRoutes() as $route) {
            $nextRoute = $route->nextRoute;
            if ($route->officeId == $this->id &&
                $nextRoute && $nextRoute->receiverId == null)
                return true;
        }
        return false;
    }

    public function canSendDoc($doc) {
        return $this->holdsDoc($doc)
            && !$this->isFinal($doc);
    }

    public function canReceiveDoc($doc) {
        foreach ($doc->currentRoutes() as $route) {
            $next = $route->nextRoute;
            if (optional($next)->officeId == $this->id)
                return true;
        }
        return false;
    }

    public function getCompleteNameAttribute() {
        return "{$this->name} {$this->campus_name}";
    }

    public function validate() {
        return Validator::make($this->toArray(), [
            'name'     => 'required',
            'campusId' => 'required',
        ]);
    }

    // Note: I'm not sure if the 4 digit tracking number
    // should be shared across campuses
    public function generateTrackingID() {
        $now = now();
        $num = TrackingCounter::nextId();
        $noise = strtolower(str_random(Config::$randIDLen));
        return "{$this->campus_code}-{$now->year}-$num-$noise";
    }

    public function isLinkedTo($office) {
        if (!$office)
            return false;
        return ($this->gateway && $office->gateway)
            || ($this->campusId == $office->campusId);
    }

    public function nextOffices() {
        $campusId = $this->campusId;
        if ($this->gateway) {
            // get all local offices and
            // all gateway (i.e. records) offices
            // except the current one
            return self::query()
                ->where("id", "<>", $this->id)
                ->where(function($query) use ($campusId) {
                    $query->where("campusId", $campusId)
                          ->orWhere("gateway", true);
                })
                ->get();
        } else {
            // get all local offices
            // except the current one
            return self::query()
                ->where("id", "<>", $this->id)
                ->where("campusId", $campusId)
                ->get();
        }
    }

    public function getMembers() {
        return User::where("officeId", $this->id)->get();
    }


    public static function withUserName($username) {
        return optional(\App\User::where("username", $username)->first())->office;
    }
}
