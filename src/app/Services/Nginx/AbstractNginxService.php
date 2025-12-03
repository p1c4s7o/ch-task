<?php

namespace App\Services\Nginx;

use App\Contracts\Nginx\NginxHostInterface;
use App\Domain\Nginx\Enums\NginxApiVersion;
use App\Domain\Nginx\Exceptions\HostException;
use App\Domain\Nginx\Exceptions\StubException;
use App\Services\Nginx\Traits\Domain\DomainNameTrait;
use App\Services\Nginx\Traits\Domain\DomainPortTrait;
use App\Services\Nginx\Traits\Domain\DomainTemplateTrait;
use App\Services\Nginx\Traits\Stub\StubLoaderTrait;
use App\Services\Nginx\Traits\Stub\StubNameValidationTrait;
use App\Services\Nginx\Traits\Stub\StubRemoveTrait;
use App\Services\Nginx\Traits\Stub\StubReplacerTrait;
use App\Services\Nginx\Traits\Stub\StubWriterTrait;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractNginxService implements NginxHostInterface
{
    use DomainNameTrait, DomainPortTrait, DomainTemplateTrait,
        StubLoaderTrait, StubNameValidationTrait, StubRemoveTrait,
        StubReplacerTrait, StubWriterTrait;

    abstract public function delete(string $domain, ?int $port = null): bool;

    abstract public function create(string $domain, ?int $port = null): bool;

    /**
     * @throws HostException
     */
    abstract protected function stubNaming(NginxApiVersion $version = NginxApiVersion::V1): string;

    /**
     * @throws StubException
     * @throws HostException
     */
    abstract protected function loadStub(NginxApiVersion $version = NginxApiVersion::V1): string;

    /**
     * @throws HostException
     */
    public function __construct(
        private readonly Filesystem $fs,
        private readonly string $stubDir,
        private readonly string $destDir,
        private readonly bool $portRequire = false
    ) {
        $tStubDir = realpath($stubDir);
        $tDestDir = realpath($destDir);

        if ($tStubDir === false || ! is_dir($tStubDir)) {
            throw new HostException("StubDir not found or inaccessible: $stubDir");
        }

        if ($tDestDir === false || ! is_dir($tDestDir)) {
            throw new HostException("DomainDir not found or inaccessible: $destDir");
        }

        if (! is_readable($tStubDir)) {
            throw new HostException("StubDir is not readable: $stubDir");
        }

        if (! is_writable($tDestDir)) {
            throw new HostException("DomainDir is not writable: $destDir");
        }
    }

    protected function getStubDir(): string
    {
        return $this->stubDir;
    }

    protected function getDestDir(): string
    {
        return $this->destDir;
    }

    protected function requiresPort(): bool
    {
        return $this->portRequire;
    }

    protected function getFilesystem(): Filesystem
    {
        return $this->fs;
    }
}
