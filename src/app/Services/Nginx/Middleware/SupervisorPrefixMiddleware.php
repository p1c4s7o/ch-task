<?php

namespace App\Services\Nginx\Middleware;

use App\Contracts\Nginx\CommandMiddlewareInterface;

class SupervisorPrefixMiddleware implements CommandMiddlewareInterface
{
    /**
     * @param array<string[]> $command
     * @return array<string[]>
     */
    public function handle(array $command, callable $next): array
    {
        if ($command[0] !== 'nginx')
            $command[0] = 'supervisorctl';

        return $next($command);
    }
}
