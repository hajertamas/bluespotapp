<?php

$opts = ["help", "install", "uninstall", "export", "import:"];
$cli = getopt("", $opts);

if (PHP_SAPI != "cli") {
    die("This script can only be run in cli");
}

if (empty($cli) || isset($cli['help'])) {
    die("
     Avaliable commands:

     --help                     displays help
     --install                  creates all tables
     --uninstall                drops all tables
     --export                   exports data
     --import \"dir_name\"       imports data from " . __DIR__ . "\\\"dir_name\" 
     ");
}


foreach($cli as $command => $value){
    switch ($command) {
        case 'install':
            require_once "./migrate/install.php";
            break;
        case 'uninstall':
            
            echo "Are you sure you want to drop all tables? [Y/N]";
            $handle = fopen ("php://stdin","r");
            $line = fgets($handle);
            if(strtoupper(trim($line)) != 'Y'){
                exit;
            }
            fclose($handle);

            require_once "./migrate/uninstall.php";
            break;
        case 'export':
            require_once "./migrate/export.php";
            break;
        case 'import':
            $import_dir_name = $value;
            require_once "./migrate/import.php";
            break;
    }
}