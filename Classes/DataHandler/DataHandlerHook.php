<?php
declare(strict_types=1);

namespace CPSIT\CpsMyraCloud\DataHandler;

use CPSIT\CpsMyraCloud\AdapterProvider\ExternalCacheProvider;
use CPSIT\CpsMyraCloud\Domain\Enum\Typo3CacheType;
use CPSIT\CpsMyraCloud\Service\ExternalCacheService;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\SingletonInterface;

class DataHandlerHook implements SingletonInterface
{
    private ExternalCacheService $externalCacheService;
    private array $pageAlreadyCleared = [];

    /**
     * @param ExternalCacheService $externalCacheService
     */
    public function __construct(ExternalCacheService $externalCacheService)
    {
        $this->externalCacheService = $externalCacheService;
    }

    /**
     * @param array $data
     * @param DataHandler $dataHandler
     */
    public function clearCachePostProc(array $data, DataHandler $dataHandler): void
    {
        if (isset($data['uid']) && isset($data['table']) && isset($data['uid_page'])) {
            $pid = (int)$data['uid_page'];
            $provider = ExternalCacheProvider::getDefaultProviderItem();
            if ($provider && !($this->pageAlreadyCleared[$pid]??false) && $provider->canAutomated()) {
                try {
                    $this->pageAlreadyCleared[$pid] = $this->externalCacheService->clear(Typo3CacheType::PAGE, (string)$pid);
                } catch (\Exception $_) {
                }
            }
        }
    }
}