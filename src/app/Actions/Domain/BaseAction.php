<?php

namespace App\Actions\Domain;

use App\Contracts\Nginx\CommandServiceInterface;
use App\Domain\Nginx\Enums\NginxApiVersion;
use App\Domain\Nginx\Exceptions\HostException;
use App\Domain\Nginx\Factories\NginxHostServiceFactory;
use App\Domain\Nginx\Factories\NginxStoreServiceFactory;
use App\Services\Nginx\NginxHost;

class BaseAction
{
    public function __construct(
        protected NginxHostServiceFactory $hostFactory,
        protected NginxStoreServiceFactory $storeFactory,
        protected CommandServiceInterface $commandService,
    ) {}

    public string $protocol = 'http';

    /**
     * @return array{0:string,1:int|null}
     *
     * @throws HostException
     */
    public function splitDomainAndPort(string $raw_domain, NginxApiVersion $version): array
    {
        /** @var NginxHost $host */
        $host = $this->hostFactory->get($version);

        return $host->splitDomainAndPort($raw_domain);
    }

    public function buildLink(NginxApiVersion $version, string $domain, ?int $port = null): string
    {
        /** @var NginxHost $host */
        $host = $this->hostFactory->get($version);

        return $this->protocol.'://'.$host->getHttpDomainName($domain, $port);
    }
}
