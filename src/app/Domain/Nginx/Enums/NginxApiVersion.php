<?php

namespace App\Domain\Nginx\Enums;

enum NginxApiVersion: string
{
    case V1 = 'v1';
    case V2 = 'v2';
}
