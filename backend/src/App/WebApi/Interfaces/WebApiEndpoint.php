<?php

namespace App\WebApi\Interfaces;

use App\WebApi\Models\ApiResponse;

interface WebApiEndpoint
{
    public function process(): void;
    public function getResponse(): ApiResponse;
}
