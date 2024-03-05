<?php

declare(strict_types=1);

namespace CygnusUy\FormSecurity;

interface HandlerInterface
{
    public const NOT_ENABLED_CODE = 1;
    public const CLIENT_ERROR_CODE = 2;

    public function run(array $formData): bool;

    public function getRequiredEntries(): array;
}
