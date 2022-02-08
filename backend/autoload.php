<?php


require_once __DIR__ . "/vendor/autoload.php";

spl_autoload_register( 'autoLoadFn' );


function autoLoadFn($class): Void {
    $parts = explode("\\", $class);
    $realPath = [];
    foreach($parts as $part){
        array_push($realPath, $part);
    }

    $fileName = __DIR__ . "/src/" . implode("/", $realPath) . ".php";
    $file = realpath($fileName);
    

    if (file_exists($file)) {
        require_once $file;
    }
}
