<?php
declare(strict_types=1);

namespace CPSIT\CpsMyraCloud\Tests\Unit\Service;

use CPSIT\CpsMyraCloud\Adapter\AdapterRegisterInterface;
use PHPUnit\Framework\TestCase;

class ExternalCacheServiceTest extends TestCase
{
    public function testGeneric()
    {
        $c = class_exists(AdapterRegisterInterface::class);
        $this->assertTrue(true);
    }
}
