<?php

namespace Fr\MyraCloud\Service;

use BR\Toolkit\Typo3\Cache\CacheService;
use Fr\MyraCloud\Domain\DTO\Typo3\PageIdInterface;
use Fr\MyraCloud\Domain\DTO\Typo3\SiteConfigExternalIdentifierInterface;
use Fr\MyraCloud\Domain\DTO\Typo3\SiteConfigInterface;
use Fr\MyraCloud\Domain\DTO\Typo3\Typo3SiteConfig;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Site\SiteFinder;

class SiteService implements SingletonInterface
{
    private SiteFinder $siteFinder;
    private CacheService $cacheService;

    /**
     * @param SiteFinder $siteFinder
     * @param CacheService $cacheService
     */
    public function __construct(SiteFinder $siteFinder, CacheService $cacheService)
    {
        $this->siteFinder = $siteFinder;
        $this->cacheService = $cacheService;
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
        return $this->cacheService->cache(
            'siteService_getAllSites',
            function () {
                $sites = [];
                foreach ($this->siteFinder->getAllSites(true) as $site) {
                    $siteConfig = new Typo3SiteConfig($site);
                    if ($this->isSiteSupported($siteConfig)) {
                        $sites[] = $siteConfig;
                    }
                }

                return $sites;
            },
            'MYRA_CLOUD',
            0
        );
    }

    /**
     * @param PageIdInterface $pageId
     * @return SiteConfigInterface[]
     */
    private function getAllSupportedSitesForPageId(PageIdInterface $pageId): array
    {
        return $this->cacheService->cache(
            'siteService_getSites_for_page_' . $pageId->getPageId(),
            function () use ($pageId) {
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
            },
            'MYRA_CLOUD',
            0
        );
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