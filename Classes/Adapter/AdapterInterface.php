<?php
declare(strict_types=1);

namespace CPSIT\CpsMyraCloud\Adapter;

use CPSIT\CpsMyraCloud\Domain\DTO\Typo3\PageSlugInterface;
use CPSIT\CpsMyraCloud\Domain\DTO\Typo3\SiteConfigInterface;

interface AdapterInterface extends AdapterRegisterInterface
{
    public function canExecute(): bool;

    public function clearSiteCache(SiteConfigInterface $site): bool;

    public function clearPageCache(SiteConfigInterface $site, PageSlugInterface $pageSlug): bool;
}