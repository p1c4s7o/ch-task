<?php

namespace App\Contracts\Nginx;

use App\Domain\Nginx\ValueObjects\NginxCommandResult;

interface CommandServiceInterface
{
    public function execute(string $action): NginxCommandResult;
}
