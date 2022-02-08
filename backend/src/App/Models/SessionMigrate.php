<?php

namespace App\Models;

use App\Interfaces\MigrateInterface;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class SessionMigrate extends Migration implements MigrateInterface
{

    public function up()
    {
        Manager::schema()->create((new Session)->getTableName(), function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('token')->unique();
            $table->integer('lifetime_minutes')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on((new User)->getTableName())->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down()
    {
        Manager::schema()->drop((new Session)->getTableName());
    }
}
