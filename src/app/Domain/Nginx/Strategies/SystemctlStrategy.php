<?php

namespace App\Domain\Nginx\Strategies;

use App\Contracts\Nginx\CommandStrategyInterface;

class SystemctlStrategy implements CommandStrategyInterface
{
    /**
     * @return string[]
     */
    public function build(string $command): array
    {
        return match ($command) {
            'start' => ['systemctl', 'start', 'nginx'],
            'restart' => ['systemctl', 'restart', 'nginx'],
            'status' => ['systemctl', 'status', 'nginx'],
            'reload' => ['nginx', '-s', 'reload'],
            'stop' => ['systemctl', 'stop', 'nginx'],
            'test' => ['nginx', '-t'],
            default => throw new \InvalidArgumentException("Unknown command '$command'")
        };
    }
}
