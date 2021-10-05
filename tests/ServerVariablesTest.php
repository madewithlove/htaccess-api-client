<?php

declare(strict_types=1);

namespace Madewithlove;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ServerVariablesTest extends TestCase
{
    /** @test */
    public function it only allows supported variables(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ServerVariables::empty()->with('foo', 'bar');
    }

    /** @test */
    public function it holds supported server variables(): void
    {
        $serverVariables = ServerVariables::empty()
            ->with(ServerVariable::HTTP_REFERER, 'example.com');

        $this->assertTrue($serverVariables->has(ServerVariable::HTTP_REFERER));
        $this->assertEquals('example.com', $serverVariables->get(ServerVariable::HTTP_REFERER));

        $this->assertFalse($serverVariables->has(ServerVariable::SERVER_NAME));
        $this->assertEquals('', $serverVariables->get(ServerVariable::SERVER_NAME));
    }
}
