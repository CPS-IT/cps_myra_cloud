<?php
declare(strict_types=1);

namespace CPSIT\CpsMyraCloud\Domain\DTO\Provider;

use CPSIT\CpsMyraCloud\Adapter\AdapterInterface;
use CPSIT\CpsMyraCloud\Adapter\AdapterRegisterInterface;

interface ProviderItemRegisterInterface extends AdapterRegisterInterface
{
    public function getAdapter(): AdapterInterface;

    public function getRequireJsCall(int $id, string $table = 'pages'): string;

    public function getTypo3CssClass(): string;

    public function canExecute(): bool;
}