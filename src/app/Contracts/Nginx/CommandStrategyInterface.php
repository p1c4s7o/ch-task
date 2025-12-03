<?php

namespace App\Contracts\Nginx;

interface CommandStrategyInterface
{
    public function build(string $command): array;
}
