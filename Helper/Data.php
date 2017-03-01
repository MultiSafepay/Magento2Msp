<?php

/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is provided with Magento in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the MultiSafepay plugin
 * to newer versions in the future. If you wish to customize the plugin for your
 * needs please document your changes and make backups before your update.
 *
 * @category    MultiSafepay
 * @package     Connect
 * @author      Ruud Jonk <techsupport@multisafepay.com>
 * @copyright   Copyright (c) 2015 MultiSafepay, Inc. (http://www.multisafepay.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, 
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR 
 * PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT 
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN 
 * ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION 
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace MultiSafepay\Connect\Helper;

class Data
{

    public $giftcards = array(
        'webshopgiftcard',
        'babygiftcard',
        'boekenbon',
        'erotiekbon',
        'parfumcadeaukaart',
        'yourgift',
        'wijncadeau',
        'gezondheidsbon',
        'fashioncheque',
        'fashiongiftcard',
        'podium',
        'vvvbon',
        'sportenfit',
        'goodcard',
        'nationaletuinbon',
        'nationaleverwencadeaubon',
        'beautyandwellness',
        'fietsenbon',
        'wellnessgiftcard',
        'winkelcheque',
        'givacard'
    );
    public $gateways = array(
        'ideal',
        'dotpay',
        'betaalnaontvangst',
        'einvoice',
        'klarnainvoice',
        'bancontact',
        'visa',
        'eps',
        'ferbuy',
        'mastercard',
        'mspbanktransfer',
        'maestro',
        'paypalmsp',
        'giropay',
        'sofort',
        'directdebit',
        'americanexpress',
        'creditcard',
        'paysafecard'
    );

        /**
     * Create Order On Your Store
     *
     * @param array $orderData
     * @return int $orderId
     *
     */
    public function createOrder($orderData) {

        //init the store id and website id @todo pass from array
        $store = $this->_storeManager->getStore();
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        //init the customer
        $customer=$this->customerFactory->create();
        $customer->setWebsiteId($websiteId);
        $customer->loadByEmail($orderData['email']);// load customet by email address
        //check the customer
        if(!$customer->getEntityId()){
            //If not avilable then create this customer
            $customer->setWebsiteId($websiteId)
                ->setStore($store)
                ->setFirstname($orderData['shipping_address']['firstname'])
                ->setLastname($orderData['shipping_address']['lastname'])
                ->setEmail($orderData['email'])
                ->setPassword($orderData['email']);
            $customer->save();
        }
        //init the quote
        $cart_id = $this->cartManagementInterface->createEmptyCart();
        $cart = $this->cartRepositoryInterface->get($cart_id);
        $cart->setStore($store);
        // if you have already buyer id then you can load customer directly
        $customer= $this->customerRepository->getById($customer->getEntityId());
        $cart->setCurrency();
        $cart->assignCustomer($customer); //Assign quote to customer
        //add items in quote
        foreach($orderData['items'] as $item){
            $product = $this->_productFactory->create()->load($item['product_id']);
            $cart->addProduct(
                $product,
                intval($item['qty'])
            );
        }
        //Set Address to quote @todo add section in order data for seperate billing and handle it
        $cart->getBillingAddress()->addData($orderData['shipping_address']);
        $cart->getShippingAddress()->addData($orderData['shipping_address']);
        // Collect Rates and Set Shipping & Payment Method
        $this->shippingRate
            ->setCode('freeshipping_freeshipping')
            ->getPrice(1);
        $shippingAddress = $cart->getShippingAddress();
        //@todo set in order data
        $shippingAddress->setCollectShippingRates(true)
            ->collectShippingRates()
            ->setShippingMethod('flatrate_flatrate'); //shipping method
        $cart->getShippingAddress()->addShippingRate($this->shippingRate);
        $cart->setPaymentMethod('checkmo'); //payment method
        //@todo insert a variable to affect the invetory
        $cart->setInventoryProcessed(false);
        // Set sales order payment
        $cart->getPayment()->importData(['method' => 'checkmo']);
        // Collect total and saeve
        $cart->collectTotals();
        // Submit the quote and create the order
        $cart->save();
        $cart = $this->cartRepositoryInterface->get($cart->getId());
        $order_id = $this->cartManagementInterface->placeOrder($cart->getId());
        return $order_id;
    }
    public function getPaymentType($code)
    {
        if (in_array($code, $this->gateways)) {
            return 'gateways';
        } elseif (in_array($code, $this->giftcards)) {
            return 'giftcards';
        }
    }

}
