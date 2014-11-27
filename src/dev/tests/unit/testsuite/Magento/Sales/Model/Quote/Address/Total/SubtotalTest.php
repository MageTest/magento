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
namespace Magento\Sales\Model\Quote\Address\Total;

/**
 * Class SubtotalTest
 * @package Magento\Sales\Model\Quote\Address\Total
 * TODO refactor me
 */
class SubtotalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Sales\Model\Quote\Address\Total\Subtotal
     */
    protected $subtotalModel;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $stockItemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockRegistry;

    protected function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->subtotalModel = $this->objectManager->getObject('Magento\Sales\Model\Quote\Address\Total\Subtotal');
    }

    public function collectDataProvider()
    {
        return array(
            array(12, 10, false, 12, 10),
            array(12, 0, false, 12, 12),
            array(0, 10, false, 0, 10),
            array(12, 10, true, null, null),
            array(12, 10, false, 12, 10)
        );
    }

    /**
     * @dataProvider collectDataProvider
     *
     * @param int $price
     * @param int $originalPrice
     * @param bool $itemHasParent
     * @param int $expectedPrice
     * @param int $expectedOriginalPrice
     */
    public function testCollect($price, $originalPrice, $itemHasParent, $expectedPrice, $expectedOriginalPrice)
    {
        $this->stockRegistry = $this->getMockBuilder('Magento\CatalogInventory\Model\StockRegistry')
            ->disableOriginalConstructor()
            ->setMethods(['getStockItem', '__wakeup'])
            ->getMock();

        $this->stockItemMock = $this->getMock(
            'Magento\CatalogInventory\Model\Stock\Item',
            ['getIsInStock', '__wakeup'],
            [],
            '',
            false
        );

        $this->stockRegistry->expects($this->any())
            ->method('getStockItem')
            ->will($this->returnValue($this->stockItemMock));

        $priceCurrency = $this->getMockBuilder('Magento\Framework\Pricing\PriceCurrencyInterface')->getMock();
        $priceCurrency->expects($this->any())
            ->method('convert')
            ->willReturn(1231313);
            //@todo this is a wrong test and it does not check methods. Any digital value will be correct

        /** @var \Magento\Sales\Model\Quote\Item|\PHPUnit_Framework_MockObject_MockObject $quoteItem */
        $quoteItem = $this->objectManager->getObject(
            'Magento\Sales\Model\Quote\Item',
            [
                'stockRegistry' => $this->stockRegistry,
                'priceCurrency' => $priceCurrency,
            ]
        );
        /** @var \Magento\Sales\Model\Quote\Address|\PHPUnit_Framework_MockObject_MockObject $address */
        $address = $this->getMock(
            'Magento\Sales\Model\Quote\Address',
            array(),
            array(),
            '',
            false
        );
        $address->expects($this->any())->method('getAllNonNominalItems')->will(
            $this->returnValue(array($quoteItem))
        );

        /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject $product */
        $product = $this->getMock(
            'Magento\Catalog\Model\Product',
            array(),
            array(),
            '',
            false
        );
        $product->expects($this->any())->method('getPrice')->will($this->returnValue($originalPrice));
        /** @var \Magento\Sales\Model\Quote|\PHPUnit_Framework_MockObject_MockObject $quote */
        $quote = $this->getMock(
            'Magento\Sales\Model\Quote',
            array(),
            array(),
            '',
            false
        );
        $store = $this->objectManager->getObject('Magento\Store\Model\Store');
        $store->setCurrentCurrency('');

        $store = $this->getMock('Magento\Store\Model\Store', ['getWebsiteId'], [], '', false);
        $store->expects($this->any())
            ->method('getWebsiteId')
            ->will($this->returnValue(10));

        $product->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($store));

        $quote->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $quoteItem->setProduct($product)->setQuote($quote)->setOriginalCustomPrice($price);

        $address->expects($this->any())->method('getAllNonNominalItems')->will(
            $this->returnValue(array($quoteItem))
        );
        $address->expects($this->any())->method('getQuote')->will($this->returnValue($quote));
        $product->expects($this->any())->method('isVisibleInCatalog')->will($this->returnValue(true));

        $parentQuoteItem = false;
        if ($itemHasParent) {
            $parentQuoteItem = $this->getMock(
                'Magento\Sales\Model\Quote\Item',
                array(),
                array(),
                '',
                false
            );
            $parentQuoteItem->expects($this->any())->method('getProduct')->will($this->returnValue($product));
        }
        $quoteItem->setParentItem($parentQuoteItem);

        $priceModel = $this->getMock('\Magento\Catalog\Model\Product\Type\Price', array(), array(), '', false);
        $priceModel->expects($this->any())->method('getChildFinalPrice')->will(
            $this->returnValue($price)
        );
        $product->expects($this->any())->method('getPriceModel')->will(
            $this->returnValue($priceModel)
        );
        $product->expects($this->any())->method('getFinalPrice')->will($this->returnValue($price));
        $this->subtotalModel->collect($address);
        $this->assertEquals($expectedPrice, $quoteItem->getPrice());
        $this->assertEquals($expectedOriginalPrice, $quoteItem->getBaseOriginalPrice());
    }
}
