<?php

use Illuminate\Support\Collection;

function generateId() : int {
    $gen = new App\IdGen();
    $gen->save();
    return $gen->id;
}

function textIndent(string $text) {
    $lines = [];
    foreach (explode("\n", $text) as $line) {
        $lines[] = preg_replace('/^\s*\|/', '', $line);
    }
    return trim(implode($lines, "\n"));
}

function joinLines(string $text) {
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

function ngayon() {
    return now(config("app.timezone"));
}

function newObject(...$keyValues) {
    $obj = [];
    for ($i = 0; $i <= count($keyValues)-1; $i+=2) {
        $k = @$keyValues[$i];
        $v = @$keyValues[$i+1];
        $obj[$k] = $v;
    }
    return (object) $obj;
}

function arrayObject($array) {
    if ($array instanceof \App\ArrayObject)
        return $array;
    return new \App\ArrayObject($array);
}

function transactDB(callable $fn) {
    try {
        DB::transaction($fn);
    } catch (\Exception $e) {
        return ["errors"=>["exception"=>$e->getMessage()]];
    }
}

function filter($coll, callable $pred) : Collection {
    $coll = collect($coll);
    $filtered = collect();
    $coll->each(function($x) use ($filtered, $pred) {
        if ($pred($x))
            $filtered->push($x);
    });
    return $filtered;
}

function rejectNull($coll) : Collection {
    return filter($coll, function($x) {
        return !!$x;
    });
}

// TODO: fold should be used, but I don't have any documentation
function mapFilter($coll, callable $fn) : Collection {
    $coll = collect($coll);
    return rejectNull($coll->map($fn)->toArray());
}

function uniqueBy(string $key, $coll) : Collection {
    $coll = collect($coll);
    $coll_ = collect();
    foreach ($coll as $item)
        $coll_->put($item->{$key}, $item);
    return $coll_->values();
}

function printDump($var) {
    fwrite(STDERR, print_r($var, TRUE)."\n");
}

function is_empty($coll) {
    if (!$coll)
        return true;
    if (count($coll) == 0)
        return true;
    return false;
}

function crap(...$objs) {
    foreach ($objs as $obj) {
        if (method_exists($obj, "toArray"))
            return dump($obj->toArray());
        dump($obj);
    }
}

function hiddenIf($cond) {
    if ($cond)
        return "hidden";
    return "";
}

function textIf($cond, $text) {
    if ($cond)
        return $text;
    return "";
}

function logCrap(...$objs) {
    foreach ($objs as $o) {
        \Log::info(print_r($o, true));
    }
}

function api($user=null) {
    if ($user instanceof \App\User)
        return new \App\DoctracAPI($user);
    if (is_string($user)) {
        $user = \App\User::where("username", $user)->first();
        if ($user)
            return new \App\DoctracAPI($user);
    }
    return \App\DoctracAPI::new();
}

function isEmpty($arr) {
    if (!$arr)
        return true;
    return count($arr) == 0;
}
