<?php

declare(strict_types=1);

namespace CygnusUy\FormSecurity;

interface ConfigInterface
{
    public function get(string $name);

    public function set(string $name, $value): self;

    public function has(string $name): bool;
}
