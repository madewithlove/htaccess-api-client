<?php

declare(strict_types=1);

namespace Madewithlove;

/**
 * @deprecated use regular strings instead of the constants included in this class
 */
final class ServerVariable
{
    public const HTTP_REFERER = 'HTTP_REFERER';
    public const HTTP_USER_AGENT = 'HTTP_USER_AGENT';
    public const SERVER_NAME = 'SERVER_NAME';
    public const REMOTE_ADDR = 'REMOTE_ADDR';

    public const ALL = [
        self::HTTP_REFERER,
        self::HTTP_USER_AGENT,
        self::SERVER_NAME,
        self::REMOTE_ADDR,
    ];
}
