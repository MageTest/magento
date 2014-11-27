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
 * @spi
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Test\Handler\Curl;

use Magento\Catalog\Test\Fixture\ProductAttribute;
use Mtf\Fixture\FixtureInterface;
use Mtf\Handler\Curl;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;
use Mtf\System\Config;

/**
 * Class CreateProductAttribute
 */
class CreateProductAttribute extends Curl
{
    /**
     * Create attribute
     *
     * @param FixtureInterface|\Magento\Catalog\Test\Fixture\ProductAttribute $fixture [optional]
     * @return mixed|string
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $url = $_ENV['app_backend_url'] . 'catalog/product_attribute/save/back/edit/active_tab/main';
        $curl = new BackendDecorator(new CurlTransport(), new Config());
        $curl->write(CurlInterface::POST, $url, '1.0', array(), $this->getPostParams($fixture));
        $response = $curl->read();
        $curl->close();

        $id = null;
        if (preg_match('!catalog/product_attribute/save/attribute_id/(\d+)/active_tab/main/!', $response, $matches)) {
            $id = $matches[1];
        }

        $optionIds = array();
        if (preg_match_all(
            '!attributeOption\.add\({"checked":"(.?)*","intype":"radio","id":"(\d+)"!',
            $response,
            $matches
        )) {
            $optionIds = $matches[2];
        }

        return array('attributeId' => $id, 'optionIds' => $optionIds);
    }

    /**
     * Get data for curl POST params
     *
     * @param ProductAttribute $fixture
     * @return array
     */
    protected function getPostParams(ProductAttribute $fixture)
    {
        $data = $this->prepareParams($fixture->getData('fields'));
        $options = $fixture->getOptions();
        foreach ($options as $option) {
            $data = array_merge($data, $this->prepareParams($option));
        }
        return $data;
    }

    /**
     * Prepare data for curl POST params
     *
     * @param array $fields
     * @return array
     */
    protected function prepareParams(array $fields)
    {
        $data = array();
        foreach ($fields as $key => $field) {
            $value = $this->getParamValue($field);

            if (null === $value) {
                continue;
            }

            $_key = $this->getFieldKey($field);
            if (null === $_key) {
                $_key = $key;
            }
            $data[$_key] = $value;
        }
        return $data;
    }

    /**
     * Return key for request
     *
     * @param array $data
     * @return null|string
     */
    protected function getFieldKey(array $data)
    {
        return isset($data['input_name']) ? $data['input_name'] : null;
    }

    /**
     * Return value for request
     *
     * @param array $data
     * @return null|string
     */
    protected function getParamValue(array $data)
    {
        if (array_key_exists('input_value', $data)) {
            return $data['input_value'];
        }

        if (array_key_exists('value', $data)) {
            return $data['value'];
        }
        return null;
    }
}
