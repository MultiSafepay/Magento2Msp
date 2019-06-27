<?php

namespace MultiSafepay\Connect\Test\Unit\Model;

use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

class CustomfieldsTest extends TestCase
{

    public function testToOptionArray()
    {
        $om = ObjectManager::getInstance();
        $instance = $om->get('MultiSafepay\Connect\Model\Config\Source\Customfields');

        $expected = [
            [
                'value' => 0,
                'label' => 'Disabled'
            ],
            [
                'value' => 1,
                'label' => 'Mandatory'
            ],
            [
                'value' => 2,
                'label' => 'Optional'
            ],
        ];

        $result = $instance->toOptionArray();
        $this->assertEquals($expected, $result);
    }
}
