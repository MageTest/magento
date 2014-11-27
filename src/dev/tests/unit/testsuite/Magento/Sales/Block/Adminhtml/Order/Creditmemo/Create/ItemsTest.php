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
namespace Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class ItemsTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create\Items */
    protected $items;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Backend\Block\Template\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $contextMock;

    /** @var \Magento\CatalogInventory\Api\Data\StockItemInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $stockItemMock;

    /** @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject */
    protected $registryMock;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeConfig;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $stockConfiguration;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockRegistry;

    protected function setUp()
    {
        $this->contextMock = $this->getMock('Magento\Backend\Block\Template\Context', [], [], '', false);
        $this->stockRegistry = $this->getMockBuilder('Magento\CatalogInventory\Model\StockRegistry')
            ->disableOriginalConstructor()
            ->setMethods(['getStockItem', '__wakeup'])
            ->getMock();

        $this->stockItemMock = $this->getMock(
            'Magento\CatalogInventory\Model\Stock\Item',
            ['getManageStock', '__wakeup'],
            [],
            '',
            false
        );

        $this->stockConfiguration = $this->getMock(
            'Magento\CatalogInventory\Model\Configuration',
            ['__wakeup', 'canSubtractQty'],
            [],
            '',
            false
        );

        $this->stockRegistry->expects($this->any())
            ->method('getStockItem')
            ->will($this->returnValue($this->stockItemMock));

        $this->registryMock = $this->getMock('Magento\Framework\Registry');
        $this->scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->contextMock->expects($this->once())
            ->method('getScopeConfig')
            ->will($this->returnValue($this->scopeConfig));

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->items = $this->objectManagerHelper->getObject(
            'Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create\Items',
            [
                'context' => $this->contextMock,
                'stockRegistry' => $this->stockRegistry,
                'stockConfiguration' => $this->stockConfiguration,
                'registry' => $this->registryMock
            ]
        );
    }

    /**
     * @param bool $canReturnToStock
     * @param bool $manageStock
     * @param bool $result
     * @dataProvider canReturnItemsToStockDataProvider
     */
    public function testCanReturnItemsToStock($canReturnToStock, $manageStock, $result)
    {
        $productId = 7;
        $property = new \ReflectionProperty($this->items, '_canReturnToStock');
        $property->setAccessible(true);
        $this->assertNull($property->getValue($this->items));
        $this->stockConfiguration->expects($this->once())
            ->method('canSubtractQty')
            ->will($this->returnValue($canReturnToStock));

        if ($canReturnToStock) {
            $orderItem = $this->getMock(
                'Magento\Sales\Model\Order\Item',
                ['getProductId', '__wakeup', 'getStore'],
                [],
                '',
                false
            );
            $store = $this->getMock('Magento\Store\Model\Store', ['getWebsiteId'], [], '', false);
            $store->expects($this->once())
                ->method('getWebsiteId')
                ->will($this->returnValue(10));
            $orderItem->expects($this->any())
                ->method('getStore')
                ->will($this->returnValue($store));
            $orderItem->expects($this->once())
                ->method('getProductId')
                ->will($this->returnValue($productId));

            $creditMemoItem = $this->getMock(
                'Magento\Sales\Model\Order\Creditmemo\Item',
                ['setCanReturnToStock', 'getOrderItem', '__wakeup'],
                [],
                '',
                false
            );

            $creditMemo = $this->getMock('Magento\Sales\Model\Order\Creditmemo', [], [], '', false);
            $creditMemo->expects($this->once())
                ->method('getAllItems')
                ->will($this->returnValue([$creditMemoItem]));
            $creditMemoItem->expects($this->any())
                ->method('getOrderItem')
                ->will($this->returnValue($orderItem));

            $this->stockItemMock->expects($this->once())
                ->method('getManageStock')
                ->will($this->returnValue($manageStock));

            $creditMemoItem->expects($this->once())
                ->method('setCanReturnToStock')
                ->with($this->equalTo($manageStock))
                ->will($this->returnSelf());

            $order = $this->getMock('Magento\Sales\Model\Order', ['setCanReturnToStock', '__wakeup'], [], '', false);
            $order->expects($this->once())
                ->method('setCanReturnToStock')
                ->with($this->equalTo($manageStock))
                ->will($this->returnSelf());
            $creditMemo->expects($this->once())
                ->method('getOrder')
                ->will($this->returnValue($order));

            $this->registryMock->expects($this->any())
                ->method('registry')
                ->with('current_creditmemo')
                ->will($this->returnValue($creditMemo));
        }

        $this->assertSame($result, $this->items->canReturnItemsToStock());
        $this->assertSame($result, $property->getValue($this->items));
        // lazy load test
        $this->assertSame($result, $this->items->canReturnItemsToStock());
    }

    /**
     * @return array
     */
    public function canReturnItemsToStockDataProvider()
    {
        return [
            'cannot subtract by config' => [false, true, false],
            'manage stock is enabled' => [true, true, true],
            'manage stock is disabled' => [true, false, false],
        ];
    }
}
