<?php

declare(strict_types=1);

namespace CygnusUy\FormSecurity;

final class Config implements ConfigInterface
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function get(string $name)
    {
        return isset($this->config[$name]) ? $this->config[$name] : null;
    }

    public function set(string $name, $value): self
    {
        $this->config[$name] = $value;
        return $this;
    }

    public function has(string $name): bool
    {
        if (isset($this->config[$name])) {
            return true;
        }

        return false;
    }

    public function __toString()
    {
        return $this->config;
    }
}
