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
namespace Magento\Downloadable\Test\Handler\Curl;

use Mtf\Fixture\FixtureInterface;
use Mtf\Handler\Curl;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;
use Mtf\System\Config;

class CreateDownloadable extends Curl
{
    /**
     * Prepare POST data for downloadable product creation request
     *
     * @param array $params
     * @param string|null $prefix
     * @return array
     */
    protected function prepareData($params, $prefix = null)
    {
        $data = array();
        foreach ($params as $key => $values) {
            if ($key === 'downloadable') {
                $data += $this->prepareDownloadableData($key, $values);
            } else {
                $value = $this->getValue($values);
                //do not add this data if value does not exist
                if (null === $value) {
                    continue;
                }
                if (isset($values['input_name'])) {
                    $data[$values['input_name']] = $value;
                } elseif ($prefix) {
                    $data[$prefix][$key] = $value;
                } else {
                    $data[$key] = $value;
                }
            }
        }
        return $data;
    }

    /**
     * Retrieve field value or return null if value does not exist
     *
     * @param array $values
     * @return null|mixed
     */
    protected function getValue($values)
    {
        return isset($values['value'])
            ? (isset($values['input_value']) ? $values['input_value'] : $values['value'])
            : null;
    }

    /**
     * Prepare downloadable data
     *
     * @param string $key
     * @param string|array $value
     * @return array
     */
    protected function prepareDownloadableData($key, $value)
    {
        if (strpos($key, '][')) {
            list($key1, $key2) = explode('][', $key);
            return [$key1 => $this->prepareDownloadableData($key2, $value)];
        }

        if (!is_array($value)) {
            return [$key => $value];
        }

        if (isset($value['value'])) {
            return [$key => $value['value']];
        }

        $data = [];
        foreach ($value as $subKey => $subValue) {
            $data = array_replace_recursive($data, $this->prepareDownloadableData($subKey, $subValue));
        }
        return [$key => $data];
    }

    /**
     * Retrieve URL for request with all necessary parameters
     *
     * @param array $config
     * @return string
     */
    protected function getUrl(array $config)
    {
        $requestParams = isset($config['create_url_params']) ? $config['create_url_params'] : array();
        $params = '';
        foreach ($requestParams as $key => $value) {
            $params .= $key . '/' . $value . '/';
        }
        return $_ENV['app_backend_url'] . 'catalog/product/save/' . $params . 'popup/1/back/edit';
    }

    /**
     * POST request for creating downloadable product
     *
     * @param FixtureInterface $fixture [optional]
     * @return int id of created product
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $config = $fixture->getDataConfig();

        $curl = new BackendDecorator(new CurlTransport(), new Config);
        $curl->addOption(CURLOPT_HEADER, 1);
        $curl->write(
            CurlInterface::POST,
            $this->getUrl($config),
            '1.0',
            [],
            $this->prepareData(
                $fixture->getData('fields'),
                isset($config['input_prefix']) ? $config['input_prefix'] : null
            )
        );
        $response = $curl->read();
        $curl->close();

        preg_match("~Location: [^\s]*\/id\/(\d+)~", $response, $matches);
        return $matches[1];
    }
}
