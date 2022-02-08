<?php

namespace App\Models;

use App\Interfaces\MigrateInterface;
use App\Interfaces\ModelInterface;
use App\Model;

class User extends Model implements ModelInterface
{

    protected $table = "users";
    public function getTableName(): string
    {
        return "users";
    }

    public function getHiddenAttributes(): array{
        return $this->hidden;
    }
    
    public function getMigrateClass(): MigrateInterface
    {
        return new UserMigrate;
    }

    protected $fillable = [
        'username',
        'email',
        'password_hash'
    ];

    protected $hidden = [
        'password_hash'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public function session()
    {
        return $this->hasMany(Session::class);
    }

    public function hours()
    {
        return $this->hasMany(Hours::class);
    }
}
