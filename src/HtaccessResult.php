<?php declare(strict_types=1);

namespace Madewithlove;

final class HtaccessResult
{
    /**
     * @param ResultLine[] $lines
     */
    public function __construct(
        private string $outputUrl,
        private array $lines,
        private ?int $outputStatusCode
    ) {
    }

    public function getOutputUrl(): string
    {
        return $this->outputUrl;
    }

    /**
     * @return ResultLine[]
     */
    public function getLines(): array
    {
        return $this->lines;
    }

    public function getOutputStatusCode(): ?int
    {
        return $this->outputStatusCode;
    }
}
