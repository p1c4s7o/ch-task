<?php

namespace App\Domain\Nginx\Factories;

use App\Contracts\Nginx\NginxHostInterface;
use App\Domain\Nginx\Enums\NginxApiVersion;

class NginxHostServiceFactory
{
    public function __construct(private readonly NginxHostInterface $v1, private readonly NginxHostInterface $v2) {}

    public function get(NginxApiVersion $version): NginxHostInterface
    {
        return match ($version) {
            NginxApiVersion::V1 => $this->v1,
            NginxApiVersion::V2 => $this->v2,
        };
    }
}
