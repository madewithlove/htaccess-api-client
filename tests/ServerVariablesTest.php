<?php

declare(strict_types=1);

namespace Madewithlove;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ServerVariablesTest extends TestCase
{
    /**
     * @test
     * @dataProvider providesInvalidServerVariableNames
     */
    public function it does not support variables using unsupported characters(string $serverVariableName): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported server variable: ' . $serverVariableName);
        ServerVariables::default()->with($serverVariableName, 'bar');
    }

    public function providesInvalidServerVariableNames(): array
    {
        return [
            [
                'PERCENTAGE_NOT_ALLOWED%',
            ],
            [
                'CURLY_BRACE_NOT_ALLOWED{',
            ],
            [
                'CURLY_BRACE_NOT_ALLOWED}',
            ],
            [
                'DOLLAR_NOT_ALLOWED$',
            ],
            [
                'CARRET_NOT_ALLOWED^',
            ],
            [
                '%ALSO_NOT_ALLOWED_IN_BEGINNING_OF_LINE',
            ],
            [
                'ALSO_NOT_ALLOWED_IN_%MIDDLE_OF_LINE',
            ],
        ];
    }

    /** @test */
    public function it holds supported server variables(): void
    {
        $serverVariables = ServerVariables::default()
            ->with(ServerVariable::HTTP_REFERER, 'example.com');

        $this->assertTrue($serverVariables->has(ServerVariable::HTTP_REFERER));
        $this->assertEquals('example.com', $serverVariables->get(ServerVariable::HTTP_REFERER));

        $this->assertFalse($serverVariables->has(ServerVariable::SERVER_NAME));
        $this->assertEquals('', $serverVariables->get(ServerVariable::SERVER_NAME));
    }

    /** @test */
    public function it is immutable(): void
    {
        $original = ServerVariables::default();
        $clone = $original->with(ServerVariable::SERVER_NAME, 'example.com');

        $this->assertNotSame($original, $clone);
        $this->assertFalse($original->has(ServerVariable::SERVER_NAME));
    }
}
