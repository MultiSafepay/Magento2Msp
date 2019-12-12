<?php

namespace MultiSafepay\Connect\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use MultiSafepay\Connect\Model\GatewayRestrictions;
use PHPUnit\Framework\TestCase;

class GatewayRestrictionsTest extends TestCase
{
    private $restrictions;

    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->restrictions = $this->objectManager->getObject(GatewayRestrictions::class);
    }

    public function testIsGroupAllowedShouldReturnTrueIfGroupInConfigData()
    {
        $result = $this->restrictions->isGroupAllowed('1', '1,2,3');
        $this->assertTrue($result);
    }

    public function testIsGroupAllowedShouldReturnFalseIfGroupNotInConfigData()
    {
        $result = $this->restrictions->isGroupAllowed('1', '0');
        $this->assertFalse($result);
    }

    public function testIsGroupAllowedShouldReturnTrueIfConfigDataIsEmptyString()
    {
        $result = $this->restrictions->isGroupAllowed('1', '');
        $this->assertTrue($result);
    }

    public function testIsGroupAllowedShouldReturnTrueIfConfigDataIsNull()
    {
        $result = $this->restrictions->isGroupAllowed('1', null);
        $this->assertTrue($result);
    }
}
