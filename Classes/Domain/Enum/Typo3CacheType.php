<?php
declare(strict_types=1);

namespace CPSIT\CpsMyraCloud\Domain\Enum;

enum Typo3CacheType: int
{
    case INVALID = -1;
    case UNKNOWN = 0;
    case PAGE = 1;
    case RESOURCE = 2;
    case ALL_PAGE = 30;
    case ALL_RESOURCES = 60;
}
