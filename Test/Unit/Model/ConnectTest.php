<?php

namespace MultiSafepay\Connect\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use MultiSafepay\Connect\Model\Connect;
use PHPUnit\Framework\TestCase;

class ConnectTest extends TestCase
{

    /**
     * @var Connect
     */
    private $connect;

    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->connect = $this->objectManager->getObject(
            'MultiSafepay\Connect\Model\Connect'
        );
    }
    
    public function testConnectInstance()
    {
        $this->assertInstanceOf(Connect::class, $this->connect);
    }

    /**
     * @dataProvider addressProvider
     */
    public function testparseAddress(
        $address1,
        $address2,
        $expected_street,
        $expected_apartment
    ) {
        $result = $this->connect->parseAddress($address1, $address2);
        $this->assertEquals($expected_street, $result[0]);
        $this->assertEquals($expected_apartment, $result[1]);
    }

    public function addressProvider()
    {
        return [
            [
                'address1'  => "Kraanspoor",
                'address2'  => "39",
                'street'    => "Kraanspoor",
                'apartment' => "39",
            ],
            [
                'address1'  => "Kraanspoor ",
                'address2'  => "39",
                'street'    => "Kraanspoor",
                'apartment' => "39",
            ],
            [
                'address1'  => "Kraanspoor 39",
                'address2'  => "",
                'street'    => "Kraanspoor",
                'apartment' => "39",
            ],
            [
                'address1'  => "Kraanspoor 39 ",
                'address2'  => "",
                'street'    => "Kraanspoor",
                'apartment' => "39",
            ],
            [
                'address1'  => "Kraanspoor",
                'address2'  => "39 ",
                'street'    => "Kraanspoor",
                'apartment' => "39",
            ],
            [
                'address1'  => "Kraanspoor39",
                'address2'  => "",
                'street'    => "Kraanspoor",
                'apartment' => "39",
            ],
            [
                'address1'  => "Kraanspoor39c",
                'address2'  => "",
                'street'    => "Kraanspoor",
                'apartment' => "39c",
            ],
            [
                'address1'  => "laan 1933 2",
                'address2'  => "",
                'street'    => "laan 1933",
                'apartment' => "2",
            ],
            [
                'address1'  => "laan 1933",
                'address2'  => "2",
                'street'    => "laan 1933",
                'apartment' => "2",
            ],
            [
                'address1'  => "18 septemberplein 12",
                'address2'  => "",
                'street'    => "18 septemberplein",
                'apartment' => "12",
            ],
            [
                'address1'  => "18 septemberplein",
                'address2'  => "12",
                'street'    => "18 septemberplein",
                'apartment' => "12",
            ],
            [
                'address1'  => "kerkstraat 42-f3",
                'address2'  => "",
                'street'    => "kerkstraat",
                'apartment' => "42-f3",
            ],
            [
                'address1'  => "kerkstraat",
                'address2'  => "42-f3",
                'street'    => "kerkstraat",
                'apartment' => "42-f3",
            ],
            [
                'address1'  => "Kerk straat 2b",
                'address2'  => "",
                'street'    => "Kerk straat",
                'apartment' => "2b",
            ],
            [
                'address1'  => "Kerk straat",
                'address2'  => "2b",
                'street'    => "Kerk straat",
                'apartment' => "2b",
            ],
            [
                'address1'  => "1e constantijn huigensstraat 1b",
                'address2'  => "",
                'street'    => "1e constantijn huigensstraat",
                'apartment' => "1b",
            ],
            [
                'address1'  => "1e constantijn huigensstraat",
                'address2'  => "1b",
                'street'    => "1e constantijn huigensstraat",
                'apartment' => "1b",
            ],
            [
                'address1'  => "Heuvel, 2a",
                'address2'  => "",
                'street'    => "Heuvel,",
                'apartment' => "2a",
            ],
            [
                'address1'  => "1e Jan  van  Kraanspoor",
                'address2'  => "2",
                'street'    => "1e Jan van Kraanspoor",
                'apartment' => "2",
            ],
            [
                'address1'  => "Neherkade 1 XI",
                'address2'  => "",
                'street'    => "Neherkade",
                'apartment' => "1 XI",
            ]
        ];
    }
}
