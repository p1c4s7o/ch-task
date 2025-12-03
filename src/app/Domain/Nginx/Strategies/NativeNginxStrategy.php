<?php

namespace App\Domain\Nginx\Strategies;

use App\Contracts\Nginx\CommandStrategyInterface;

class NativeNginxStrategy implements CommandStrategyInterface
{
    /**
     * @return string[]
     */
    public function build(string $command): array
    {
        return match ($command) {
            'start' => ['nginx', '-s', 'reload'],
            'restart' => ['nginx', '-s', 'reload'],
            'reload' => ['nginx', '-s', 'reload'],
            'status' => ['pgrep', 'nginx'],
            'stop' => ['nginx', '-s', 'stop'],
            'test' => ['nginx', '-t'],
            default => throw new \InvalidArgumentException("Unknown command '$command'")
        };
    }
}
