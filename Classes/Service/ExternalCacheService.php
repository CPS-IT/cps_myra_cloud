<?php
declare(strict_types=1);

namespace CPSIT\CpsMyraCloud\Service;


use CPSIT\CpsMyraCloud\AdapterProvider\ExternalCacheProvider;

class ExternalCacheService
{
    private PageService $pageService;
    private SiteService $siteService;

    /**
     * @param PageService $pageService
     * @param SiteService $siteService
     */
    public function __construct(PageService $pageService, SiteService $siteService)
    {
        $this->pageService = $pageService;
        $this->siteService = $siteService;
    }

    /**
     * @param int $pageUid
     * @param bool $clearAll
     * @return bool
     */
    public function clearPage(int $pageUid, bool $clearAll = false): bool
    {
        $page = $this->pageService->getPage($pageUid);
        $sites = $this->siteService->getSitesForClearance($page, $clearAll);
        $providerItem = ExternalCacheProvider::getDefaultProviderItem();

        $result = 0;
        if ($clearAll) {
            foreach ($sites as $site) {
                $result |= $providerItem->getAdapter()->clearSiteCache($site);
            }
        } elseif ($page) {
            foreach ($sites as $site) {
                $result |= $providerItem->getAdapter()->clearPageCache($site, $page);
            }
        }

        return (bool)$result;
    }
}