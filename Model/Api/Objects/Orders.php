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

namespace MultiSafepay\Connect\Model\Api\Objects;

class Orders extends Core
{

    public $success;
    public $data;

    public function patch($body, $endpoint = '')
    {
        $result = parent::patch(json_encode($body), $endpoint);
        $this->success = $result->success;
        $this->data = $result->data;
        return $result;
    }

    public function get($type = 'orders', $id, $body = array(), $query_string = false)
    {
        $result = parent::get($type, $id, $body, $query_string);
        $this->success = $result->success;
        $this->data = $result->data;
        return $this->data;
    }

    public function post($body, $endpoint = 'orders')
    {
        $result = parent::post(json_encode($body), $endpoint);

        if (!empty($result->error_code)) {
            $this->success = null;
            $this->data = (object) array('payment_url' => null);
        } else {
            $this->success = $result->success;
            $this->data = $result->data;
        }
        return $this->data;
    }

    public function getPaymentLink()
    {
        return $this->data->payment_url;
    }

}
