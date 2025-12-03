<?php

namespace App\Contracts\Nginx;

interface CommandPipelineInterface
{
    public function pipe(callable $middleware): self;

    public function process(array $command): array;
}
