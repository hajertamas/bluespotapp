<?php

use App\Interfaces\ModelInterface;
use App\Model;
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
    foreach ($models as $class) {
        if (!($class instanceof Model) || !($class instanceof ModelInterface)) {
            throw new  Exception("Class " . $class::class . " must extend " . Model::class . " and implement " . ModelInterface::class);
        }

        echo "\n";
        $tableName = $class->getTableName();

        if (!Manager::schema()->hasTable($tableName)) {
            echo "Table  \"$tableName\" does not exist.";
            continue;
        }

        $class->getMigrateClass()->down();

        if (Manager::schema()->hasTable($tableName)) {
            throw new Exception("Table \"$tableName\" still exists after running " . $class::class . "->down()");
        } else {
            echo "Dropped table \"$tableName\"";
        }
    }

    try {
        $pdo->commit();
    } catch (PDOException $e) {
        //php ^8 error
    }
} catch (\Throwable $e) {
    $pdo->rollBack();
    echo "Uninstallation failed, rolled back changes:\n";
    echo $e;
} finally {
    Manager::schema()->enableForeignKeyConstraints();
}
