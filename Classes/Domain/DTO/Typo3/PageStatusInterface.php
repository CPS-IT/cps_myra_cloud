<?php
declare(strict_types=1);

namespace Fr\MyraCloud\Domain\DTO\Typo3;

interface PageStatusInterface
{
    public function getDokType(): int;

    /**
     * is hidden or not
     *
     * @return bool
     */
    public function getVisibility(): bool;
}