<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class DocumentRoute extends Model
{
    public $appends = [
        "status", "office_name",
        "sender_name", "receiver_name",
        "detailed_info",
    ];
    public $hidden = ["office", "prevRoute", "nextRoute", "sender", "receiver"];

    public function office() {
        return $this->hasOne("App\Office", "id", "officeId");
    }

    public function sender() {
        return $this->hasOne("App\User", "id", "senderId");
    }
    public function receiver() {
        return $this->hasOne("App\User", "id", "receiverId");
    }

    public function prevRoute() {
        return $this->hasOne("App\DocumentRoute", "id", "prevId");
    }

    public function nextRoute() {
        return $this->hasOne("App\DocumentRoute", "id", "nextId");
    }

    public function getSenderNameAttribute() {
        return optional($this->sender)->fullname;
    }

    public function getReceiverNameAttribute() {
        return optional($this->receiver)->fullname;
    }

    public function getOfficeNameAttribute() {
        $office  = $this->office;
        if (!$office)
            return "";
        return $office->name . " " . $office->campus;
    }

    public function findNextRoute($officeId) {
        $route = $this->nextRoute;
        while ($route) {
            if ($route->officeId == $officeId)
                return $route;
            // TODO: make sure no cycles are made
            $route = $route->nextRoute;
        }
        return null;
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

    public function getDetailedInfoAttribute() {
        if ($this->isStart()) {
            if ($this->arrivalTime == $this->forwardTime)
                return "Created on {$this->arrivalTime} by {$this->receiver_name}";
            else {
                $text = textIndent("
                |Created by {$this->receiver_name}
                |on <strong>{$this->arrivalTime}</strong>
                ");
                if ($this->forwardTime) {
                    $text .= "\n" . textIndent("
                    |and was sent on <strong>{$this->forwardTime}</strong>
                    ");
                }
                return $text;
            }
        }
        $text = "";
        if ($this->annotations)
            $text .= "<em>Note:</em> $this->annotations\n\n";
        if ($this->arrivalTime) {
            $text .= textIndent("
                |Arrived on <strong>{$this->arrivalTime}</strong>
                |and was received by <strong>{$this->receiver_name}</strong>
                ");
            }
        if ($this->forwardTime) {
            $text .= "\n\n" . textIndent("
            |Forwarded on <strong>{$this->forwardTime}</strong>
            |and was dispatched by <strong>{$this->receiver_name}</strong>
            ");
        }
        if (!$text) {
            $text = "(no information available)";
        }
        return $text;
    }

    public function isStart() {
        return $this->prevId == null && $this->nextId != null;
    }

    public function followRoutesInPath() {
        $route = $this;
        $sortedRoutes = collect();

        while($route) {
            $sortedRoutes->push($route);
            $route = $route->nextRoute;
        }

        return $sortedRoutes;
    }
}
