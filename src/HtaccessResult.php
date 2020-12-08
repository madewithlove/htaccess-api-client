<?php declare(strict_types=1);

namespace Madewithlove;

final class HtaccessResult
{
    /**
     * @var string
     */
    private $outputUrl;

    /**
     * @var ResultLine[]
     */
    private $lines = [];

    /**
     * @var int?
     */
    private $outputStatusCode;

    public function __construct(
        string $outputUrl,
        array $lines,
        ?int $outputStatusCode
    ) {
        $this->outputUrl = $outputUrl;
        $this->outputStatusCode = $outputStatusCode;

        foreach ($lines as $line) {
            $this->addLine($line);
        }
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

    private function addLine(ResultLine $line): void
    {
        $this->lines[] = $line;
    }
}
