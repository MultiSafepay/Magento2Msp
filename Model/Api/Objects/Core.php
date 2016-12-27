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

class Core
{

    protected $mspapi;
    public $result;

    public function __construct(\MultiSafepay\Connect\Model\Api\MspClient $mspapi)
    {
        $this->mspapi = $mspapi;
    }

    public function post($body, $endpoint = 'orders')
    {
        $this->result = $this->processRequest('POST', $endpoint, $body);
        return $this->result;
    }

    public function patch($body, $endpoint = '')
    {
        $this->result = $this->processRequest('PATCH', $endpoint, $body);
        return $this->result;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function get($endpoint, $id, $body = array(), $query_string = false)
    {
        if (!$query_string) {
            $url = "{$endpoint}/{$id}";
        } else {
            $url = "{$endpoint}?{$query_string}";
        }


        $this->result = $this->processRequest('GET', $url, $body);
        return $this->result;
    }

    protected function processRequest($http_method, $api_method, $http_body = NULL)
    {
        $body = $this->mspapi->processAPIRequest($http_method, $api_method, $http_body);
        if (!($object = @json_decode($body))) {
            throw new \Exception("'{$body}'.");
        }

        /* if (!empty($object->error_code)) {
          echo "{$object->error_code}: {$object->error_info}.";

          exit;
          } */
        return $object;
    }

}
