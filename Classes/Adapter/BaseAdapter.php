<?php
declare(strict_types=1);

namespace CPSIT\CpsMyraCloud\Adapter;

use BR\Toolkit\Typo3\Cache\CacheService;
use CPSIT\CpsMyraCloud\Traits\DomainListParserTrait;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\ServerRequestFactory;
use TYPO3\CMS\Core\SingletonInterface;

abstract class BaseAdapter implements SingletonInterface, AdapterInterface
{
    use DomainListParserTrait;

    private ExtensionConfiguration $extensionConfiguration;
    protected CacheService $cacheService;
    private static array $configCache = [];
    private static array $checkupCache = [];

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
        return 'TYPO3/CMS/CpsMyraCloud/ContextMenuActions';
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
        return !(
            !$this->setupConfigCondition() ||
            !$this->liveOnlyCondition() ||
            !$this->domainBlacklist()
        );
    }

    /**
     * @return bool
     */
    public function canInteract(): bool
    {
        return !(
            !$this->adminOnlyCondition() ||
            !$this->canExecute()
        );
    }

    /**
     * @return bool
     */
    private function adminOnlyCondition(): bool
    {
        if (isset(self::$checkupCache[__METHOD__])) {
            return self::$checkupCache[__METHOD__];
        }

        /** @var BackendUserAuthentication $backendUser */
        $backendUser = $GLOBALS['BE_USER']??null;
        if (!$backendUser)
            return self::$checkupCache[__METHOD__] = false;

        $allConfigData = $this->getAdapterConfig(true);
        $only = ($allConfigData['onlyAdmin'] ?? '1') === '1';
        if ($only)
            return self::$checkupCache[__METHOD__] = $backendUser->isAdmin();

        return self::$checkupCache[__METHOD__] = true;
    }

    /**
     * @return bool
     */
    private function liveOnlyCondition(): bool
    {
        if (isset(self::$checkupCache[__METHOD__])) {
            return self::$checkupCache[__METHOD__];
        }

        $allConfigData = $this->getAdapterConfig(true);
        $only = ($allConfigData['onlyLive'] ?? '1') === '1';
        if ($only)
            return self::$checkupCache[__METHOD__] = Environment::getContext()->isProduction();

        return self::$checkupCache[__METHOD__] = true;
    }

    /**
     * @return bool
     */
    private function domainBlacklist(): bool
    {
        if (isset(self::$checkupCache[__METHOD__])) {
            return self::$checkupCache[__METHOD__];
        }

        $blacklistString = $this->getAdapterConfig(true)['domainBlacklist'] ?? '';
        $blackList = $this->parseCommaList($blacklistString);
        $request = $GLOBALS['TYPO3_REQUEST'] ?? ServerRequestFactory::fromGlobals();
        $currentDomainContext = $request->getUri()->getHost();
        return self::$checkupCache[__METHOD__] = (empty($blackList) || !in_array($currentDomainContext, $blackList));
    }

    /**
     * @return bool
     */
    private function setupConfigCondition(): bool
    {
        if (isset(self::$checkupCache[__METHOD__])) {
            return self::$checkupCache[__METHOD__];
        }

        $allConfigData = $this->getAdapterConfig(true);
        foreach ($allConfigData as $key => $value) {
            if (strpos($key, $this->getAdapterConfigPrefix()) === 0) {
                if (empty($this->getRealAdapterConfigValue($value))) {
                    return self::$checkupCache[__METHOD__] = false;
                }
            }
        }

        return self::$checkupCache[__METHOD__] = true;
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
            $data = $this->extensionConfiguration->get('cps_myra_cloud');
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