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

namespace Magento\Tax\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class TaxRate Repository
 *
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class TaxRate extends AbstractRepository
{
    /**
     * @param array $defaultConfig
     * @param array $defaultData
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['US-CA-*-Rate 1'] = [
            'tax_calculation_rate_id' => '1',
            'tax_country_id' => 'US',
            'tax_region_id' => '12',
            'tax_postcode' => '*',
            'code' => 'US-CA-*-Rate 1',
            'rate' => '8.2500',
            'zip_is_range' => '',
            'zip_from' => '',
            'zip_to' => '',
            'id' => '1',
            'mtf_dataset_name' => 'US-CA-*-Rate 1',
        ];

        $this->_data['US-NY-*-Rate 1'] = [
            'tax_calculation_rate_id' => '2',
            'tax_country_id' => 'US',
            'tax_region_id' => '43',
            'tax_postcode' => '*',
            'code' => 'US-NY-*-Rate 1',
            'rate' => '8.3750',
            'zip_is_range' => '',
            'zip_from' => '',
            'zip_to' => '',
            'id' => '2',
            'mtf_dataset_name' => 'US-NY-*-Rate 1',
        ];

        $this->_data['us_ca_rate_8_25'] = [
            'code' => 'Tax Rate %isolation%',
            'rate' => '8.25',
            'tax_country_id' => 'United States',
            'tax_postcode' => '90230',
            'tax_region_id' => 'California',
        ];

        $this->_data['us_ca_rate_8_25_no_zip'] = [
            'code' => 'Tax Rate %isolation%',
            'rate' => '8.25',
            'tax_country_id' => 'United States',
            'tax_postcode' => '*',
            'tax_region_id' => 'California',
        ];

        $this->_data['us_ca_rate_8_375'] = [
            'code' => 'Tax Rate %isolation%',
            'rate' => '8.375',
            'tax_country_id' => 'United States',
            'tax_postcode' => '*',
            'tax_region_id' => 'California',
        ];

        $this->_data['us_ny_rate_8_375'] = [
            'code' => 'Tax Rate %isolation%',
            'rate' => '8.375',
            'tax_country_id' => 'United States',
            'tax_region_id' => 'New York',
            'tax_postcode' => '*',
        ];

        $this->_data['us_ny_rate_8_25'] = [
            'code' => 'Tax Rate %isolation%',
            'rate' => '8.25',
            'tax_country_id' => 'United States',
            'tax_region_id' => 'New York',
            'tax_postcode' => '*',
        ];

        $this->_data['us_ny_rate_8_1'] = [
            'code' => 'US-NY-*-%isolation%',
            'rate' => '8.1',
            'tax_country_id' => 'United States',
            'tax_region_id' => 'New York',
            'tax_postcode' => '*',
        ];

        $this->_data['paypal_rate_8_25'] = [
            'code' => 'Tax Rate %isolation%',
            'rate' => '8.25',
            'tax_country_id' => 'United States',
            'tax_postcode' => '95131',
            'tax_region_id' => 'California',
        ];

        $this->_data['uk_full_tax_rate'] = [
            'code' => 'Tax Rate %isolation%',
            'rate' => '20',
            'tax_country_id' => 'United Kingdom',
            'tax_postcode' => '*',
        ];

        $this->_data['default'] = [
            'code' => 'TaxIdentifier%isolation%',
            'tax_postcode' => '*',
            'tax_country_id' => 'Australia',
            'rate' => '20'
        ];

        $this->_data['withZipRange'] = [
            'code' => 'TaxIdentifier%isolation%',
            'zip_is_range' => 'Yes',
            'zip_from' => '90001',
            'zip_to' => '96162',
            'tax_country_id' => 'United States',
            'tax_region_id' => 'California',
            'rate' => '15.5'
        ];

        $this->_data['withFixedZip'] = [
            'code' => 'TaxIdentifier%isolation%',
            'tax_postcode' => '*',
            'tax_country_id' => 'United States',
            'tax_region_id' => 'Texas',
            'rate' => '20'
        ];

        $this->_data['us_ut_fixed_zip_rate_20'] = [
            'code' => 'TaxIdentifier%isolation%',
            'tax_postcode' => '84001',
            'tax_country_id' => 'United States',
            'tax_region_id' => 'Utah',
            'rate' => '20'
        ];

        $this->_data['tx_rate_10'] = [
            'code' => 'TaxIdentifier%isolation%',
            'tax_postcode' => '*',
            'tax_country_id' => 'United States',
            'tax_region_id' => 'Texas',
            'rate' => '10'
        ];

        $this->_data['ny_rate_20'] = [
            'code' => 'TaxIdentifier%isolation%',
            'tax_postcode' => '*',
            'tax_country_id' => 'United States',
            'tax_region_id' => 'New York',
            'rate' => '20'
        ];

        $this->_data['ca_rate_30'] = [
            'code' => 'TaxIdentifier%isolation%',
            'tax_postcode' => '*',
            'tax_country_id' => 'United States',
            'tax_region_id' => 'California',
            'rate' => '30'
        ];
    }
}
