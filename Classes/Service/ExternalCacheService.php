<?php
declare(strict_types=1);

namespace Fr\MyraCloud\Service;

use Fr\MyraCloud\Adapter\MyraApiAdapter;
use Fr\MyraCloud\Domain\DTO\PageConfig;
use Fr\MyraCloud\Domain\DTO\InstanceConfig;
use Fr\MyraCloud\Domain\DTO\MyraDomainList;
use Fr\MyraCloud\Domain\Enum\CacheConfigCommandMapping;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;

class ExternalCacheService
{
    private MyraApiAdapter $myraApiAdapter;
    private SiteFinder $siteFinder;

    /**
     * @param MyraApiAdapter $myraApiAdapter
     * @param SiteFinder $siteFinder
     */
    public function __construct(MyraApiAdapter $myraApiAdapter, SiteFinder $siteFinder)
    {
        $this->myraApiAdapter = $myraApiAdapter;
        $this->siteFinder = $siteFinder;
    }

    /**
     * @param PageConfig $cacheConfig
     * @param InstanceConfig $instanceConfig
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function clearCacheWithConfig(PageConfig $cacheConfig, InstanceConfig $instanceConfig): bool
    {
        $domainClearList = $this->myraApiAdapter->getDomains($instanceConfig);
        $domainClearList->setSites($this->getSitesToClear($cacheConfig));

        return $this->clearCacheWithDomainList($domainClearList, $cacheConfig, $instanceConfig);
    }

    /**
     * @param MyraDomainList $domainList
     * @param PageConfig $cacheConfig
     * @param InstanceConfig $instanceConfig
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function clearCacheWithDomainList(MyraDomainList $domainList, PageConfig $cacheConfig, InstanceConfig $instanceConfig): bool
    {
        foreach ($domainList->getMatchingDomains() as $domain) {
            $t = $this->myraApiAdapter->clearCache($domain, $cacheConfig, $instanceConfig);
        }

        return true;
    }

    /**
     * @param PageConfig $config
     * @return Site[]
     */
    private function getSitesToClear(PageConfig $config): array
    {
        $site = null;
        if ($config->getType() === CacheConfigCommandMapping::PAGE) {
            $site = $this->getSiteForPageUid($config->getPageId());
        }

        if ($config->getType() !== CacheConfigCommandMapping::PAGE || $site === null) {
            return $this->getAllSites();
        }

        return array_filter([$site]);
    }

    /**
     * @param int $pid
     * @return Site|null
     */
    private function getSiteForPageUid(int $pid): ?Site
    {
        $site = null;
        try {
            $site = $this->siteFinder->getSiteByPageId($pid);
        } catch (SiteNotFoundException $e) {
        }

        return $site;
    }

    /**
     * @return Site[]
     */
    private function getAllSites(): array
    {
        return $this->siteFinder->getAllSites(false);
    }
}