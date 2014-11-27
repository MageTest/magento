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
namespace Magento\CustomerImportExport\Model\Export;

use Magento\CustomerImportExport\Model\Import\Address as ImportAddress;
/**
 * Test for customer address export model
 *
 * @magentoDataFixture Magento/Customer/_files/import_export/customer_with_addresses.php
 */
class AddressTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Address
     */
    protected $_model;

    /**
     * List of existing websites
     *
     * @var array
     */
    protected $_websites = array();

    protected function setUp()
    {
        parent::setUp();
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\CustomerImportExport\Model\Export\Address'
        );

        $websites = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\StoreManagerInterface'
        )->getWebsites(
            true
        );
        /** @var $website \Magento\Store\Model\Website */
        foreach ($websites as $website) {
            $this->_websites[$website->getId()] = $website->getCode();
        }
    }

    /**
     * Test export method
     */
    public function testExport()
    {
        $websiteCode = Address::COLUMN_WEBSITE;
        $emailCode = Address::COLUMN_EMAIL;
        $entityIdCode = Address::COLUMN_ADDRESS_ID;

        $expectedAttributes = array();
        /** @var $collection \Magento\Customer\Model\Resource\Address\Attribute\Collection */
        $collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Customer\Model\Resource\Address\Attribute\Collection'
        );
        /** @var $attribute \Magento\Customer\Model\Attribute */
        foreach ($collection as $attribute) {
            $expectedAttributes[] = $attribute->getAttributeCode();
        }

        // Get customer default addresses column name to customer attribute mapping array.
        $defaultAddressMap = ImportAddress::getDefaultAddressAttributeMapping();

        $this->_model->setWriter(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                'Magento\ImportExport\Model\Export\Adapter\Csv'
            )
        );
        $this->_model->setParameters(array());

        $data = $this->_csvToArray($this->_model->export(), $entityIdCode);

        $this->assertEquals(
            count($expectedAttributes),
            count(array_intersect($expectedAttributes, $data['header'])),
            'Expected attribute codes were not exported'
        );

        $this->assertNotEmpty($data['data'], 'No data was exported');

        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        // Get addresses
        /** @var $customers \Magento\Customer\Model\Customer[] */
        $customers = $objectManager->get(
            'Magento\Framework\Registry'
        )->registry(
            '_fixture/Magento_ImportExport_Customers_Array'
        );
        foreach ($customers as $customer) {
            /** @var $address \Magento\Customer\Model\Address */
            foreach ($customer->getAddresses() as $address) {
                // Check unique key
                $data['data'][$address->getId()][$websiteCode] = $this->_websites[$customer->getWebsiteId()];
                $data['data'][$address->getId()][$emailCode] = $customer->getEmail();
                $data['data'][$address->getId()][$entityIdCode] = $address->getId();

                // Check by expected attributes
                foreach ($expectedAttributes as $code) {
                    if (!in_array($code, $this->_model->getDisabledAttributes())) {
                        $this->assertEquals(
                            $address->getData($code),
                            $data['data'][$address->getId()][$code],
                            'Attribute "' . $code . '" is not equal'
                        );
                    }
                }

                // Check customer default addresses column name to customer attribute mapping array
                foreach ($defaultAddressMap as $exportCode => $code) {
                    $this->assertEquals(
                        $address->getData($code),
                        (int)$data['data'][$address->getId()][$exportCode],
                        'Attribute "' . $code . '" is not equal'
                    );
                }
            }
        }
    }

    /**
     * Get possible gender values for filter
     *
     * @return array
     */
    public function getGenderFilterValueDataProvider()
    {
        return array('male' => array('$genderFilterValue' => 1), 'female' => array('$genderFilterValue' => 2));
    }

    /**
     * Test export method if filter was set
     *
     * @dataProvider getGenderFilterValueDataProvider
     *
     * @param int $genderFilterValue
     */
    public function testExportWithFilter($genderFilterValue)
    {
        $entityIdCode = Address::COLUMN_ADDRESS_ID;

        $this->_model->setWriter(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                'Magento\ImportExport\Model\Export\Adapter\Csv'
            )
        );

        $filterData = array('export_filter' => array('gender' => $genderFilterValue));

        $this->_model->setParameters($filterData);

        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        // Get expected address count
        /** @var $customers \Magento\Customer\Model\Customer[] */
        $customers = $objectManager->get(
            'Magento\Framework\Registry'
        )->registry(
            '_fixture/Magento_ImportExport_Customers_Array'
        );
        $expectedCount = 0;
        foreach ($customers as $customer) {
            if ($customer->getGender() == $genderFilterValue) {
                $expectedCount += count($customer->getAddresses());
            }
        }

        $data = $this->_csvToArray($this->_model->export(), $entityIdCode);

        $this->assertCount($expectedCount, $data['data']);
    }

    /**
     * Test entity type code value
     */
    public function testGetEntityTypeCode()
    {
        $this->assertEquals('customer_address', $this->_model->getEntityTypeCode());
    }

    /**
     * Test type of attribute collection
     */
    public function testGetAttributeCollection()
    {
        $this->assertInstanceOf(
            'Magento\Customer\Model\Resource\Address\Attribute\Collection',
            $this->_model->getAttributeCollection()
        );
    }

    /**
     * Export CSV string to array
     *
     * @param string $content
     * @param mixed $entityId
     * @return array
     */
    protected function _csvToArray($content, $entityId = null)
    {
        $data = array('header' => array(), 'data' => array());

        $lines = str_getcsv($content, "\n");
        foreach ($lines as $index => $line) {
            if ($index == 0) {
                $data['header'] = str_getcsv($line);
            } else {
                $row = array_combine($data['header'], str_getcsv($line));
                if (!is_null($entityId) && !empty($row[$entityId])) {
                    $data['data'][$row[$entityId]] = $row;
                } else {
                    $data['data'][] = $row;
                }
            }
        }

        return $data;
    }

    /**
     * Test filename getter. Filename must be set in constructor.
     */
    public function testGetFileName()
    {
        $this->assertEquals($this->_model->getEntityTypeCode(), $this->_model->getFileName());
    }
}
