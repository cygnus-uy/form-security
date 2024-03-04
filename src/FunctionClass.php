<?php

declare(strict_types=1);

namespace CygnusUy\LibraryName;

final class FunctionClass
{
    private string $LIBRARYNAME_VAR;

    public function __construct(string $LIBRARYNAME_VAR)
    {
        $this->LIBRARYNAME_VAR = $LIBRARYNAME_VAR;
    }

    public function __set($name, $value)
    {
        echo "Setting '$name' to '$value'\n";
        $this->$name = $value;
    }

    public function __get($name)
    {
        echo "Getting '$name'\n";
        if (property_exists(FunctionClass::class, $name)) {
            return $this->$name;
        }

        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE
        );
        return null;
    }
}
