<?php

namespace App\Services\Nginx\Middleware;

use App\Contracts\Nginx\CommandMiddlewareInterface;

class DockerPrefixMiddleware implements CommandMiddlewareInterface
{
    /**
     * @param  array<string[]>  $command
     * @return array<string[]>
     */
    public function handle(array $command, callable $next): array
    {
        $container = config('nginx.docker.container');
        if (! $container) {
            return $next($command);
        }

        $interactive = (bool) config('nginx.docker.interactive') ? '-i' : '';

        $dockerCommand = array_merge(
            ['docker', 'exec', $interactive, $container],
            $command
        );

        return $next($dockerCommand);
    }
}
