<?php

return [
    'v3_api_enable' => env('CITYHOST_V3_ENABLE', false),
    'port_min' => env('CITYHOST_PORT_MIN', 8081),
    'port_max' => env('CITYHOST_PORT_MAX', 8085),
    'stubs_path' => env('CITYHOST_STUBS_PATH', '/var/www/html/storage/app/private/stubs'),
    'domains_path' => env('CITYHOST_DOMAINS_PATH', '/etc/nginx/conf.d'),
    'public_html' => env('CITYHOST_WWW_PATH', '/var/www/html')
];
