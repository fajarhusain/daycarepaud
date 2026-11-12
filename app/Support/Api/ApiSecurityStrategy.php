<?php

namespace App\Support\Api;

use Dedoc\Scramble\SecurityDocumentation\MiddlewareAuthSecurityStrategy;
use Dedoc\Scramble\Support\Generator\SecurityScheme;

final class ApiSecurityStrategy extends MiddlewareAuthSecurityStrategy
{
    public function __construct()
    {
        parent::__construct(
            ['auth:sanctum'],
            SecurityScheme::http('bearer', 'Sanctum')
                ->as('sanctum')
                ->setDescription('Masukkan Bearer token dari endpoint login.'),
        );
    }
}
