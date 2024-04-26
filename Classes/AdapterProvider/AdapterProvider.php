<?php
declare(strict_types=1);

namespace CPSIT\CpsMyraCloud\AdapterProvider;

use CPSIT\CpsMyraCloud\Adapter\AdapterInterface;
use CPSIT\CpsMyraCloud\Domain\DTO\Provider\ProviderItem;
use CPSIT\CpsMyraCloud\Domain\DTO\Provider\ProviderItemRegisterInterface;

final class AdapterProvider
{
    private static array $providerItemCache = [];

    /**
     * @param AdapterInterface[] $adapters
     */
    public function __construct(
        private readonly iterable $adapters
    ) {}

    public function getAllProviderItems(): iterable
    {
        return iterator_to_array($this->adapters);
    }

    /**
     * @return ProviderItemRegisterInterface|null
     */
    public function getDefaultProviderItem(): ?ProviderItemRegisterInterface
    {
        return $this->getProviderItem($this->getAllProviderItems()[0]??null);
    }

    /**
     * @param AdapterInterface|null $adapter
     * @return ProviderItemRegisterInterface|null
     */
    public function getProviderItem(?AdapterInterface $adapter): ?ProviderItemRegisterInterface
    {
        return $adapter ? self::$providerItemCache[$adapter::class] ??= new ProviderItem($adapter) : null;
    }
}
