<?php

namespace App;

class DoctracAPI {

    public $errors = [];

    public function setErrors($errors) {
        $this->errors = ["errors"=>$errors];
        return null;
    }

    public function hasErrors() {
        return !!$this->errors;
    }

    public function getErrors() {
        return $this->errors;
    }

    public function buildRoute($officeIds, $user, $annotations, $route) {
        $doc = $route->document;
        $doc->createSerialRoutes($officeIds, $user, $annotations, $route);
    }

    public function receiveDocument($user, $trackingId) {
        if (is_string($user)) {
            $user = User::where("username", $user)->first();
        }

        $doc = \App\Document::where("trackingId", $trackingId)->first();

        if (!$doc)
            return $this->setErrors(["doc"=>"invalid tracking id"]);
        if (!$user)
            return $this->setErrors(["user"=>"invalid user"]);

        if (!$user->office)
            return $this->setErrors(["user"=>"user has no valid office"]);

        if (!$user->office->canReceiveDoc($doc)) {
            return $this->setErrors(["doc"=>"cannot receive document"]);
        }

        // TODO: handle parallel routes
        $errors = [];
        foreach ($doc->nextRoutes() as $route) {
            $office = $user->office;
            if ($office->id != $route->officeId)
                continue;
            $prevRoute = $route->prevRoute;
            if (!$prevRoute)
                continue;
            $route->receiverId = $user->id;
            $route->arrivalTime = ngayon();
            $route->save();
        }
        return $doc;
    }

    public function createDocument($user, $docData) {
        $docData = arrayObject($docData);
        if (is_string($user)) {
            $user = User::where("username", $user)->first();
        }

        if (!$user) {
            return $this->setErrors(["user id"=>"user id is invalid"]);
        }

        if (!$user->office) {
            return $this->setErrors(["office"=>"user does not have an office"]);
        }

        if (!$user->isKeeper()) {
            return $this->setErrors(["office"=>"user does belong to records office"]);
        }

        $doc = new \App\Document();
        $doc->userId         = $user->id;
        $doc->title          = $docData->title;
        $doc->type           = $docData->type;
        $doc->details        = $docData->details;
        $doc->trackingId     = $user->office->generateTrackingID();
        $annotations         = $docData->annotations;
        $doc->classification = $docData->classification;

        $v = $doc->validate();
        if ($v->fails()) {
            return $this->setErrors($v->errors());
        }

        // at least one destination must be given
        $ids = $docData->officeIds;
        if (!$ids) {
            $msg = "select at least one destination";
            return $this->setErrors(["officeIds"=>$msg]);
        }

        if (!$user->office) {
            return $this->setErrors(["officeId"=>"office id is invalid"]);
        }
        $officeId = $user->officeId;

        \DB::transaction(function() use ($doc, $ids, $user, $officeId, $annotations) {
            $doc->save();

            if ($doc->type == "serial") {
                $doc->createSerialRoutes($ids, $user, $annotations);
            } else {
                $doc->createParallelRoutes($ids, $user, $annotations);
            }
        });

        return $doc;
    }
}
