<?php

namespace App\Services\Nginx\Traits\Domain;

use App\Domain\Nginx\Exceptions\HostException;
use DomainException;

trait DomainNameTrait
{
    abstract protected function requiresPort(): bool;

    /**
     * @throws HostException
     */
    public function validateDomainName(string $name): void
    {
        $len = strlen($name);
        if ($len < 1 || $len > 255) {
            throw new HostException('Name length is invalid (1–255)');
        }

        if (! preg_match('/^[a-z0-9-]+$/', $name)) {
            throw new HostException('Invalid name: allowed only a-z, 0-9, -');
        }
    }

    public function getPathDomainName(string $domain, ?int $port = null): string
    {
        if ($this->requiresPort()) {
            $domain .= ".{$port}";
        }

        return $domain;
    }

    /**
     * @return array{0:string,1:int|null}
     *
     * @throws DomainException
     * @throws HostException
     */
    public function splitDomainAndPort(string $domain): array
    {
        $host = '';
        $port = null;

        if (str_contains($domain, ':')) {
            [$host, $port] = explode(':', $domain, 2);

            if (strlen($port) > strlen('00000')) {
                throw new DomainException('Port range size is out of allowed boundaries.');
            }

            $port = intval($port);
        } else {
            $host = $domain;
        }

        $this->validateDomainAndPort($host, $port);

        return [$host, $port];
    }

    public function getHttpDomainName(string $domain, ?int $port = null): string
    {
        if ($this->requiresPort()) {
            $domain .= ":{$port}";
        }

        return $domain;
    }
}
