<?php
declare(strict_types=1);

namespace Fr\MyraCloud\Domain\DTO\Provider;

use Fr\MyraCloud\Domain\DTO\Adapter\AdapterRegisterInterface;

interface ProviderItemRegisterInterface extends AdapterRegisterInterface
{
    public function getAdapter(): AdapterRegisterInterface;

    public function getRequireJsCall(int $id, string $table = 'pages'): string;

    public function getTypo3CssClass(): string;
}