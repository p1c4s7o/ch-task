<?php

namespace App\Services\Nginx;

use App\Contracts\Nginx\CommandExecutorInterface;
use App\Contracts\Nginx\CommandPipelineInterface;
use App\Contracts\Nginx\CommandServiceInterface;
use App\Contracts\Nginx\CommandStrategyInterface;
use App\Domain\Nginx\ValueObjects\NginxCommandResult;

class NginxCommandService implements CommandServiceInterface
{
    public function __construct(
        private readonly CommandStrategyInterface $strategy,
        private readonly CommandPipelineInterface $pipeline,
        private readonly CommandExecutorInterface $executor
    ) {}

    public function execute(string $action): NginxCommandResult
    {
        $command = $this->strategy->build($action);
        $command = $this->pipeline->process($command);

        return $this->executor->execute($command);
    }
}
