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

namespace Magento\Tax\Test\Handler\TaxRate;

use Mtf\Fixture\FixtureInterface;
use Mtf\Handler\Curl as AbstractCurl;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;
use Mtf\System\Config;

/**
 * Class Curl
 * Curl handler for creating Tax Rate
 */
class Curl extends AbstractCurl implements TaxRateInterface
{
    /**
     * Mapping for countries
     *
     * @var array
     */
    protected $countryId = [
        'AU' => 'Australia',
        'US' => 'United States',
        'GB' => 'United Kingdom',
    ];

    /**
     * Mapping for regions
     *
     * @var array
     */
    protected $regionId = [
        '0' => '*',
        '12' => 'California',
        '43' => 'New York',
        '57' => 'Texas',
    ];

    /**
     * Post request for creating tax rate
     *
     * @param FixtureInterface $fixture [optional]
     * @return mixed|string
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $data = $fixture->getData();
        $data['tax_country_id'] = array_search($data['tax_country_id'], $this->countryId);
        if (isset($data['tax_region_id'])) {
            $data['tax_region_id'] = array_search($data['tax_region_id'], $this->regionId);
        }

        $url = $_ENV['app_backend_url'] . 'tax/rate/ajaxSave/?isAjax=true';
        $curl = new BackendDecorator(new CurlTransport(), new Config());
        $curl->write(CurlInterface::POST, $url, '1.0', array(), $data);
        $response = $curl->read();
        $curl->close();

        $id = $this->getTaxRateId($response);
        return ['id' => $id];
    }

    /**
     * Return saved tax rate id
     *
     * @param $response
     * @return int|null
     * @throws \Exception
     */
    protected function getTaxRateId($response)
    {
        $data = json_decode($response, true);
        if ($data['success'] !== true) {
            throw new \Exception("Tax rate creation by curl handler was not successful! Response: $response");
        }
        return isset($data['tax_calculation_rate_id']) ? (int)$data['tax_calculation_rate_id'] : null;
    }
}
