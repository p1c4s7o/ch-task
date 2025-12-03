<?php

namespace App\Services\Nginx\Traits\Stub;

use App\Domain\Nginx\Exceptions\IOError;
use App\Domain\Nginx\Exceptions\StubException;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

trait StubWriterTrait
{
    abstract protected function getDestDir(): string;

    abstract protected function getFilesystem(): Filesystem;

    /**
     * @throws StubException|IOError
     */
    public function write(string $name, string $content = '', bool $allowReplace = true): bool
    {
        $fs = $this->getFilesystem();
        $baseDir = rtrim($this->getDestDir(), DIRECTORY_SEPARATOR);
        $filePath = $baseDir.DIRECTORY_SEPARATOR.$name;

        $realBase = realpath($baseDir) ?: $baseDir;
        $realTarget = realpath(dirname($filePath));

        if ($realTarget !== false && ! str_starts_with($realTarget, $realBase)) {
            throw new StubException('Security error: cannot write outside destDir.');
        }
        try {
            if (! $fs->exists($baseDir)) {
                $fs->mkdir($baseDir, 0755);
            }
        } catch (IOExceptionInterface $e) {
            throw new IOError("Failed to create directory '{$baseDir}': ".$e->getMessage());
        }

        $freeSpace = disk_free_space($baseDir);
        $contentSize = mb_strlen($content, '8bit');

        if ($freeSpace !== false && $freeSpace < $contentSize) {
            throw new StubException("Not enough disk space to write '{$filePath}'.");
        }

        if (! $allowReplace) {
            if ($fs->exists($filePath)) {
                return false;
            }
        }

        try {
            $fs->dumpFile($filePath, $content);
        } catch (IOExceptionInterface $e) {
            throw new IOError("Failed to write file '{$filePath}': ".$e->getMessage());
        }

        return true;
    }
}
