<?php

namespace App\Models;

use App\Interfaces\MigrateInterface;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class HoursMigrate extends Migration implements MigrateInterface
{

    public function up()
    {
        Manager::schema()->create((new Hours)->getTableName(), function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->date('date_day');
            $table->integer('hours');
            $table->string('description')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on((new User)->getTableName())->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down()
    {
        Manager::schema()->drop((new Hours)->getTableName());
    }
}
