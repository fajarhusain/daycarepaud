<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientAiTokensException extends RuntimeException
{
    public function __construct(
        public readonly int $sekolahId,
        public readonly string $feature,
        public readonly string $fallbackMessage,
    ) {
        parent::__construct($fallbackMessage);
    }
}
