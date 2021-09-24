<?php
declare(strict_types=1);

namespace Fr\MyraCloud\Hook;

use Fr\MyraCloud\Domain\DTO\CacheConfig;
use Fr\MyraCloud\Service\MyraCacheService;
use TYPO3\CMS\Core\DataHandling\DataHandler;

class Typo3CacheManager
{
    private MyraCacheService $cacheService;

    /**
     * @param MyraCacheService $cacheService
     */
    public function __construct(MyraCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * @param array $config
     * @param DataHandler $dataHandler
     */
    public function clearClear(array $config, DataHandler $dataHandler)
    {
        $cacheConfig = new CacheConfig($config);
        if ($cacheConfig->isValid()) {
            $this->cacheService->clearCacheWithConfig($cacheConfig);
        }
    }
}