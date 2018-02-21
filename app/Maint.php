<?php

namespace App;

class Maint {
    static function cleanDB() {
        if ( ! isLocal()) {
            return null;
        }
        echo env("APP_ENV");
        \DB::delete("delete from campuses");
        \DB::delete("delete from offices");
        \DB::delete("delete from positions");
        \DB::delete("delete from privileges");
        \DB::delete("delete from users");
        \DB::delete("delete from documents");
        \DB::delete("delete from document_routes");
        return "okay";
    }
}

?>
