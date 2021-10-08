<?php declare(strict_types=1);

namespace Madewithlove;

final class ShareResult
{
    public function __construct(
        private string $shareUrl
    ) {
    }

    public function getShareUrl(): string
    {
        return $this->shareUrl;
    }
}
