<?php

namespace App\Services\Nginx;

use App\Domain\Nginx\Enums\NginxApiVersion;
use App\Domain\Nginx\Exceptions\HostException;
use App\Domain\Nginx\Exceptions\StubException;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractDomainService extends AbstractNginxService
{
    /**
     * @throws HostException
     */
    public function __construct(private string $naming, Filesystem $fs, string $stubDir, string $domainDir, bool $portRequire = false)
    {
        parent::__construct($fs, $stubDir, $domainDir, $portRequire);
    }

    abstract public function delete(string $domain, ?int $port = null): bool;

    abstract public function create(string $domain, ?int $port = null): bool;

    /**
     * @throws HostException
     */
    protected function stubNaming(NginxApiVersion $version = NginxApiVersion::V1): string
    {
        if (! in_array($version, [NginxApiVersion::V1, NginxApiVersion::V2])) {
            throw new HostException('Host version is invalid. Expected version 1 or 2');
        }

        return $this->naming.$version->value;
    }

    /**
     * @throws StubException
     * @throws HostException
     */
    protected function loadStub(NginxApiVersion $version = NginxApiVersion::V1): string
    {
        return $this->load($this->stubNaming($version));
    }

    //    /**
    //     * @param string $domain
    //     * @param int|null $port
    //     * @return string
    //     */
    //    public function getPathDomainName(string $domain, ?int $port = null): string
    //    {
    //        return parent::getPathDomainName($domain, $port);
    //    }
    //
    //    /**
    //     * @param string $domain
    //     * @param int|null $port
    //     * @return string
    //     */
    //    public function getHttpDomainName(string $domain, ?int $port = null): string
    //    {
    //        return parent::getHttpDomainName($domain, $port);
    //    }
}
