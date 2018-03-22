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

function arrayObject(array $array) {
    return new \App\ArrayObject($array);
}

function transactDB(callable $fn) {
    try {
        DB::transaction($fn);
    } catch (\Exception $e) {
        return ["errors"=>["exception"=>$e->getMessage()]];
    }
}

function filter(array $coll, callable $pred) : Collection {
    $coll = collect($coll);
    $filtered = collect();
    $coll->each(function($x) use ($filtered, $pred) {
        if ($pred($x))
            $filtered->push($x);
    });
    return $filtered;
}

function rejectNull(array $coll) : Collection {
    return filter($coll, function($x) {
        return !!$x;
    });
}

// TODO: fold should be used, but I don't have any documentation
function mapFilter(array $coll, callable $fn) : Collection {
    $coll = collect($coll);
    return rejectNull($coll->map($fn)->toArray());
}

function uniqueBy(string $key, array $coll) : Collection {
    $coll = collect($coll);
    $coll_ = collect();
    foreach ($coll as $item)
        $coll_->put($item->{$key}, $item);
    return $coll_->values();
}

function printDump($var) {
    fwrite(STDERR, print_r($var, TRUE)."\n");
}
