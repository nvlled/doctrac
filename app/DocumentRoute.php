<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class DocumentRoute extends Model
{
    public $appends = [
        "document_title",
        "document_details",
        "document_type",
        "campus_id",
        "attachment_filename",
        "attachment_size",
        "attachment_url",
        "status", "office_name",
        "sender_name", "receiver_name",
        "detailed_info",
        "activities",
        "time_elapsed",
        "seen_by",
        "link",
    ];

    public $hidden = ["office", "document", "prevRoute", "nextRoute", "sender", "receiver"];

    public function office() {
        return $this->hasOne("App\Office", "id", "officeId");
    }

    public function document() {
        return $this->hasOne("App\Document", "trackingId", "trackingId");
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

    public function getDocumentTitleAttribute() {
        return optional($this->document)->title;
    }

    public function getDocumentTypeAttribute() {
        return optional($this->document)->type;
    }

    public function getDocumentDetailsAttribute() {
        return optional($this->document)->details;
    }

    public function getSenderNameAttribute() {
        return optional($this->sender)->fullname;
    }

    public function getReceiverNameAttribute() {
        return optional($this->receiver)->fullname;
    }

    public function getLinkAttribute() {
        return route("view-document", ["id"=>$this->id]);
    }

    public function getCampusIdAttribute() {
        return optional($this->office)->campusId;
    }

    public function getOfficeNameAttribute() {
        $office  = $this->office;
        if (!$office)
            return "";
        return $office->name . " " . $office->campus_name;
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

    public function getTimeElapsedAttribute() {
        return "TODO";
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

    public function isDone() {
        return $this->status == "done";
    }

    public function getActivitiesAttribute() {
        $activities = collect();
        $prevRoute = $this->prevRoute;
        $nextRoute = $this->nextRoute;

        if ($prevRoute && $prevRoute->sender) {
            $activities->push(joinLines(
                "Dispatched from the office ({$prevRoute->office_name})
                 on {$prevRoute->forwardTime} by {$prevRoute->sender_name}"
            ));
        }
        if ($this->arrivalTime && $this->prevId != null) {
            $activities->push(joinLines(
                "Received on office ({$this->office_name})
                 on {$this->arrivalTime} by {$this->receiver->fullname}"
            ));
        }
        if ($nextRoute && $this->sender) {
            $activities->push(joinLines(
                "Forwarded on the next office ({$nextRoute->office_name})
                 on {$this->forwardTime} by {$this->sender->fullname}"
            ));
        }

        return $activities;
    }

    public function getDetailedInfoAttribute() {
        if ($this->isStart()) {
            if ($this->arrivalTime == $this->forwardTime)
                return textIndent("
                    |Created on {$this->arrivalTime}
                    |by {$this->receiver_name}
                ");
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

    public function getSeenByAttribute() {
        $status = $this->status;
        if ($status == "delivering") {
            $seenRoutes = SeenRoute
                ::where("routeId", $this->nextId)
                ->where("status",  "waiting")
                ->get();
        } else if ($status == "waiting") {
            $seenRoutes = SeenRoute
                ::where("routeId", $this->id)
                ->where("status",  "waiting")
                ->get();
        } else {
            return collect();
        }

        $result = collect();
        return $seenRoutes;
        foreach ($seenRoutes as $sr) {
            $office = optional($sr->user)->office;
            if ($office && $office->id != $this->officeId) {
                $result->push($sr);
            }
        }
        return $result;
    }

    public function isStart() {
        return $this->prevId == null && $this->nextId != null;
    }

    public function seenBy($user) {
        if (!$user)
            return;
        try {
            $seen = new \App\SeenRoute();
            $seen->userId = $user->id;
            $seen->routeId = $this->id;
            $seen->status = $this->status;
            $seen->save();
        } catch (Exception $_) { /* ignore */ }
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

    public function getAttachmentFilenameAttribute() {
        return optional($this->document)->attachment_filename;
    }

    public function getAttachmentSizeAttribute() {
        return optional($this->document)->attachment_size;
    }

    public function getAttachmentUrlAttribute() {
        return optional($this->document)->attachment_url;
    }
}

