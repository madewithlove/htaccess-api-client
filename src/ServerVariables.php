<?php

declare(strict_types=1);

namespace Madewithlove;

use InvalidArgumentException;

class ServerVariables
{
    private array $variables = [];

    private function __construct()
    {
    }

    public static function empty(): self
    {
        return new self();
    }

    public function with(string $optionName, string $value): self
    {
        if (!in_array($optionName, ServerVariable::ALL)) {
            throw new InvalidArgumentException('Unsupported server variable: ' . $optionName);
        }

        $clone = clone $this;
        $clone->variables[$optionName] = $value;

        return $clone;
    }

    public function has(string $optionName): bool
    {
        return isset($this->variables[$optionName]);
    }

    public function get(string $optionName): string
    {
        return $this->variables[$optionName] ?? '';
    }
}
