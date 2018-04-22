<?php

namespace App;

class Maint {
    static function cleanDB() {
        if ( ! \App::environment("local")) {
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

    static function initializeDB() {
        if ( ! \App::environment("local")) {
            return null;
        }
        $json = file_get_contents(public_path("data/init.json"));
        $data = json_decode($json);
        foreach ($data->campuses as $c) {
            $campus = new \App\Campus();
            $campus->code = $c->code;
            $campus->name = $c->name;
            try {
                $campus->save();
            } catch (\Exception $e) {
                echo $e->getMessage() . "\n";
                continue;
            }
            foreach ($data->offices as $officeName) {
                $office = new \App\Office();
                $office->name     = $officeName;
                $office->campusId = $campus->id;
                $office->gateway  = strtolower($officeName) == "records";
                $office->main     = $campus->code == "main" && $office->gateway;
                try {
                    $office->save();
                } catch (\Exception $e) {
                    echo $e->getMessage() . "\n";
                    continue;
                }
                $user = new \App\User();
                $user->username = $campus->code . "-" . strtolower($office->name);
                $user->firstname = $office->name;
                $user->lastname  = $campus->name;
                $user->password = bcrypt("x");
                $user->positionId  = 0;
                $user->officeId    = $office->id;
                try {
                    $user->save();
                } catch (\Exception $e) {
                    echo $e->getMessage() . "\n";
                    continue;
                }
            }
        }
    }
}
