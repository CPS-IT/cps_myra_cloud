<?php

namespace Fr\MyraCloud\Service;

use Fr\MyraCloud\Domain\DTO\Typo3\PageIdInterface;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Site\Entity\Site;
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
     * @return array
     */
    public function getSitesForClearance(?PageIdInterface $pageId, bool $clearAll = false): array
    {
        if ($pageId && !$clearAll)
            return $this->getAllSupportedSitesForPageId($pageId);

        return $this->getAllSupportedSites();
    }

    /**
     * @return array
     */
    private function getAllSupportedSites(): array
    {
        $sites = [];
        foreach ($this->siteFinder->getAllSites(true) as $site) {
            if ($this->isSiteSupported($site)) {
                $sites[] = $site;
            }
        }

        return $sites;
    }

    /**
     * @param PageIdInterface $pageId
     * @return array
     */
    private function getAllSupportedSitesForPageId(PageIdInterface $pageId): array
    {
        try {
            $site = $this->siteFinder->getSiteByPageId($pageId->getPageId());
        } catch (\Exception $_) {
            return [];
        }

        if ($site && $this->isSiteSupported($site)) {
            return [$site];
        }

        return [];
    }

    /**
     * @param Site $site
     * @return bool
     */
    private function isSiteSupported(Site $site): bool
    {
        return !empty($site->getConfiguration()['myra_host']);
    }
}