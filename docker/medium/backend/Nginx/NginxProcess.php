<?php


namespace Nginx;
class NginxProcess
{
    protected static string $service = 'systemctl';

    private static function runCommand(array $cmd): array
    {
        $commandString = implode(' ', array_map('escapeshellarg', $cmd));

        $descriptors = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($commandString, $descriptors, $pipes);

        if (!is_resource($process)) {
            return self::result(false, '', 'Failed to start process');
        }

        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        return self::result(
            $exitCode === 0,
            $exitCode === 0 ? trim($stdout) . trim($stderr) : trim($stdout),
            $exitCode === 0 ? '' : trim($stderr)
        );
    }

    private static function result(bool $success, string $output, string $error): array
    {
        return [
            'success' => $success,
            'output' => $output,
            'error' => $error,
        ];
    }


    // commands


    public static function reload(): array
    {
        return self::runCommand(['nginx', '-s', 'reload']);
    }

    public static function restart(): array
    {
        return self::runCommand([static::$service, 'restart', 'nginx']);
    }

    public static function start(): array
    {
        return self::runCommand([static::$service, 'start', 'nginx']);
    }

    public static function stop(): array
    {
        return self::runCommand([static::$service, 'stop', 'nginx']);
    }

    public static function test(): array
    {
        return self::runCommand(['nginx', '-t']);
    }

    public static function status(): array
    {
        return self::runCommand([static::$service, 'status', 'nginx']);
    }
}