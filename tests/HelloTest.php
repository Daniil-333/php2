<?php

use PHPUnit\Framework\TestCase;

class HelloTest extends TestCase
{
    public function testItLoadFromRepository(): void
    {
        $this->assertTrue(true);
    }

    public function testAdd(): void
    {
        $this->assertEquals(4, 2+2);
    }

}