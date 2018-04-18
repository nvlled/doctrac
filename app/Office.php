<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class Office extends Model
{
    protected $guarded = [];

    protected $appends = [
        "complete_name",
        "campus_name", "campus_code",
        "primary_email", "primary_phone_number",
        "other_emails", "other_phone_numbers",
        "level",
    ];
    protected $hidden = ["campus", "user"];

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

        $errors = [];
        foreach ($phoneNumbers as $data) {
            $phoneNo = new \App\OfficeMobileNumber();
            $phoneNo->officeId = $this->id;

            if ( ! SubscribedNumber::isNumberActive($data)) {
                $errors []= "$data is unsubscribed";
            }

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
        return ["errors"=>["unsubscribed"=>$errors]];
    }

    function getCampusCodeAttribute() {
        return optional($this->campus)->code;
    }

    function getCampusNameAttribute() {
        return optional($this->campus)->name;
    }

    function holdsDoc($doc) {
        $api = DoctracAPI::new();
        foreach ($api->allCurrentRoutes($doc) as $route) {
            if ($this->id == optional($route->office)->id)
                return true;
        }
        return false;
    }

    function actionForStatus($doc, $status) {
        //if ($status == "processing" && $doc->state == "ongoing")
        if ($status == "processing") {
            if ($doc->state == "disapproved")
                return "return";
            return "send";
        } else if ($status == "waiting") {
            return "recv";
        }
        return "";
    }

    public function actionForRoute($route) {
        if ($route->officeId == $this->id)
            return $this->actionForStatus($route->document, optional($route)->status);
        return "";
    }

    public function actionFor($doc) {
        foreach($doc->currentRoutes() as $route) {
            if ($this->id == $route->officeId) {
                $action = $this->actionForStatus($doc, $route->status);
                if ($action)
                    return $action;
            }
        }
        foreach($doc->nextRoutes() as $route) {
            if ($this->id == $route->officeId) {
                $action = $this->actionForStatus($doc, $route->status);
                if ($action)
                    return $action;
            }
        }
        return "";
    }

    public function getForwardedRoutes() {
        $routes = \App\DocumentRoute
            ::where("officeId", $this->id)
            ->where("prevId", null)
            ->where("final", 0)
            ->get();
        return $routes;
    }

    public function getIncomingRoutes() {
        $routes = \App\DocumentRoute::where("officeId", $this->id)->get();
        return filter($routes, function($route) {
            return $route->status == "waiting";
        });
    }

    public function getDeliveringRoutes() {
        $routes = \App\DocumentRoute::where("officeId", $this->id)->get();
        return filter($routes, function($route) {
            return $route->status == "delivering";
        });
    }

    public function getFinalRoutes() {
        $routes = \App\DocumentRoute
            ::where("officeId", $this->id)
            ->where("final",   1)
            ->where("nextId",   null)
            ->get();
        $startRoutes = \App\DocumentRoute
            ::where("officeId", $this->id)
            ->where("prevId",   null)
            ->get();
        $startRoutes = filter($startRoutes, function($route) {
            return $route->document->isDone();
        })->sortByDesc("created_at");

        return uniqueBy("trackingId", $routes->merge($startRoutes));
    }

    public function getProcessingRoutes() {
        $routes = \App\DocumentRoute::where("officeId", $this->id)->get();

        return filter($routes, function($route) {
            return $route->status == "processing";
        });
    }

    public function getAllRoutes() {
        $routes = \App\DocumentRoute
            ::where("officeId", $this->id)
            ->orderByDesc("created_at")
            ->get();
        return uniqueBy("trackingId", $routes);
    }

    public function isFinal($doc) {
        $api = DoctracAPI::new();
        foreach ($api->allFinalRoutes($doc) as $route) {
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
            && $doc->state == "ongoing"
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
        return "{$this->campus_code}-{$now->year}-$noise";
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

    public function getLevelAttribute() {
        if ($this->main)
            return 3;
        if ($this->gateway)
            return 2;
        return 1;
    }

    public static function withUserName(string $username) {
        return optional(\App\User::where("username", $username)->first())->office;
    }

    public static function withUsernames(array $usernames) {
        return collect($usernames)->map(function($username) {
            return self::withUserName($username);
        });
    }

    public function notifySMS(\App\Notifications\DocumentAction $action) {
        $sendFailed = [];
        foreach ($this->other_phone_numbers as $num) {
            GlobeAPI::send($num, $action->toArray(null)["message"]);
        }
    }

    public static function getIDsOf($officeNames) {
        return collect($officeNames)->map(function($name) {
            return optional(self::withUserName($name))->id;
        });
    }
}
