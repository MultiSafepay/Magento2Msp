<?php

namespace MultiSafepay\Connect\Helper;

class AddressHelper
{
    /**
     * @param $address
     * @return bool|string
     */
    public function serializeAddress($address)
    {
        return serialize([
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
