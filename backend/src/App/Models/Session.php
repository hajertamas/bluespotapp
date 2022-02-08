<?php

namespace App\Models;

use App\Interfaces\MigrateInterface;
use App\Interfaces\ModelInterface;
use App\Model;

class Session extends Model implements ModelInterface
{

    protected $table = "sessions";
    public function getTableName(): string{
        return "sessions";
    }

    public function getHiddenAttributes(): array{
        return $this->hidden;
    }

    public function getMigrateClass(): MigrateInterface
    {
        return new SessionMigrate;
    }

    protected $fillable = [
        'user_id',
        'lifetime_minutes',
        'expires_at',
        'token'
    ];

    protected $hidden = [
        'token'
    ];


    public function user(){
        return $this->hasOne(User::class);
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }


}
