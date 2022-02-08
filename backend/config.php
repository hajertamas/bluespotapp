<?php

use App\Config;

require_once "./autoload.php";

$cfg = new Config();

//Ahol az app fut
$cfg->setAppOrigin("http://localhost:4200");
$cfg->setDevMode(true);
//Bejelentkezések élettartama (perc)
$cfg->setSessionLifeTimeMinutes(60);
//Sql adatbázis conn
$cfg->setEloquentDriver(
    [
        "driver" => "mysql",
        "host" => "localhost",
        "database" => "bluespot",
        "username" => "root",
        "password" => ""
    ]
);
