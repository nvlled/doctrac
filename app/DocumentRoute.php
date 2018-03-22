<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class DocumentRoute extends Model
{
    public $appends = [
        "document_title",
        "document_details",
        "document_class",
        "document_type",
        "time_elapsed",
        "seconds_elapsed",
        "campus_id",
        "attachment_filename",
        "attachment_size",
        "attachment_url",
        "status",
        "office_name",
        "next_office_name",
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

    public function allNextRoutes() {
        $nextRoutes = $this->moreNextRoutes();
        if (!$this->nextRoute)
            return $nextRoutes;
        return collect($this->nextRoute)->concat($nextRoutes);
    }

    public function moreNextRouteIds() {
        return $this->hasMany("App\NextRoute", "moreNextId", "moreNextId");
    }

    public function moreNextRoutes() {
        return $this->moreNextRouteIds->map(function($nr) {
            return $nr->route;
        });
    }

    public static function createNextRoute($routeIds, $moreNextId = null) {
        if (! $moreNextId)
            $moreNextId = generateId();
        return $routeIds->map(function($id) {
            return (new \App\NextRoute())->fill(["moreNextId"=>$moreNextId, "routeId"=>$id]);
        });
    }

    public function isDelivering() {
        return $this->status == "delivering";
    }

    public function isWaiting() {
        return $this->status == "waiting";
    }

    public function isProcessing() {
        return $this->status == "processing";
    }

    public function isCurrent() {
        $status = $this->status;
        return $status == "processing"
            || $status == "delivering";
    }

    public function isDone() {
        return $this->status == "done";
    }

    public function getDocumentTitleAttribute() {
        return optional($this->document)->title;
    }

    public function getDocumentClassAttribute() {
        return optional($this->document)->classification;
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
        return $office->complete_name;
    }

    public function getNextOfficeNameAttribute() {
        if ( ! $this->nextRoute)
            return "";
        return optional($this->nextRoute->office)->complete_name;
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

    public function getSecondsElapsedAttribute() {
    }

    public function getTimeElapsedAttribute() {
        if ( ! $this->arrivalTime || $this->final)
            return "";

        $d = new Carbon($this->arrivalTime);
        if ( ! $this->nextRoute || !$this->nextRoute->arrivalTime) {
            if ($d->diffInSeconds() < 30)
                return "just now";
            return $d->diffForHumans(now(), true);
        }
        return $d->diffForHumans(new Carbon($this->nextRoute->arrivalTime), true);
    }

    public function getStatusAttribute() {
        if ($this->arrivalTime) {
            $nextRoute  = $this->nextRoute;
            $nextRoutes = $this->allNextRoutes();

            if ($nextRoutes->isEmpty()) {
                if ($this->final)
                    return "done";
                return "processing";
            }

            if (!$this->senderId)
                return "processing";

            $allDone = $nextRoutes->all(function($route) {
                return !!$route->arrivalTime;
            });

            if ($allDone)
                return "done";

            return "delivering";
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
                "Dispatched from ({$prevRoute->office_name})
                 on {$prevRoute->forwardTime}"
            ));
        }
        if ($this->arrivalTime && $this->prevId != null) {
            $activities->push(joinLines(
                "Received on {$this->arrivalTime}"
            ));
        }
        if ($nextRoute && $this->sender) {
            $activities->push(joinLines(
                "Forwarded to {$nextRoute->office_name}
                 on {$this->forwardTime}"
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
            $user->readNotification($this->id);
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

    public function canBeAbortedBy($office) {
        if ( ! $office)
            return false;
        $nextRoute = $this->nextRoute;
        if ($this->officeId == $office->id &&
            $nextRoute && $nextRoute->receiverId == null)
            return true;
        return false;
    }

    public function canBeSentBy($office) {
        if ( ! $office)
            return false;
        if ($this->officeId != $office->id)
            return false;
        $doc = $this->document;
        return $office->holdsDoc($doc)
            && !$office->isFinal($doc);
    }

    public function previousRecordsRoute() {
        $route = $this->prevRoute;
        while ($route) {
            if (optional($route->office)->gateway) {
                return $route;
            }
            $route = $route->prevRoute;
        }
        return null;
    }

    public function traceOriginPath() {
        foreach ($this->document->startingRoutes() as $origin) {

            if ($this->pathId != $origin->pathId)
                continue;

            // [a, _, a] == [a]
            if ($this->id == $origin->id)
                return [$this];

            // [a, a, b] == [a, b]
            $midRoute = $this->previousRecordsRoute();
            if ($this->id == $midRoute->id)
                return [$midRoute, $origin];

            // [a, b, b] = [a, b]
            if ($midRoute->id == $origin->id)
                return [$this, $midRoute];

            // [a, b, c] == [a, b, c]
            return [$this, $midRoute, $origin];
        }
    }

}
