<?php

namespace App\Services\Nginx\Traits\Stub;

use App\Domain\Nginx\Exceptions\HostException;
use App\Domain\Nginx\Exceptions\IOError;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

trait StubRemoveTrait
{
    abstract protected function getDestDir(): string;

    abstract protected function getFilesystem(): Filesystem;

    /**
     * @throws HostException|IOError
     * @throws HostException|IOError
     */
    protected function deleteFile(string $path): bool
    {
        $fs = $this->getFilesystem();
        $baseDir = rtrim($this->getDestDir(), DIRECTORY_SEPARATOR);
        $basePath = $baseDir.DIRECTORY_SEPARATOR.$path;
        $real = realpath($basePath);

        if ($real === false) {
            return false;
        }

        if (! str_starts_with($real, $baseDir)) {
            throw new HostException('Security error: cannot delete file outside destDir.');
        }

        try {
            $dir = dirname($real);
            if (! is_writable($dir)) {
                throw new IOError("Directory '$dir' is not writable. Cannot delete file.");
            }

            $fs->remove($real);
        } catch (IOExceptionInterface $e) {
            throw new IOError("Failed to delete '$real': ".$e->getMessage());
        }

        return true;
    }

    /**
     * @throws IOError
     */
    protected function deleteDir(string $dir): bool
    {
        $fs = $this->getFilesystem();
        $baseDir = rtrim($this->getDestDir(), DIRECTORY_SEPARATOR);
        $target = $baseDir.DIRECTORY_SEPARATOR.$dir;

        $realBase = realpath($baseDir) ?: $baseDir;
        $realTarget = realpath($target);

        if ($realTarget === false) {
            return false;
        }

        if (! str_starts_with($realTarget, $realBase)) {
            throw new IOError('Security error: cannot delete directory outside destDir.');
        }

        if (! is_dir($realTarget)) {
            throw new IOError("Path '{$realTarget}' is not a directory.");
        }

        if (! is_writable(dirname($realTarget))) {
            throw new IOError("Directory '".dirname($realTarget)."' is not writable.");
        }

        try {
            $fs->remove($realTarget);
        } catch (IOExceptionInterface $e) {
            throw new IOError("Failed to delete directory '{$realTarget}': ".$e->getMessage());
        }

        return true;
    }
}
