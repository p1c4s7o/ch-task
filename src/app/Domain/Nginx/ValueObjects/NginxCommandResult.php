<?php

namespace App\Domain\Nginx\ValueObjects;

class NginxCommandResult
{
    public function __construct(
        public bool $success,
        public string $output,
        public string $error
    ) {}
}
