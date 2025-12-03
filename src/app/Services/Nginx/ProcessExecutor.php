<?php

namespace App\Services\Nginx;

use App\Contracts\Nginx\CommandExecutorInterface;
use App\Domain\Nginx\ValueObjects\NginxCommandResult;
use Symfony\Component\Process\Process;

class ProcessExecutor implements CommandExecutorInterface
{
    /**
     * @param  array<string[]>  $command
     */
    public function execute(array $command): NginxCommandResult
    {
        $process = new Process($command);
        try {
            $process->run();

            return new NginxCommandResult(
                $process->isSuccessful(),
                $process->getOutput(),
                $process->getErrorOutput()
            );
        } catch (\Throwable $e) {
            return new NginxCommandResult(
                false,
                implode(' ', [$process->getOutput(), $process->getErrorOutput()]),
                $e->getMessage()
            );
        }
    }
}
