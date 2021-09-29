<?php

namespace Fr\MyraCloud\Service;

use Fr\MyraCloud\Domain\DTO\Typo3\PageIdInterface;
use Fr\MyraCloud\Domain\DTO\Typo3\SiteConfigExternalIdentifierInterface;
use Fr\MyraCloud\Domain\DTO\Typo3\SiteConfigInterface;
use Fr\MyraCloud\Domain\DTO\Typo3\Typo3SiteConfig;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Site\SiteFinder;

class SiteService implements SingletonInterface
{
    private SiteFinder $siteFinder;

    /**
     * @param SiteFinder $siteFinder
     */
    public function __construct(SiteFinder $siteFinder)
    {
        $this->siteFinder = $siteFinder;
    }

    /**
     * @param PageIdInterface|null $pageId
     * @param bool $clearAll
     * @return SiteConfigInterface[]
     */
    public function getSitesForClearance(?PageIdInterface $pageId, bool $clearAll = false): array
    {
        if ($pageId && !$clearAll)
            return $this->getAllSupportedSitesForPageId($pageId);

        return $this->getAllSupportedSites();
    }

    /**
     * @return SiteConfigInterface[]
     */
    private function getAllSupportedSites(): array
    {
        $sites = [];
        foreach ($this->siteFinder->getAllSites(true) as $site) {
            $siteConfig = new Typo3SiteConfig($site);
            if ($this->isSiteSupported($siteConfig)) {
                $sites[] = $siteConfig;
            }
        }

        return $sites;
    }

    /**
     * @param PageIdInterface $pageId
     * @return SiteConfigInterface[]
     */
    private function getAllSupportedSitesForPageId(PageIdInterface $pageId): array
    {
        try {
            $site = $this->siteFinder->getSiteByPageId($pageId->getPageId());
            $siteConfig = new Typo3SiteConfig($site);
        } catch (\Exception $_) {
            return [];
        }

        if ($this->isSiteSupported($siteConfig)) {
            return [$siteConfig];
        }

        return [];
    }

    /**
     * @param SiteConfigExternalIdentifierInterface $site
     * @return bool
     */
    private function isSiteSupported(SiteConfigExternalIdentifierInterface $site): bool
    {
        return !empty($site->getExternalIdentifier());
    }
}