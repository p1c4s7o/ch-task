<?php

namespace App\Contracts\Nginx;

interface NginxStorageInterface
{
    public function createIndexFile(string $domain, ?int $port = null): bool;
}
