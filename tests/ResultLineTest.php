<?php

declare(strict_types=1);

namespace Madewithlove;

use PHPStan\Testing\TestCase;

class ResultLineTest extends TestCase
{
    /** @test */
    public function it correctly returns all properties(): void
    {
        $resultLine = new ResultLine('foo', 'bar', true, true, true, true);
        $this->assertEquals('foo', $resultLine->getLine());
        $this->assertEquals('bar', $resultLine->getMessage());
        $this->assertTrue($resultLine->isMet());
        $this->assertTrue($resultLine->isValid());
        $this->assertTrue($resultLine->isSupported());
        $this->assertTrue($resultLine->wasReached());

        $resultLine = new ResultLine('foo', 'bar', false, true, true, true);
        $this->assertFalse($resultLine->isMet());

        $resultLine = new ResultLine('foo', 'bar', true, false, true, true);
        $this->assertFalse($resultLine->isValid());

        $resultLine = new ResultLine('foo', 'bar', true, true, false, true);
        $this->assertFalse($resultLine->wasReached());

        $resultLine = new ResultLine('foo', 'bar', true, true, true, false);
        $this->assertFalse($resultLine->isSupported());
    }
}
