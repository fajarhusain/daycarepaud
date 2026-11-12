<?php

namespace App\Support\Api;

final readonly class GoogleIdentity
{
    public function __construct(
        public string $subject,
        public string $email,
        public string $name,
    ) {}
}
