<?php

namespace App\Domain\Nginx\Strategies;

use App\Contracts\Nginx\CommandStrategyInterface;

class ServiceStrategy implements CommandStrategyInterface
{
    /**
     * @return string[]
     */
    public function build(string $command): array
    {
        return match ($command) {
            'restart' => ['service', 'nginx', 'restart'],
            'status' => ['service', 'nginx', 'status'],
            'reload' => ['nginx', '-s', 'reload'],
            'stop' => ['service', 'nginx', 'stop'],
            'test' => ['nginx', '-t'],
            default => throw new \InvalidArgumentException("Unknown command '$command'")
        };
    }
}
