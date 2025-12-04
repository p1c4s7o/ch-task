<?php

namespace App\Services\Nginx\Traits\Domain;

use App\Domain\Nginx\Exceptions\HostException;
use http\Exception\RuntimeException;

trait DomainPortTrait
{
    abstract protected function requiresPort(): bool;

    /**
     * @throws HostException
     */
    public function validatePort(?int $port = null): void
    {
        if (! $this->requiresPort()) {
            return;
        }

        $min = intval(config('cth.port_min', 8001));
        $max = intval(config('cth.port_max', 8005));

        if ($min > $max) {
            throw new RuntimeException("Invalid port range: minimum ($min) is greater than maximum ($max).");
        }
        if ($port < 1 || $port > 65535 || $port < $min) {
            throw new HostException("Port {$port} is not available. Expected minimum port: {$min}.");
        }
        if ($port > $max) {
            throw new HostException("Port {$port} is not available. Expected maximum port: {$max}.");
        }
    }
}
