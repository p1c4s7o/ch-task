<?php

namespace App\Services\Nginx;

use App\Contracts\Nginx\NginxStorageInterface;
use App\Domain\Nginx\Enums\NginxApiVersion;
use App\Domain\Nginx\Exceptions\HostException;
use App\Domain\Nginx\Exceptions\IOError;
use App\Domain\Nginx\Exceptions\StubException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Filesystem\Filesystem;

class NginxStore extends AbstractDomainService implements NginxStorageInterface
{
    /**
     * @throws HostException
     */
    final public function __construct(Filesystem $fs, string $stubDir, string $domainDir, private readonly ?NginxApiVersion $version = NginxApiVersion::V1)
    {
        parent::__construct(config('nginx.stubs_prefix.store', 'not_found_store_'), $fs, $stubDir, $domainDir, $this->version === NginxApiVersion::V2);
    }

    /**
     * @throws HostException
     * @throws IOError
     */
    public function delete(string $domain, ?int $port = null): bool
    {
        $this->validateDomainAndPort($domain, $port);

        return $this->deleteDir(self::getPathDomainName($domain, $port));
    }

    /**
     * @throws HostException
     * @throws StubException|IOError
     *
     * @see createIndexFile
     * @deprecated Use createIndexFile() instead.
     */
    public function create(string $domain, ?int $port = null): bool
    {
        Log::warning(sprintf(
            'Deprecated method %s::%s() called. Use %s::createIndexFile instead.',
            self::class,
            __METHOD__,
            self::class
        ));

        return $this->createIndexFile($domain, $port);
    }

    /**
     * @throws HostException
     * @throws IOError
     * @throws StubException
     */
    public function createIndexFile(string $domain, ?int $port = null): bool
    {
        $this->validateDomainAndPort($domain, $port);

        $stub = $this->loadStub($this->version);
        $conf = $this->createHostData($stub, $domain, $port);

        return $this->write($this->getPathDomainName($domain, $port).DIRECTORY_SEPARATOR.'index.html', $conf, false);
    }

    /**
     * @throws HostException
     */
    public function exists(string $domain, ?int $port = null): bool
    {
        $this->validateDomainAndPort($domain, $port);

        return file_exists(
            rtrim($this->getDestDir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.
            $this->getPathDomainName($domain, $port).DIRECTORY_SEPARATOR.'index.html');
    }
}
