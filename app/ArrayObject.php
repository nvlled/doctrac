<?php

namespace App;

class ArrayObject {
    public $data = [];
    public $default = null;

    function __construct($arg) {
        $array = $arg;
        if ($arg instanceof self)
            $array = $arg->data;
        if ($arg instanceof \Illuminate\Support\Collection)
            $array = $arg->toArray();
        $this->data = $array;
    }

    function __get($key) {
        return $this->data[$key] ?? $this->default;
    }

    function __set($key, $value) {
        return $this->data[$key] = $value;
    }
}
