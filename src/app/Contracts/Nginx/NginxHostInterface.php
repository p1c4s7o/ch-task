<?php

namespace App\Contracts\Nginx;

interface NginxHostInterface
{
    public function create(string $domain, ?int $port = null): bool;

    public function delete(string $domain, ?int $port = null): bool;

    public function exists(string $domain, ?int $port = null): bool;
}
