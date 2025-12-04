<?php

namespace app\Services\Nginx;

use App\Contracts\Nginx\CommandExecutorInterface;
use App\Domain\Nginx\ValueObjects\NginxCommandResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RestApiExecutor implements CommandExecutorInterface
{
    /**
     * Body not used
     *
     * @param string $method
     * @param string $uri
     * @return NginxCommandResult
     */
    private function simple_request_builder(string $method, string $uri): NginxCommandResult
    {
        try {
            $response = Http::send($method, $uri);
            $result = $response->json();

            if (! is_array($result)) {
                $body = $response->body() ?? '';
                Log::error(
                    "Invalid result: {$body}",
                    ['uri' => $uri]
                );
                return new NginxCommandResult(
                    false,
                    '',
                    'Internal Server Error'
                );
            }

            $success = $result['success'] ?? $response->successful();
            $output  = $result['output']  ?? ($response->body() ?? '');
            $error   = $result['error']   ?? ($response->serverError() ? 'Server error' : '');

            return new NginxCommandResult(
                (bool)$success,
                (string)$output,
                (string)$error
            );
        } catch (\Throwable $e) {
            Log::error(
                "HTTP error: {$e->getMessage()}",
                ['method' => $method, 'uri' => $uri]
            );

            return new NginxCommandResult(
                false,
                '',
                'Unexpected Error'
            );
        }
    }

    /**
     * @param  array<string[]>  $command
     */
    public function execute(array $command): NginxCommandResult
    {
        return $this->simple_request_builder(...$command);
    }
}
