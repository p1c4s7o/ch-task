<?php

namespace App\Contracts\Nginx;

use App\Domain\Nginx\ValueObjects\NginxCommandResult;

interface CommandExecutorInterface
{
    public function execute(array $command): NginxCommandResult;
}
