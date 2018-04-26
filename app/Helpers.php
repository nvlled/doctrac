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

function paginate($coll, $pageNo) {
    $pageSize = \App\Config::PAGE_SIZE;
    $count = $coll->count();
    $numPages = ceil($count/$pageSize);
    return (object) [
        "items"=>$coll->forPage($pageNo, $pageSize),
        "startNo"=>($pageNo-1)*$pageSize+1,
        "pageNo"=>$pageNo,
        "numPages"=>$numPages,
        "pageSize"=>$pageSize,
        "numItems"=>$coll->count(),
    ];
}

function http_parse_query($str) {
    $str = trim($str);
    $qi = strpos($str, "?");
    if ($qi === false)
        return [];
    $str = substr($str, $qi+1);

    $query = [];
    foreach (explode("&", $str) as $str) {
        $fields = explode("=", trim($str));
        $k = $fields[0];
        $v = $fields[1] ?? "";
        if ( !! $k)
            $query[$k] = $v;
    }
    return $query;
}

// note; does not handle hashes on url
function replaceQueryString($url, $queryStr) {
    $index = strpos($url, "?");

    $queryStr = trim($queryStr);
    if ($queryStr[0] == "?")
        $queryStr = substr($queryStr, 1);

    if ($index !== false) {
        $url = substr($url, 0, $index);
    }

    return "$url?$queryStr";
}

function makeQueryString($params) {
    $str = [];
    foreach ($params as $k => $v) {
        $str []= "$k=$v";
    }
    return implode($str, "&");
}

function addQueryString($url, $queryStr) {
    $queryParams = array_merge(
        http_parse_query($url),
        http_parse_query($queryStr)
    );
    $index = strpos($url, "?");
    $queryStr = makeQueryString($queryParams);

    if ($index !== false) {
        $url = substr($url, 0, $index);
    }

    return "$url?$queryStr";
}
