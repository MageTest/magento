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
namespace Magento\CatalogInventory\Api;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class StockStateTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StockStateTest extends \PHPUnit_Framework_TestCase
{
    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var \Magento\CatalogInventory\Api\StockStateInterface
     */
    protected $stockState;

    /**
     * @var \Magento\CatalogInventory\Model\Spi\StockStateProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockStateProvider;

    /**
     * @var \Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockRegistryProvider;

    /**
     * @var \Magento\CatalogInventory\Api\Data\StockInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stock;

    /**
     * @var \Magento\CatalogInventory\Api\Data\StockItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockItem;

    /**
     * @var \Magento\CatalogInventory\Api\Data\StockStatusInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockStatus;

    /**
     * @var \Magento\Framework\Object|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectResult;

    protected $productId = 111;
    protected $websiteId = 111;
    protected $qty = 111;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->stock = $this->getMock(
            '\Magento\CatalogInventory\Api\Data\StockInterface',
            [],
            [],
            '',
            false
        );
        $this->stockItem = $this->getMock(
            '\Magento\CatalogInventory\Api\Data\StockItemInterface',
            [],
            [],
            '',
            false
        );
        $this->stockStatus = $this->getMock(
            '\Magento\CatalogInventory\Api\Data\StockStatusInterface',
            [],
            [],
            '',
            false
        );
        $this->objectResult = $this->getMock(
            '\Magento\Framework\Object',
            [],
            [],
            '',
            false
        );

        $this->stockStateProvider = $this->getMock(
            'Magento\CatalogInventory\Model\Spi\StockStateProviderInterface',
            [
                'verifyStock',
                'verifyNotification',
                'checkQty',
                'suggestQty',
                'getStockQty',
                'checkQtyIncrements',
                'checkQuoteItemQty'
            ],
            [],
            '',
            false
        );
        $this->stockStateProvider->expects($this->any())->method('verifyStock')->willReturn(true);
        $this->stockStateProvider->expects($this->any())->method('verifyNotification')->willReturn(true);
        $this->stockStateProvider->expects($this->any())->method('checkQty')->willReturn(true);
        $this->stockStateProvider->expects($this->any())->method('suggestQty')->willReturn($this->qty);
        $this->stockStateProvider->expects($this->any())->method('getStockQty')->willReturn($this->qty);
        $this->stockStateProvider->expects($this->any())->method('checkQtyIncrements')->willReturn($this->objectResult);
        $this->stockStateProvider->expects($this->any())->method('checkQuoteItemQty')->willReturn($this->objectResult);

        $this->stockRegistryProvider = $this->getMock(
            'Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface',
            ['getStock', 'getStockItem', 'getStockStatus'],
            [],
            '',
            false
        );
        $this->stockRegistryProvider->expects($this->any())
            ->method('getStock')
            ->will($this->returnValue($this->stock));
        $this->stockRegistryProvider->expects($this->any())
            ->method('getStockItem')
            ->will($this->returnValue($this->stockItem));
        $this->stockRegistryProvider->expects($this->any())
            ->method('getStockStatus')
            ->will($this->returnValue($this->stockStatus));


        $this->stockState = $this->objectManagerHelper->getObject(
            '\Magento\CatalogInventory\Model\StockState',
            [
                'stockStateProvider' => $this->stockStateProvider,
                'stockRegistryProvider' => $this->stockRegistryProvider
            ]
        );
    }

    protected function tearDown()
    {
        $this->stockState = null;
    }

    public function testVerifyStock()
    {
        $this->assertEquals(
            true,
            $this->stockState->verifyStock($this->productId, $this->websiteId)
        );
    }

    public function testVerifyNotification()
    {
        $this->assertEquals(
            true,
            $this->stockState->verifyNotification($this->productId, $this->websiteId)
        );
    }

    public function testCheckQty()
    {
        $this->assertEquals(
            true,
            $this->stockState->checkQty($this->productId, $this->qty, $this->websiteId)
        );
    }

    public function testSuggestQty()
    {
        $this->assertEquals(
            $this->qty,
            $this->stockState->suggestQty($this->productId, $this->qty, $this->websiteId)
        );
    }

    public function testGetStockQty()
    {
        $this->assertEquals(
            $this->qty,
            $this->stockState->getStockQty($this->productId, $this->websiteId)
        );
    }

    public function testCheckQtyIncrements()
    {
        $this->assertEquals(
            $this->objectResult,
            $this->stockState->checkQtyIncrements($this->productId, $this->qty, $this->websiteId)
        );
    }

    public function testCheckQuoteItemQty()
    {
        $this->assertEquals(
            $this->objectResult,
            $this->stockState->checkQuoteItemQty(
                $this->productId,
                $this->qty,
                $this->qty,
                $this->qty,
                $this->websiteId
            )
        );
    }
}
