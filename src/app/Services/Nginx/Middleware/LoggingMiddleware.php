<?php

namespace App\Services\Nginx\Middleware;

use App\Contracts\Nginx\CommandMiddlewareInterface;
use Illuminate\Support\Facades\Log;

class LoggingMiddleware implements CommandMiddlewareInterface
{
    /**
     * @param  string[]  $command
     * @return array<string[]>
     */
    public function handle(array $command, callable $next): array
    {
        Log::info('NGINX CMD: '.implode(' ', $command));

        return $next($command);
    }
}
