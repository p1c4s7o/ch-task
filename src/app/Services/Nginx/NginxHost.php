<?php

namespace App\Services\Nginx;

use App\Domain\Nginx\Enums\NginxApiVersion;
use App\Domain\Nginx\Exceptions\HostException;
use App\Domain\Nginx\Exceptions\IOError;
use App\Domain\Nginx\Exceptions\StubException;
use Symfony\Component\Filesystem\Filesystem;

class NginxHost extends AbstractDomainService
{
    /**
     * @throws HostException
     */
    final public function __construct(Filesystem $fs, string $stubDir, string $domainDir, private readonly ?NginxApiVersion $version = NginxApiVersion::V1)
    {
        parent::__construct(config('nginx.stubs_prefix.host', 'not_found_host_'), $fs, $stubDir, $domainDir, $this->version === NginxApiVersion::V2);
    }

    /**
     * @throws HostException|IOError
     */
    public function delete(string $domain, ?int $port = null): bool
    {
        $this->validateDomainAndPort($domain, $port);

        return $this->deleteFile($this->getPathDomainName($domain, $port).'.conf');
    }

    /**
     * @throws HostException
     * @throws StubException|IOError
     */
    public function create(string $domain, ?int $port = null): bool
    {
        $this->validateDomainAndPort($domain, $port);

        $stub = $this->loadStub($this->version);
        $conf = $this->createHostData($stub, $domain, $port);

        if (! self::write(self::getPathDomainName($domain, $port).'.conf', $conf, false)) {
            throw new HostException('Host '.self::getHttpDomainName($domain, $port).' exists');
        }

        return true;
    }

    /**
     * @throws HostException
     */
    public function exists(string $domain, ?int $port = null): bool
    {
        $this->validateDomainAndPort($domain, $port);

        return file_exists(
            rtrim($this->getDestDir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.
            $this->getPathDomainName($domain, $port).'.conf');
    }
}
