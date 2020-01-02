<?php declare(strict_types=1);

namespace Madewithlove;

final class ShareResult
{
    /**
     * @var string
     */
    private $shareUrl;

    public function __construct(string $shareUrl)
    {
        $this->shareUrl = $shareUrl;
    }

    public function getShareUrl(): string
    {
        return $this->shareUrl;
    }
}
