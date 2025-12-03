<?php

namespace App\Contracts\Nginx;

interface CommandMiddlewareInterface
{
    public function handle(array $command, callable $next): array;
}
