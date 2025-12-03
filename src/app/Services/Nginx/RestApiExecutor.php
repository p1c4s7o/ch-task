<?php

namespace app\Services\Nginx;

use App\Contracts\Nginx\CommandExecutorInterface;
use App\Domain\Nginx\ValueObjects\NginxCommandResult;

class RestApiExecutor implements CommandExecutorInterface
{
    /**
     * @param  array<string[]>  $command
     */
    public function execute(array $command): NginxCommandResult
    {
        throw new \RuntimeException('Unexpected');
    }
}
