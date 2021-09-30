<?php
declare(strict_types=1);

namespace CPSIT\CpsMyraCloud\Service;


use CPSIT\CpsMyraCloud\AdapterProvider\ExternalCacheProvider;
use CPSIT\CpsMyraCloud\Domain\Enum\Typo3CacheType;

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

    public function clear(int $type, string $identifier, bool $clearAll): bool
    {
        if ($type === Typo3CacheType::PAGE) {
            return $this->clearPage((int)$identifier, $clearAll);
        }

        return false;
    }

    /**
     * @param int $pageUid
     * @param bool $clearAll
     * @return bool
     */
    private function clearPage(int $pageUid, bool $clearAll = false): bool
    {
        $page = $this->pageService->getPage($pageUid);
        $sites = $this->siteService->getSitesForClearance($page, $clearAll);
        $providerItem = ExternalCacheProvider::getDefaultProviderItem();

        $result = 0;
        if ($clearAll) {
            foreach ($sites as $site) {
                //$result |= $providerItem->getAdapter()->clearSiteCache($site);
            }
        } elseif ($page) {
            foreach ($sites as $site) {
                //$result |= $providerItem->getAdapter()->clearPageCache($site, $page);
            }
        }

        return (bool)$result;
    }
}