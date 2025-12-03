<?php

namespace App\Helpers;

class ApiResult
{
    protected function __construct(
        public bool $created = false,
        public bool $queued = false,
        public bool $exists = false,
        public ?string $link = null,
        public ?string $message = null,
        public ?int $status_code = null,
    ) {}

    public static function created(string $link): self
    {
        return new self(created: true, link: $link, status_code: 201);
    }

    public static function queued(?string $link = null): self
    {
        return new self(queued: true, link: $link, status_code: 202);
    }

    public static function exists(?string $link = null): self
    {
        return new self(exists: true, link: $link, status_code: 409);
    }

    public static function error(string $message, int $code = 500): self
    {
        return new self(
            created: false,
            queued: false,
            exists: false,
            link: null,
            message: $message,
            status_code: $code
        );
    }

    public function isCreated(): bool
    {
        return $this->created;
    }

    public function isQueued(): bool
    {
        return $this->queued;
    }
}
