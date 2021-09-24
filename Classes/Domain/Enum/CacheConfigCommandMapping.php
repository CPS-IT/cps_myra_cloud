<?php
declare(strict_types=1);

namespace Fr\MyraCloud\Domain\Enum;

abstract class CacheConfigCommandMapping
{
    public const UNKNOWN = 0;
    public const PAGES = 1;
    public const PAGE = 2;

    /**
     * @var int[]
     */
    public static $mapping = [
        '-1' => self::UNKNOWN,
        'all' => self::PAGES,
        'pages' => self::PAGES,
    ];
}