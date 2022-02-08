<?php

namespace App\Interfaces;

interface ModelInterface
{
    public function getTableName(): string;
    public function getHiddenAttributes(): array;
    public function getMigrateClass(): MigrateInterface;
}
