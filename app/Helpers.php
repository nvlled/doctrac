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


?>
