<?php

// TODO: fold should be used, but I don't have any documentation
function mapFilter($coll, $fn) {
    $coll = collect($coll);
    $coll = $coll->map($fn);
    $coll = $coll->filter(function($x) {
        return $x != null;
    });
    return $coll;
}

function generateId() {
    $gen = new App\IdGen();
    $gen->save();
    return $gen->id;
}

function textIndent($text) {
    $lines = [];
    foreach (explode("\n", $text) as $line) {
        $lines[] = preg_replace('/^\s*\|/', '', $line);
    }
    return trim(implode($lines, "\n"));
}

function joinLines($text) {
    $text = preg_replace('/\n/', ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);
    return $text;
}

function isLocal() {
    return env("APP_ENV") == "local";
}

function disk() {
    return Storage::disk();
}

function flashMessages() {
    // TODO: 
    return request()->session()->flash("action-notice") ?? [];
}
