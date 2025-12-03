<?php

namespace App\Exceptions;

use Throwable;

class NginxRedisException extends \Exception
{
    private bool $status = false;

    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        $this->status = $code === 200;
        parent::__construct($message, $code, $previous);
    }

    public function getStatus(): bool
    {
        return $this->status;
    }
}
