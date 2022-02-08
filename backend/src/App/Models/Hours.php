<?php

namespace App\Models;

use App\Interfaces\MigrateInterface;
use App\Interfaces\ModelInterface;
use App\Model;

class Hours extends Model implements ModelInterface
{

    protected $table = "hours";
    public function getTableName(): string{
        return "hours";
    }

    public function getHiddenAttributes(): array{
        return $this->hidden;
    }

    public function getMigrateClass(): MigrateInterface
    {
        return new HoursMigrate;
    }

    protected $fillable = [
        'user_id',
        'date_day',
        'hours',
        'description'
    ];

    public function user(){
        return $this->hasOne(User::class);
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

}
