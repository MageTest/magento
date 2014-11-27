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
namespace Magento\Shipping\Model\Carrier;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Model\Quote\Address\RateRequest;

class AbstractCarrierOnlineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test identification number of product
     *
     * @var int
     */
    protected $productId = 1;

    /**
     * @var AbstractCarrierOnline|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $carrier;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockRegistry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockItemData;

    protected function setUp()
    {
        $this->stockRegistry = $this->getMock(
            'Magento\CatalogInventory\Model\StockRegistry',
            [],
            [],
            '',
            false
        );
        $this->stockItemData = $this->getMock('Magento\CatalogInventory\Model\Stock\Item', [], [], '', false);

        $this->stockRegistry->expects($this->any())->method('getStockItem')
            ->with($this->productId, 10)
            ->will($this->returnValue($this->stockItemData));

        $objectManagerHelper = new ObjectManagerHelper($this);
        $carrierArgs = $objectManagerHelper->getConstructArguments(
            'Magento\Shipping\Model\Carrier\AbstractCarrierOnline',
            ['stockRegistry' => $this->stockRegistry]
        );
        $this->carrier = $this->getMockBuilder('Magento\Shipping\Model\Carrier\AbstractCarrierOnline')
            ->setConstructorArgs($carrierArgs)
            ->setMethods(['getConfigData', '_doShipmentRequest', 'collectRates'])
            ->getMock();
    }

    /**
     * @covers \Magento\Shipping\Model\Shipping::composePackagesForCarrier
     */
    public function testComposePackages()
    {
        $this->carrier->expects($this->any())->method('getConfigData')->will($this->returnCallback(function ($key) {
            $configData = [
                'max_package_weight' => 10,
                'showmethod'         => 1
            ];
            return isset($configData[$key]) ? $configData[$key] : 0;
        }));

        $product = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $product->expects($this->any())->method('getId')->will($this->returnValue($this->productId));

        $item = $this->getMockBuilder('\Magento\Sales\Model\Quote\Item')
            ->disableOriginalConstructor()
            ->setMethods(['getProduct', 'getQty', 'getWeight', '__wakeup', 'getStore'])
            ->getMock();
        $item->expects($this->any())->method('getProduct')->will($this->returnValue($product));

        $store = $this->getMock('Magento\Store\Model\Store', ['getWebsiteId'], [], '', false);
        $store->expects($this->any())
            ->method('getWebsiteId')
            ->will($this->returnValue(10));
        $item->expects($this->any())->method('getStore')->will($this->returnValue($store));

        $request = new RateRequest();
        $request->setData('all_items', [$item]);
        $request->setData('dest_postcode', 1);

        /** Testable service calls to CatalogInventory module */
        $this->stockRegistry->expects($this->atLeastOnce())->method('getStockItem')->with($this->productId);
        $this->stockItemData->expects($this->atLeastOnce())->method('getEnableQtyIncrements')
            ->will($this->returnValue(true));
        $this->stockItemData->expects($this->atLeastOnce())->method('getQtyIncrements')
            ->will($this->returnValue(5));
        $this->stockItemData->expects($this->atLeastOnce())->method('getIsQtyDecimal')->will($this->returnValue(true));
        $this->stockItemData->expects($this->atLeastOnce())->method('getIsDecimalDivided')
            ->will($this->returnValue(true));

        $this->carrier->proccessAdditionalValidation($request);
    }
}
