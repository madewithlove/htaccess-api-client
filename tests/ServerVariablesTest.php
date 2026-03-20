<?php

declare(strict_types=1);

namespace Madewithlove\HtaccessApiClient;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ServerVariablesTest extends TestCase
{
    #[Test]
    #[DataProvider('providesInvalidServerVariableNames')]
    public function it_does_not_support_variables_using_unsupported_characters(string $serverVariableName): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported server variable: ' . $serverVariableName);
        ServerVariables::default()->with($serverVariableName, 'bar');
    }

    /**
     * @return array<int, array<string>>
     */
    public static function providesInvalidServerVariableNames(): array
    {
        return [
            ['PERCENTAGE_NOT_ALLOWED%'],
            ['CURLY_BRACE_NOT_ALLOWED{'],
            ['CURLY_BRACE_NOT_ALLOWED}'],
            ['DOLLAR_NOT_ALLOWED$'],
            ['CARRET_NOT_ALLOWED^'],
            ['%ALSO_NOT_ALLOWED_IN_BEGINNING_OF_LINE'],
            ['ALSO_NOT_ALLOWED_IN_%MIDDLE_OF_LINE'],
        ];
    }

    #[Test]
    public function it_holds_supported_server_variables(): void
    {
        $serverVariables = ServerVariables::default()
            ->with('HTTP_REFERER', 'example.com');

        $this->assertTrue($serverVariables->has('HTTP_REFERER'));
        $this->assertEquals('example.com', $serverVariables->get('HTTP_REFERER'));

        $this->assertFalse($serverVariables->has('SERVER_NAME'));
        $this->assertEquals('', $serverVariables->get('SERVER_NAME'));
    }

    #[Test]
    public function it_is_immutable(): void
    {
        $original = ServerVariables::default();
        $clone = $original->with('SERVER_NAME', 'example.com');

        $this->assertNotSame($original, $clone);
        $this->assertFalse($original->has('SERVER_NAME'));
    }
}
