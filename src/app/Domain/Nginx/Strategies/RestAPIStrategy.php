<?php

namespace App\Domain\Nginx\Strategies;

use App\Contracts\Nginx\CommandStrategyInterface;
use RuntimeException;

class RestAPIStrategy implements CommandStrategyInterface
{
    /**
     * @param string $method
     * @param string $uri
     * @param string $token
     * @param string $endpoint
     * @return array
     */
    private function create_api(string $method, string $uri, string $token, string $endpoint): array
    {
        return [
            $method,
            implode('/', [rtrim($uri, '/') , $token, $endpoint]),
        ];
    }

    /**
     * @return string[]
     */
    public function build(string $command): array
    {
        // TODO ADD VALIDATION INTERFACE http, etc. if is possible, may be use network in container
        $base_uri = config('nginx.executor_provider');
        if(! $base_uri)
            throw new RuntimeException('URI provider is null');

        $token = config('nginx.secret_provider');
        if(! $token)
            throw new RuntimeException('token is null');

        return match ($command) {
            'restart' => $this->create_api('post', $base_uri, $token, 'restart'),
            'status' => $this->create_api('post', $base_uri, $token, 'status'),
            'reload' => $this->create_api('post', $base_uri, $token, 'reload'),
            'start' => $this->create_api('post', $base_uri, $token, 'start'),
            'stop' => $this->create_api('post', $base_uri, $token, 'stop'),
            'test' => $this->create_api('post', $base_uri, $token, 'test'),
            default => throw new \InvalidArgumentException("Unknown command '$command'")
        };
    }
}
