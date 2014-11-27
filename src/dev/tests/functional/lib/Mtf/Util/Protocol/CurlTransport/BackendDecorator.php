<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Mtf\Util\Protocol\CurlTransport;

use Mtf\Util\Protocol\CurlTransport;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\System\Config;

/**
 * Class BackendDecorator
 * Curl transport on backend
 */
class BackendDecorator implements CurlInterface
{
    /**
     * Curl transport protocol
     *
     * @var CurlTransport
     */
    protected $transport;

    /**
     * Form key
     *
     * @var string
     */
    protected $formKey = null;

    /**
     * Response data
     *
     * @var string
     */
    protected $response;

    /**
     * System config
     *
     * @var Config
     */
    protected $configuration;

    /**
     * Constructor
     *
     * @param CurlTransport $transport
     * @param Config $configuration
     */
    public function __construct(CurlTransport $transport, Config $configuration)
    {
        $this->transport = $transport;
        $this->configuration = $configuration;
        $this->authorize();
    }

    /**
     * Authorize customer on backend
     *
     * @throws \Exception
     * @return void
     */
    protected function authorize()
    {
        $credentials = $this->configuration->getConfigParam('application/backend_user_credentials');
        $url = $_ENV['app_backend_url'] . $this->configuration->getConfigParam('application/backend_login_url');
        $data = [
            'login[username]' => $credentials['login'],
            'login[password]' => $credentials['password']
        ];
        $this->transport->write(CurlInterface::POST, $url, '1.0', [], $data);
        $response = $this->read();
        if (strpos($response, 'page-login')) {
            throw new \Exception('Admin user cannot be logged in by curl handler!');
        }
    }

    /**
     * Init Form Key from response
     *
     * @return void
     */
    protected function initFormKey()
    {
        preg_match('!var FORM_KEY = \'(\w+)\';!', $this->response, $matches);
        if (!empty($matches[1])) {
            $this->formKey = $matches[1];
        }
    }

    /**
     * Send request to the remote server
     *
     * @param string $method
     * @param string $url
     * @param string $httpVer
     * @param array $headers
     * @param array $params
     * @return void
     * @throws \Exception
     */
    public function write($method, $url, $httpVer = '1.1', $headers = [], $params = [])
    {
        if ($this->formKey) {
            $params['form_key'] = $this->formKey;
        } else {
            throw new \Exception('Form key is absent! Response: ' . $this->response);
        }
        $this->transport->write($method, $url, $httpVer, $headers, http_build_query($params));
    }

    /**
     * Read response from server
     *
     * @return string
     */
    public function read()
    {
        $this->response = $this->transport->read();
        $this->initFormKey();
        return $this->response;
    }

    /**
     * Add additional option to cURL
     *
     * @param  int $option the CURLOPT_* constants
     * @param  mixed $value
     * @return void
     */
    public function addOption($option, $value)
    {
        $this->transport->addOption($option, $value);
    }

    /**
     * Close the connection to the server
     *
     * @return void
     */
    public function close()
    {
        $this->transport->close();
    }
}
