<?php

namespace App\Services\Nginx\Traits\Stub;

use App\Domain\Nginx\Exceptions\StubException;
use Symfony\Component\Filesystem\Filesystem;

trait StubLoaderTrait
{
    abstract protected function getStubDir(): string;

    abstract protected function getFilesystem(): Filesystem;

    use StubNameValidationTrait;

    /**
     * @throws StubException
     */
    protected function load(string $name): string
    {
        $this->validateStubName($name);
        $fs = $this->getFilesystem();
        $stubPath = rtrim($this->getStubDir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR."$name.stub";
        $cacheKey = 'stub_file_'.sha1($stubPath);

        return \Cache::remember($cacheKey, 3600, function () use ($fs, $stubPath) {
            if (! $fs->exists($stubPath)) {
                throw new StubException("$stubPath not found");
            }

            if (! is_readable($stubPath)) {
                throw new StubException("$stubPath is not readable");
            }

            try {
                $content = file_get_contents($stubPath);
            } catch (\Throwable $e) {
                throw new StubException("Failed to read file: $stubPath. Error: ".$e->getMessage());
            }

            if ($content === false) {
                throw new StubException("Failed to read file: $stubPath.");
            }

            return $content;
        });
    }
}
