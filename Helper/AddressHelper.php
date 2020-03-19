<?php

namespace MultiSafepay\Connect\Helper;

use Magento\Framework\Serialize\SerializerInterface;

class AddressHelper
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(
        SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
    }

    /**
     * @param $address
     * @return bool|string
     */
    public function serializeAddress($address)
    {
        return $this->serializer->serialize([
            'street' => $address->getStreet(),
            'city' => $address->getCity(),
            'postcode' => $address->getPostcode()
        ]);
    }

    /**
     * @param $shippingAddress
     * @param $billingAddress
     * @return bool
     */
    public function isSameAddress($shippingAddress, $billingAddress): bool
    {
        return $this->serializeAddress($shippingAddress) === $this->serializeAddress($billingAddress);
    }
}
