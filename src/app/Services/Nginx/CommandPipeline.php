<?php

namespace App\Services\Nginx;

use App\Contracts\Nginx\CommandPipelineInterface;

class CommandPipeline implements CommandPipelineInterface
{
    /**
     * @var array<callable>
     */
    private array $pipes = [];

    /**
     * @return $this
     */
    public function pipe(callable $middleware): self
    {
        $this->pipes[] = $middleware;

        return $this;
    }

    /**
     * @param  string[]  $command
     * @return array<callable>
     */
    public function process(array $command): array
    {
        $pipeline = array_reduce(
            array_reverse($this->pipes),
            fn ($next, $pipe) => fn ($cmd) => $pipe($cmd, $next),
            fn ($cmd) => $cmd
        );

        return $pipeline($command);
    }
}
