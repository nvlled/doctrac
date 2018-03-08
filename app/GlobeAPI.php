<?php

namespace App;

use Globe\Connect\Oauth;
use Globe\Connect\Sms;

class GlobeAPI {

    static function trimPrefix($number) {
        return preg_replace("/(^\+?639)|(^09)/", "9", $number);
    }

    static function broadcast($address, $message) {
        foreach (\App\SubscribedNumber::all() as $sub) {
            self::send($sub->subscriberNumber, $message);
        }
    }

    static function execute($data) {
        $msg = $data->message;
        $sender = $data->senderAddress;
        $fields = array_filter(explode(" ", $msg), function($x) {
            return !!$x;
        });

        switch (strtolower(@$fields[0])) {
        case "send":
            $msg = "from $sender:" . implode(array_slice($fields, 1), " ");
            GlobeAPI::broadcast($sender, $msg);
        }
    }

    static function send($address, $message) {
        $address = self::trimPrefix($address);
        $sub = \App\SubscribedNumber::where("subscriberNumber", $address)->first();
        if (!$sub || !$sub->active) {
            return null;
        }

        $id = env("GLOBE_ID");
        $code = env("GLOBE_CODE");
        $secret = env("GLOBE_SECRET");

        $shortCode = substr($code, strlen($code)-4, strlen($code)); // get last 4 digits
        $token = $sub->accessToken;

        $sms = new Sms($shortCode, $token);
        $sms->setReceiverAddress($address);

        // truncate message to 150 characters
        $limit = 150;
        if (strlen($message) >= $limit) {
            $message = substr($message, 0, $limit-3) . "...";
        }
        $sms->setMessage($message);

        $response = $sms->sendMessage();
        \Log::debug("globe api send response " . ((string) $response));

        return $response;
    }
}
