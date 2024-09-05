<?php
declare(strict_types=1);

namespace CPSIT\CpsMyraCloud\Service;

use CPSIT\CpsMyraCloud\Domain\DTO\Typo3\PageIdInterface;
use CPSIT\CpsMyraCloud\Domain\DTO\Typo3\SiteConfigExternalIdentifierInterface;
use CPSIT\CpsMyraCloud\Domain\DTO\Typo3\SiteConfigInterface;
use CPSIT\CpsMyraCloud\Domain\DTO\Typo3\Typo3SiteConfig;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Site\SiteFinder;

readonly class SiteService implements SingletonInterface
{
    /**
     * @param SiteFinder $siteFinder
     */
    public function __construct(
        private SiteFinder $siteFinder
    )
    {}

    /**
     * @param PageIdInterface|null $pageId
     * @return SiteConfigInterface[]
     */
    public function getSitesForClearance(?PageIdInterface $pageId): array
    {
        if ($pageId)
            return $this->getAllSupportedSitesForPageId($pageId);

        return $this->getAllSupportedSites();
    }

    /**
     * @return SiteConfigInterface[]
     */
    private function getAllSupportedSites(): array
    {
        // TODO: caching ?
        $sites = [];
        foreach ($this->siteFinder->getAllSites() as $site) {
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
        // todo: caching?
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
        return !empty($site->getExternalIdentifierList());
    }
}
