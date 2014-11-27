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
namespace Magento\Framework\Api\Config;

/**
 * Tests for \Magento\Framework\Api\Config\Reader
 */
class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Api\Config\Reader
     */
    protected $_model;

    /**
     * @var array
     */
    protected $_fileList;

    /**
     * @var \Magento\Framework\App\Arguments\FileResolver\Primary
     */
    protected $_fileResolverMock;

    /**
     * @var \Magento\Framework\App\Arguments\ValidationState
     */
    protected $_validationState;

    /**
     * @var \Magento\Framework\Api\Config\SchemaLocator
     */
    protected $_schemaLocator;

    /**
     * @var \Magento\Framework\Api\Config\Converter
     */
    protected $_converter;

    protected function setUp()
    {
        $fixturePath = realpath(__DIR__ . '/_files') . '/';
        $this->_fileList = array(
            file_get_contents($fixturePath . 'config_one.xml'),
            file_get_contents($fixturePath . 'config_two.xml')
        );

        $this->_fileResolverMock = $this->getMockBuilder('Magento\Framework\App\Arguments\FileResolver\Primary')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
        $this->_fileResolverMock->expects($this->once())
            ->method('get')
            ->will($this->returnValue($this->_fileList));

        $this->_converter = new \Magento\Framework\Api\Config\Converter();

        $this->_validationState = new \Magento\Framework\App\Arguments\ValidationState(
            \Magento\Framework\App\State::MODE_DEFAULT
        );
        $this->_schemaLocator = new \Magento\Framework\Api\Config\SchemaLocator();
    }

    public function testMerge()
    {
        $model = new \Magento\Framework\Api\Config\Reader(
            $this->_fileResolverMock,
            $this->_converter,
            $this->_schemaLocator,
            $this->_validationState
        );

        $expectedArray = [
            'Magento\Tax\Service\V1\Data\TaxRate' => [],
            'Magento\Catalog\Service\Data\V1\Product' => [
                'stock_item' => "Magento\CatalogInventory\Service\Data\V1\StockItem"
            ],
            'Magento\Customer\Service\V1\Data\Customer' => [
                'custom_1' => "Magento\Customer\Service\V1\Data\CustomerCustom",
                'custom_2' => "Magento\CustomerExtra\Service\V1\Data\CustomerCustom22",
                'custom_3' => "Magento\Customer\Service\V1\Data\CustomerCustom3"
            ]
        ];

        $this->assertEquals($expectedArray, $model->read('global'));
    }
}
