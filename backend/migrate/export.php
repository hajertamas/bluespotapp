<?php

use App\Interfaces\ModelInterface;
use App\Model;
use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager;

require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../migrate_classes.php";

if (empty($models)) {
    throw new Exception("Classes for migration undefined (\$models)");
}

$pdo = Manager::connection()->getPdo();
$pdo->beginTransaction();

try {

    $dirname = __DIR__ . "/../bluespot-app-export-" . Carbon::now()->getTimestampMs();

    if (file_exists($dirname) || is_dir($dirname)) {
        throw new Exception("file $dirname already exists, cant continue export");
    }

    mkdir($dirname);



    foreach ($models as $class) {
        if (!($class instanceof Model) || !($class instanceof ModelInterface)) {
            throw new  Exception("Class " . $class::class . " must extend " . Model::class . " and implement " . ModelInterface::class);
        }

        echo "\n";
        $tableName = $class->getTableName();
        $filename = $dirname . "/" . $tableName . ".json";
        $handle = fopen($filename, "w");
        $collection = $class::all();
        $collection->makeVisible($class->getHiddenAttributes());
        $data = $collection->toArray();
        fwrite($handle, json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        fclose($handle);

        echo "exported $tableName";
    }

    try {
        $pdo->commit();
    } catch (PDOException $e) {
        //php ^8 error
    }

    echo "\nExport completed to directory: \n\n$dirname\n\n";

} catch (\Throwable $e) {
    $pdo->rollBack();
    echo "Export failed, rolled back changes:\n";
    echo $e;
}
