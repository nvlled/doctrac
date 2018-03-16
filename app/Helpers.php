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
    return new \App\ArrayObject($array);
}

function transactDB($fn) {
    try {
        DB::transaction($fn);
    } catch (\Exception $e) {
        return ["errors"=>["exception"=>$e->getMessage()]];
    }
}

function filter($collection, $pred) {
    $filtered = collect();
    $collection->each(function($x) use ($filtered, $pred) {
        if ($pred($x))
            $filtered->push($x);
    });
    return $filtered;
}

function rejectNull($collection) {
    return filter($collection, function($x) {
        return !!$x;
    });
}

function uniqueBy($key, $collection) {
    $collection_ = collect();
    foreach ($collection as $item)
        $collection_->put($item->{$key}, $item);
    return $collection_->values();
}
