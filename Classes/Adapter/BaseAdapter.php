<?php
declare(strict_types=1);

namespace Fr\MyraCloud\Adapter;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\SingletonInterface;

abstract class BaseAdapter implements SingletonInterface, AdapterInterface
{
    private ExtensionConfiguration $extensionConfiguration;

    /**
     * @param ExtensionConfiguration $extensionConfiguration
     */
    public function __construct(ExtensionConfiguration $extensionConfiguration)
    {
        $this->extensionConfiguration = $extensionConfiguration;
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
        $onlyLive = ($this->getAdapterConfig(true)['onlyLive']??'0') === '1';
        return !($onlyLive && !Environment::getContext()->isProduction());
    }

    /**
     * @param bool $ignorePrefix
     * @return array
     */
    protected function getAdapterConfig(bool $ignorePrefix = false): array
    {
        $data = [];
        try {
            $data = $this->extensionConfiguration->get('fr_myra_cloud');
        } catch (\Exception $e) {}

        $filterData = [];
        foreach ($data as $key => $value) {
            if ($ignorePrefix || strpos($key, $this->getAdapterConfigPrefix()) === 0) {
                $filterData[$key] = $this->getRealAdapterConfigValue($value);
            }
        }

        return $filterData;
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