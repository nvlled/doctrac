<?php

class Flash {
    const KEY_OKAY = "action-flash-okay";
    const KEY_ERR = "action-flash-err";

    static private function __push($key, $msg) {
        $msgs = (array) \Session::get($key);
        $msgs []= $msg;
        \Session::put($key, $msgs);
    }

    static function has() {
        return \Session::has(self::KEY_OKAY);
    }

    static function hasError() {
        return \Session::has(self::KEY_ERR);
    }

    static function add($msg) {
        self::__push(self::KEY_OKAY, $msg);
    }

    static function get() {
        return @\Session::remove(self::KEY_OKAY)[0];
    }

    static function getAll() {
        return \Session::remove(self::KEY_OKAY);
    }

    static function addError($msg) {
        self::__push(self::KEY_ERR, $msg);
    }

    static function error() {
        return @\Session::remove(self::KEY_ERR)[0];
    }

    static function errorAll() {
        return \Session::remove(self::KEY_ERR);
    }
}
