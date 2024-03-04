<?php

declare(strict_types=1);

use CygnusUy\LibraryName\FunctionClass;
use PHPUnit\Framework\TestCase;

class LibraryNameTest extends TestCase
{
    private string $LIBRARYNAME_VAR;

    private function init()
    {
        $this->LIBRARYNAME_VAR = getenv('LIBRARYNAME_VAR') ? getenv('LIBRARYNAME_VAR') : (isset($_ENV['LIBRARYNAME_VAR']) ? $_ENV['LIBRARYNAME_VAR'] : null);

        $this->assertNotEmpty($this->LIBRARYNAME_VAR);
    }

    public function testMerchantOrder()
    {
        $this->init();

        $functionInstance = new FunctionClass($this->LIBRARYNAME_VAR);

        $this->assertNotEmpty($functionInstance);

        $this->assertIsString($functionInstance->LIBRARYNAME_VAR);
    }
}
