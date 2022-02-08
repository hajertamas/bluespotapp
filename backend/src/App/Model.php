<?php

namespace App;

use Illuminate\Database\Eloquent\Model as Eloquent;


class Model extends Eloquent
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}
