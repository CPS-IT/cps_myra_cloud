<?php
declare(strict_types=1);

namespace CPSIT\CpsMyraCloud\Domain\Enum;

abstract class Typo3CacheType
{
    public const INVALID = -1;
    public const UNKNOWN = 0;
    public const PAGE = 1;
    public const FILEADMIN = 2;
}