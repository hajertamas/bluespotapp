<?php

namespace App\Models;

use App\Interfaces\MigrateInterface;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class UserMigrate extends Migration implements MigrateInterface
{
    public function up()
    {
        Manager::schema()->create((new User)->getTableName(), function (Blueprint $table) {
            $table->increments('id');
            $table->string('username');
            $table->string('email')->unique();
            $table->string('password_hash');
            $table->timestamps();
        });
    }

    public function down()
    {
        Manager::schema()->drop((new User)->getTableName());
    }
}
