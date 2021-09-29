<?php
declare(strict_types=1);

namespace Fr\MyraCloud\Domain\DTO\Typo3;

interface PageSlugInterface
{
    public function getSlug(): string;
}