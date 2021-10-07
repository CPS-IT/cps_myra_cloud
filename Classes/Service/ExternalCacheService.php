<?php
declare(strict_types=1);

namespace CPSIT\CpsMyraCloud\Service;


use CPSIT\CpsMyraCloud\Adapter\AdapterInterface;
use CPSIT\CpsMyraCloud\AdapterProvider\ExternalCacheProvider;
use CPSIT\CpsMyraCloud\Domain\DTO\Provider\ProviderItemRegisterInterface;
use CPSIT\CpsMyraCloud\Domain\DTO\Typo3\File\CustomFile;
use CPSIT\CpsMyraCloud\Domain\DTO\Typo3\File\FileAdmin;
use CPSIT\CpsMyraCloud\Domain\DTO\Typo3\File\FileInterface;
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
        $providerItem = ExternalCacheProvider::getDefaultProviderItem();
        if ($providerItem === null) {
            return false;
        }

        if ($type === Typo3CacheType::PAGE) {
            return $this->clearPage($providerItem, (int)$identifier);
        } elseif ($type === Typo3CacheType::RESOURCE) {
            return $this->clearFile($providerItem, trim($identifier));
        } elseif ($type === Typo3CacheType::ALL_PAGE) {
            return $this->clearAllPages($providerItem);
        } elseif ($type === Typo3CacheType::ALL_RESOURCES) {
            return $this->clearAllFiles($providerItem);
        }

        return false;
    }

    /**
     * @param ProviderItemRegisterInterface $provider
     * @param int $pageUid
     * @return bool
     */
    private function clearPage(ProviderItemRegisterInterface $provider, int $pageUid): bool
    {
        $page = $this->pageService->getPage($pageUid);
        $sites = $this->siteService->getSitesForClearance($page);
        return $this->clearCacheWithAdapter($provider->getAdapter(), $sites, $page);
    }

    /**
     * @param ProviderItemRegisterInterface $provider
     * @return bool
     */
    private function clearAllPages(ProviderItemRegisterInterface $provider): bool
    {
        $sites = $this->siteService->getSitesForClearance(null);
        return $this->clearCacheWithAdapter($provider->getAdapter(), $sites, null, true);
    }

    /**
     * @param ProviderItemRegisterInterface $provider
     * @return bool
     */
    private function clearAllFiles(ProviderItemRegisterInterface $provider): bool
    {
        $sites = $this->siteService->getSitesForClearance(null);
        $fileCaches = [new FileAdmin(), new Typo3Temp(), new Typo3Conf(), new Typo3Core()];
        $result = 0;
        foreach ($fileCaches as $file) {
            $result |= $this->clearCacheWithAdapter($provider->getAdapter(), $sites, $file, true);
        }

        return (bool)$result;
    }

    /**
     * @param ProviderItemRegisterInterface $provider
     * @param string $relPath
     * @return bool
     */
    private function clearFile(ProviderItemRegisterInterface $provider, string $relPath): bool
    {
        $file = $this->getFile($relPath);
        $sites = $this->siteService->getSitesForClearance(null);
        // files are always recursive deleted
        return $this->clearCacheWithAdapter($provider->getAdapter(), $sites, $file, true);
    }

    /**
     * @param string $identifier
     * @return FileInterface
     */
    private function getFile(string $identifier): FileInterface
    {
        if (strpos($identifier, '1:/') === 0) {
            return new FileAdmin(substr($identifier, 3));
        }
        // TODO: add other storages here
        return new CustomFile($identifier);
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