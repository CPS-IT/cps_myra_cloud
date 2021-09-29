<?php
declare(strict_types=1);

namespace Fr\MyraCloud\Provider;


use BR\Toolkit\Typo3\VersionWrapper\InstanceUtility;
use Fr\MyraCloud\Adapter\AdapterInterface;
use Fr\MyraCloud\Adapter\MyraApiAdapter;
use Fr\MyraCloud\Domain\DTO\Adapter\AdapterRegisterInterface;
use Fr\MyraCloud\Domain\DTO\Provider\ProviderItem;
use Fr\MyraCloud\Domain\DTO\Provider\ProviderItemRegisterInterface;
use TYPO3\CMS\Extbase\Object\Exception;

abstract class ExternalCacheProvider
{
    private static string $defaultAdapter = MyraApiAdapter::class;

    private static array $adapterRegister = [
        MyraApiAdapter::class
    ];

    /**
     * @var ProviderItemRegisterInterface[]
     */
    private static array $providerItemCache = [];

    /**
     * @return ProviderItemRegisterInterface|null
     */
    public static function getDefaultProviderItem(): ?ProviderItemRegisterInterface
    {
        return static::getProviderItem(self::$defaultAdapter);
    }

    /**
     * @param string $adapterClass
     * @return ProviderItemRegisterInterface|null
     */
    public static function getProviderItem(string $adapterClass): ?ProviderItemRegisterInterface
    {
        if (isset(self::$providerItemCache[$adapterClass])) {
            return self::$providerItemCache[$adapterClass];
        }

        $adapter = self::getAdapterClass($adapterClass);
        if ($adapter) {
            return self::$providerItemCache[$adapterClass] = new ProviderItem($adapter);
        }

        return null;
    }

    /**
     * @param string $adapterClass
     * @return AdapterInterface|null
     */
    private static function getAdapterClass(string $adapterClass): ?AdapterInterface
    {
        $adapter = null;
        if (class_exists($adapterClass)) {
            try {
                /** @var null|AdapterInterface $adapter */
                if(!(($adapter = InstanceUtility::get($adapterClass)) instanceof AdapterInterface)) {
                    $adapter = null;
                }
            } catch (Exception $e) {
                return null;
            }
        }

        return $adapter;
    }
}