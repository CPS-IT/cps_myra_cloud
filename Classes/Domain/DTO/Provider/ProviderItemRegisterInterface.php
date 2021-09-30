<?php
declare(strict_types=1);

namespace CPSIT\CpsMyraCloud\Domain\DTO\Provider;

use CPSIT\CpsMyraCloud\Adapter\AdapterInterface;
use CPSIT\CpsMyraCloud\Adapter\AdapterRegisterInterface;
use CPSIT\CpsMyraCloud\Domain\Enum\Typo3CacheType;

interface ProviderItemRegisterInterface extends AdapterRegisterInterface
{
    public function getAdapter(): AdapterInterface;

    public function getRequireJsCall(string $id, int $type = Typo3CacheType::UNKNOWN): string;

    public function getTypo3CssClass(): string;

    public function canExecute(): bool;
}