<?php

namespace App\Services\Nginx\Traits\Domain;

use App\Domain\Nginx\Exceptions\HostException;
use App\Services\Nginx\Traits\Stub\StubReplacerTrait;

trait DomainTemplateTrait
{
    use DomainNameTrait, DomainPortTrait, StubReplacerTrait;

    /**
     * @throws HostException
     */
    public function validateDomainAndPort(string $domain, ?int $port = null): void
    {
        $this->validateDomainName($domain);
        $this->validatePort($port);
    }

    protected function createHostData(string $content, string $domain, ?int $port = null): string
    {
        return $this->replaceAll([
            'DOMAIN_NAME' => $domain,
            'PORT' => $port,
        ], $content);
    }
}
