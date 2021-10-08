<?php declare(strict_types=1);

namespace Madewithlove;

final class ResultLine
{
    public function __construct(
        private string $line,
        private string $message,
        private bool $isMet,
        private bool $isValid,
        private bool $wasReached,
        private bool $isSupported
    ) {
    }

    public function getLine(): string
    {
        return $this->line;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function isMet(): bool
    {
        return $this->isMet;
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }

    public function wasReached(): bool
    {
        return $this->wasReached;
    }

    public function isSupported(): bool
    {
        return $this->wasReached;
    }
}
