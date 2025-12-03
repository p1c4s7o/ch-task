<?php

namespace App\Services\Nginx\Middleware;

use App\Contracts\Nginx\CommandMiddlewareInterface;

class ValidateMiddleware implements CommandMiddlewareInterface
{
    /**
     * @param  array<string[]>  $command
     * @return array<string[]>
     */
    public function handle(array $command, callable $next): array
    {
        if (empty($command)) {
            throw new \RuntimeException('Command is empty');
        }

        return $next($command);
    }
}
