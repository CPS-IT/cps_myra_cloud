<?php
declare(strict_types=1);

namespace Fr\MyraCloud\Domain\DTO\Provider;

use Fr\MyraCloud\Adapter\AdapterInterface;
use Fr\MyraCloud\Adapter\AdapterRegisterInterface;

interface ProviderItemRegisterInterface extends AdapterRegisterInterface
{
    public function getAdapter(): AdapterInterface;

    public function getRequireJsCall(int $id, string $table = 'pages'): string;

    public function getTypo3CssClass(): string;

    public function canExecute(): bool;
}