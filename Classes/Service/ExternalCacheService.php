<?php
declare(strict_types=1);

namespace CPSIT\CpsMyraCloud\Service;


use CPSIT\CpsMyraCloud\Adapter\AdapterInterface;
use CPSIT\CpsMyraCloud\AdapterProvider\ExternalCacheProvider;
use CPSIT\CpsMyraCloud\Domain\DTO\Typo3\File\FileAdmin;
use CPSIT\CpsMyraCloud\Domain\DTO\Typo3\File\Typo3Conf;
use CPSIT\CpsMyraCloud\Domain\DTO\Typo3\File\Typo3Core;
use CPSIT\CpsMyraCloud\Domain\DTO\Typo3\File\Typo3Temp;
use CPSIT\CpsMyraCloud\Domain\DTO\Typo3\PageSlugInterface;
use CPSIT\CpsMyraCloud\Domain\DTO\Typo3\SiteConfigInterface;
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

    /**
     * @param int $type
     * @param string $identifier
     * @return bool
     */
    public function clear(int $type, string $identifier): bool
    {
        if ($type === Typo3CacheType::PAGE) {
            return $this->clearPage((int)$identifier);
        } elseif ($type === Typo3CacheType::FILE_ADMIN) {
            return $this->clearFile($identifier);
        } elseif ($type === Typo3CacheType::ALL_PAGE) {
            return $this->clearAllPages();
        } elseif ($type === Typo3CacheType::ALL_RESOURCES) {
            return $this->clearAllFiles();
        }

        return false;
    }

    /**
     * @param int $pageUid
     * @return bool
     */
    private function clearPage(int $pageUid): bool
    {
        $page = $this->pageService->getPage($pageUid);
        $sites = $this->siteService->getSitesForClearance($page);
        $providerItem = ExternalCacheProvider::getDefaultProviderItem();

        return $this->clearCacheWithAdapter($providerItem->getAdapter(), $sites, $page);
    }

    /**
     * @return bool
     */
    private function clearAllPages(): bool
    {
        $sites = $this->siteService->getSitesForClearance(null, true);
        $providerItem = ExternalCacheProvider::getDefaultProviderItem();

        return $this->clearCacheWithAdapter($providerItem->getAdapter(), $sites, null, true);
    }

    /**
     * @return bool
     */
    private function clearAllFiles(): bool
    {
        $sites = $this->siteService->getSitesForClearance(null, true);
        $providerItem = ExternalCacheProvider::getDefaultProviderItem();
        $fileCaches = [new FileAdmin(), new Typo3Temp(), new Typo3Conf(), new Typo3Core()];

        $result = 0;
        foreach ($fileCaches as $file) {
            $result |= $this->clearCacheWithAdapter($providerItem->getAdapter(), $sites, $file, true);
        }

        return (bool)$result;
    }

    /**
     * @param string $relPath
     * @return bool
     */
    private function clearFile(string $relPath): bool
    {
        $file = new FileAdmin($relPath);
        $sites = $this->siteService->getSitesForClearance(null, true);
        $providerItem = ExternalCacheProvider::getDefaultProviderItem();

        // files are always recursive deleted
        return $this->clearCacheWithAdapter($providerItem->getAdapter(), $sites, $file, true);
    }

    /**
     * @param AdapterInterface $adapter
     * @param SiteConfigInterface[] $sites
     * @param PageSlugInterface|null $slug
     * @param bool $recursive
     * @return bool
     */
    private function clearCacheWithAdapter(AdapterInterface $adapter, array $sites, ?PageSlugInterface $slug = null, bool $recursive = false): bool
    {
        $result = 0;
        foreach ($sites as $site)
            $result |= $adapter->clearCache($site, $slug, $recursive);

        return (bool)$result;
    }
}