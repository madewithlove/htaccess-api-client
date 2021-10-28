<?php

declare(strict_types=1);

namespace Madewithlove;

use InvalidArgumentException;

class ServerVariables
{
    private function __construct(
        private array $variables
    ) {
    }

    public static function default(): self
    {
        return new self([]);
    }

    public function with(string $optionName, string $value): self
    {
        if (!preg_match('/^[a-zA-Z1-9_\-:]+$/', $optionName)) {
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

    public function toArray(): array
    {
        return $this->variables;
    }
}
