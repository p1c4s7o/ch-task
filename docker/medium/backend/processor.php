<?php

$dir = dirname($_SERVER['SCRIPT_FILENAME']);

function json_response(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function error_json_response(string $message, int $code = 500):void
{
    json_response([
        'success' => false,
        'output' => '',
        'error' => $message,
    ], $code);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST')
    error_json_response('Method not allowed', 405);


$path = explode('/', trim($_SERVER['REQUEST_URI'], '/'));

if (count($path) < 2 || strlen($path[1]) < 1)
    error_json_response('Token not found', 401);

[$token, $fn] = $path;

if ($token !== getenv('SEC_TOKEN'))
    error_json_response('Forbidden', 403);


require_once $dir . '/Nginx/NginxProcess.php';
require_once $dir . '/Nginx/Supervisor.php';

if (is_callable([\Nginx\Supervisor::class, $fn])) {
    $result = call_user_func([\Nginx\Supervisor::class, $fn]);

    json_response(
        $result,
        $result['success'] ? 200 : 500
    );
}

error_json_response('Command not found', 404);


