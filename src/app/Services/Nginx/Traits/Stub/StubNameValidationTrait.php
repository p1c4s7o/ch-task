<?php

namespace App\Services\Nginx\Traits\Stub;

use App\Domain\Nginx\Exceptions\StubException;

trait StubNameValidationTrait
{
    /**
     * @throws StubException
     */
    protected function validateStubName(string $name): void
    {
        $len = strlen($name);
        if ($len < 1 || $len > 255) {
            throw new StubException('Name length is invalid (1–255)');
        }

        if (! preg_match('/^[a-z0-9_]+$/', $name)) {
            throw new StubException('Invalid name: allowed only a-z, 0-9, _');
        }
    }
}
