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

    Manager::schema()->disableForeignKeyConstraints();
    if (empty($import_dir_name)) {
        throw new Exception("\nDirectory name cannot be empty, Usage: \n\n --import \"file_name\"       imports data from " . __DIR__ . "\\\"file_name\" \n\n");
    }

    $dirname = __DIR__ . "/../$import_dir_name";

    if (!is_dir($dirname)) {
        throw new Exception("file $dirname already exists, cant continue export");
    }

    foreach ($models as $class) {
        if (!($class instanceof Model) || !($class instanceof ModelInterface)) {
            throw new  Exception("Class " . $class::class . " must extend " . Model::class . " and implement " . ModelInterface::class);
        }

        echo "\n";
        $tableName = $class->getTableName();
        $filename = $dirname . "/" . $tableName . ".json";

        if (!file_exists($filename)) {
            throw new Exception("Could not find file: $filename");
        }

        $data = json_decode(file_get_contents($filename), true, 512, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $class::class::insert($data);

        echo "Imported " . count($data) . " rows in to $tableName";
        

    }

    try {
        $pdo->commit();
    } catch (PDOException $e) {
        //php ^8 error
    }
} catch (\Throwable $e) {
    $pdo->rollBack();
    echo "Import failed, rolled back changes:\n";
    echo $e;
} finally{
    Manager::schema()->disableForeignKeyConstraints();
}
