<?php
declare(strict_types=1);

namespace Fr\MyraCloud\Adapter;

use BR\Toolkit\Typo3\Cache\CacheService;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\SingletonInterface;

abstract class BaseAdapter implements SingletonInterface, AdapterInterface
{
    private ExtensionConfiguration $extensionConfiguration;
    protected CacheService $cacheService;
    private static array $configCache = [];

    /**
     * @param ExtensionConfiguration $extensionConfiguration
     * @param CacheService $cacheService
     */
    public function __construct(ExtensionConfiguration $extensionConfiguration, CacheService $cacheService)
    {
        $this->extensionConfiguration = $extensionConfiguration;
        $this->cacheService = $cacheService;
    }

    public function getRequireJsNamespace(): string
    {
        return 'TYPO3/CMS/FrMyraCloud/ContextMenuActions';
    }

    public function getRequireJsFunction(): string
    {
        return 'ClearExternalCache';
    }

    abstract public function getCacheId(): string;

    abstract public function getCacheIconIdentifier(): string;

    abstract public function getCacheTitle(): string;

    abstract public function getCacheDescription(): string;

    abstract protected function getAdapterConfigPrefix(): string;

    /**
     * @return bool
     */
    public function canExecute(): bool
    {
        $allConfigData = $this->getAdapterConfig(true);
        $onlyLive = ($allConfigData['onlyLive']??'0') === '1';
        if ($onlyLive && !Environment::getContext()->isProduction()) {
            return false;
        }

        foreach ($allConfigData as $key => $value) {
            if (strpos($key, $this->getAdapterConfigPrefix()) === 0) {
                if (empty($this->getRealAdapterConfigValue($value))) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @param bool $ignorePrefix
     * @return array
     */
    protected function getAdapterConfig(bool $ignorePrefix = false): array
    {
        $prefix = $this->getAdapterConfigPrefix();
        if (!empty(self::$configCache)) {
            if ($ignorePrefix) {
                return self::$configCache['all'];
            } elseif (!empty(self::$configCache[$prefix])) {
                return self::$configCache[$prefix];
            }
        }

        $data = [];
        try {
            $data = $this->extensionConfiguration->get('fr_myra_cloud');
        } catch (\Exception $e) {}

        foreach ($data as $key => $value) {
            $value = $this->getRealAdapterConfigValue($value);
            self::$configCache['all'][$key] = $value;
            if (strpos($key, $prefix) === 0) {
                self::$configCache[$prefix][$key] = $value;
            }
        }

        if ($ignorePrefix) {
            return self::$configCache['all'];
        }

        return self::$configCache[$prefix];
    }

    /**
     * @param string $value
     * @return string
     */
    private function getRealAdapterConfigValue(string $value): string
    {
        if (stripos($value, 'ENV=') === 0) {
            return (string)getenv(substr($value, 4));
        }

        return $value;
    }
}