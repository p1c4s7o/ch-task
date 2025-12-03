<?php

namespace App\Domain\Nginx\Factories;

use App\Contracts\Nginx\NginxStorageInterface;
use App\Domain\Nginx\Enums\NginxApiVersion;

class NginxStoreServiceFactory
{
    public function __construct(private NginxStorageInterface $v1, private NginxStorageInterface $v2) {}

    public function get(NginxApiVersion $version): NginxStorageInterface
    {
        return match ($version) {
            NginxApiVersion::V1 => $this->v1,
            NginxApiVersion::V2 => $this->v2,
        };
    }
}
