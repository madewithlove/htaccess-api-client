<?php

declare(strict_types=1);

namespace Madewithlove;

final class ServerVariable
{
    public const HTTP_REFERER = 'HTTP_REFERER';
    public const SERVER_NAME = 'SERVER_NAME';

    public const ALL = [
        self::HTTP_REFERER,
        self::SERVER_NAME,
    ];
}
