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

namespace Magento\Sales\Model\AdminOrder\Product\Quote;

/**
 * Initializer test
 */
class InitializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Sales\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \Magento\Framework\Object|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Sales\Model\AdminOrder\Product\Quote\Initializer
     */
    protected $model;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $stockItemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockRegistry;

    protected function setUp()
    {
        $this->quoteMock = $this->getMock(
            'Magento\Sales\Model\Quote',
            ['addProduct', '__wakeup', 'getStore'],
            [],
            '',
            false
        );

        $this->productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['getId', 'setIsQtyDecimal', 'setCartQty', '__wakeup'],
            [],
            '',
            false
        );

        $this->configMock = $this->getMock(
            'Magento\Framework\Object',
            ['getQty', 'setQty'],
            [],
            '',
            false
        );

        $this->stockRegistry = $this->getMockBuilder('Magento\CatalogInventory\Model\StockRegistry')
            ->disableOriginalConstructor()
            ->setMethods(['getStockItem', '__wakeup'])
            ->getMock();

        $this->stockItemMock = $this->getMock(
            'Magento\CatalogInventory\Model\Stock\Item',
            ['getIsQtyDecimal', '__wakeup'],
            [],
            '',
            false
        );

        $this->stockRegistry->expects($this->any())
            ->method('getStockItem')
            ->will($this->returnValue($this->stockItemMock));

        $store = $this->getMock('Magento\Store\Model\Store', ['getWebsiteId'], [], '', false);
        $store->expects($this->once())
            ->method('getWebsiteId')
            ->will($this->returnValue(10));
        $this->quoteMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($store));

        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $this->objectManager
            ->getObject(
                'Magento\Sales\Model\AdminOrder\Product\Quote\Initializer',
                ['stockRegistry' => $this->stockRegistry]
            );
    }

    public function testInitWithDecimalQty()
    {
        $quoteItemMock = $this->getMock(
            '\Magento\Sales\Model\Quote\Item',
            ['getStockId', 'getIsQtyDecimal', '__wakeup'],
            [],
            '',
            false
        );

        $this->stockItemMock->expects($this->once())
            ->method('getIsQtyDecimal')
            ->will($this->returnValue(10));

        $this->productMock->expects($this->once())
            ->method('getId')
            ->will($this->returnSelf());

        $this->productMock->expects($this->once())
            ->method('setIsQtyDecimal')
            ->will($this->returnSelf());

        $this->productMock->expects($this->once())
            ->method('setCartQty')
            ->will($this->returnSelf());

        $this->configMock->expects($this->once())
            ->method('getQty')
            ->will($this->returnValue(20));

        $this->configMock->expects($this->never())
            ->method('setQty');

        $this->quoteMock->expects($this->once())
            ->method('addProduct')
            ->will($this->returnValue($quoteItemMock));

        $this->assertInstanceOf(
            'Magento\Sales\Model\Quote\Item',
            $this->model->init(
                $this->quoteMock,
                $this->productMock,
                $this->configMock
            )
        );
    }

    public function testInitWithNonDecimalQty()
    {
        $quoteItemMock = $this->getMock(
            '\Magento\Sales\Model\Quote\Item',
            ['getStockId', 'getIsQtyDecimal', '__wakeup'],
            [],
            '',
            false
        );

        $this->productMock->expects($this->once())
            ->method('getId')
            ->will($this->returnSelf());

        $this->productMock->expects($this->never())
            ->method('setIsQtyDecimal');

        $this->productMock->expects($this->once())
            ->method('setCartQty')
            ->will($this->returnSelf());

        $this->configMock->expects($this->exactly(2))
            ->method('getQty')
            ->will($this->returnValue(10));

        $this->configMock->expects($this->once())
            ->method('setQty')
            ->will($this->returnSelf());


        $this->quoteMock->expects($this->once())
            ->method('addProduct')
            ->will($this->returnValue($quoteItemMock));

        $this->assertInstanceOf(
            'Magento\Sales\Model\Quote\Item',
            $this->model->init(
                $this->quoteMock,
                $this->productMock,
                $this->configMock
            )
        );
    }
}
