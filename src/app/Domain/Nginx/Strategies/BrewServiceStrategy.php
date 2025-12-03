<?php

namespace App\Domain\Nginx\Strategies;

use App\Contracts\Nginx\CommandStrategyInterface;

class BrewServiceStrategy implements CommandStrategyInterface
{
    /**
     * @return string[]
     */
    public function build(string $command): array
    {
        return match ($command) {
            'stop' => ['brew', 'services', 'stop', 'nginx'],
            'start' => ['brew', 'services', 'start', 'nginx'],
            'restart' => ['brew', 'services', 'restart', 'nginx'],
            'status' => ['brew', 'services', 'info', 'nginx'],

            'reload' => ['nginx', '-s', 'reload'],
            'test' => ['nginx', '-t'],

            default => throw new \InvalidArgumentException("Unknown command '$command'"),
        };
    }
}
