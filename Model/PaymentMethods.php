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
 * @author      MultiSafepay <integration@multisafepay.com>
 * @copyright   Copyright (c) MultiSafepay, Inc. (https://www.multisafepay.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
 * PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN
 * ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace MultiSafepay\Connect\Model;

use Magento\Payment\Model\MethodList;
use Magento\Quote\Api\CartRepositoryInterface;
use MultiSafepay\Connect\Api\PaymentMethodsInterface;
use MultiSafepay\Connect\Helper\Data;

class PaymentMethods implements PaymentMethodsInterface
{

    /**
     * @var Data
     */
    protected $mspHelper;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var MethodList
     */
    protected $methodList;

    /**
     * PaymentUrl constructor.
     * @param Data $data
     * @param CartRepositoryInterface $quoteRepository
     * @param MethodList $methodList
     */
    public function __construct(
        Data $data,
        CartRepositoryInterface $quoteRepository,
        MethodList $methodList
    ) {
        $this->mspHelper = $data;
        $this->quoteRepository = $quoteRepository;
        $this->methodList = $methodList;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($cartId)
    {
        $quote = $this->quoteRepository->get($cartId);
        $methods = $this->methodList->getAvailableMethods($quote);
        foreach ($methods as $key => $value) {
            // Add iDEAL issuers
            if ($value->getCode() === 'ideal' && $value->getTitle() === 'iDEAL') {
                $ideal = [
                    'code' => 'ideal',
                    'title' => 'iDEAL',
                    'issuers' => $this->mspHelper->getIssuers()
                ];
                $methods[$key] = $ideal;
            }
        }
        return $methods;
    }
}
