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
namespace Magento\Framework\Pricing\Adjustment;

/**
 * Class CalculatorTest
 *
 */
class CalculatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Pricing\Adjustment\Calculator
     */
    protected $model;

    /**
     * @var \Magento\Framework\Pricing\Amount\AmountFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $amountFactoryMock;

    public function setUp()
    {
        $this->amountFactoryMock = $this->getMockBuilder('Magento\Framework\Pricing\Amount\AmountFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new \Magento\Framework\Pricing\Adjustment\Calculator($this->amountFactoryMock);
    }

    public function tearDown()
    {
        $this->model = null;
        $this->amountFactoryMock = null;
    }

    /**
     * Test getAmount()
     */
    public function testGetAmount()
    {
        $amount = 10;
        $fullAmount = $amount;
        $newAmount = 15;
        $taxAdjustmentCode = 'tax';
        $weeeAdjustmentCode = 'weee';
        $adjustment = 5;
        $expectedAdjustments = [
            $taxAdjustmentCode => $adjustment,
            $weeeAdjustmentCode => $adjustment
        ];

        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->setMethods(['getPriceInfo', '__wakeup'])
            ->getMock();

        $taxAdjustmentMock = $this->getMockBuilder('Magento\Tax\Pricing\Adjustment')
            ->disableOriginalConstructor()
            ->getMock();
        $taxAdjustmentMock->expects($this->once())
            ->method('getAdjustmentCode')
            ->will($this->returnValue($taxAdjustmentCode));
        $taxAdjustmentMock->expects($this->once())
            ->method('isIncludedInBasePrice')
            ->will($this->returnValue(true));
        $taxAdjustmentMock->expects($this->once())
            ->method('extractAdjustment')
            ->with($this->equalTo($amount), $this->equalTo($productMock))
            ->will($this->returnValue($adjustment));
        $taxAdjustmentMock->expects($this->once())
            ->method('applyAdjustment')
            ->with($this->equalTo($fullAmount), $this->equalTo($productMock))
            ->will($this->returnValue($amount));

        $weeeAdjustmentMock = $this->getMockBuilder('Magento\Weee\Pricing\Adjustment')
            ->disableOriginalConstructor()
            ->getMock();
        $weeeAdjustmentMock->expects($this->once())
            ->method('getAdjustmentCode')
            ->will($this->returnValue($weeeAdjustmentCode));
        $weeeAdjustmentMock->expects($this->once())
            ->method('isIncludedInBasePrice')
            ->will($this->returnValue(false));
        $weeeAdjustmentMock->expects($this->once())
            ->method('isIncludedInDisplayPrice')
            ->with($this->equalTo($productMock))
            ->will($this->returnValue(true));
        $weeeAdjustmentMock->expects($this->once())
            ->method('applyAdjustment')
            ->with($this->equalTo($fullAmount), $this->equalTo($productMock))
            ->will($this->returnValue($newAmount));

        $adjustments = [$taxAdjustmentMock, $weeeAdjustmentMock];

        $priceInfoMock = $this->getMockBuilder('\Magento\Framework\Pricing\PriceInfo\Base')
            ->disableOriginalConstructor()
            ->getMock();
        $priceInfoMock->expects($this->any())
            ->method('getAdjustments')
            ->will($this->returnValue($adjustments));

        $productMock->expects($this->any())
            ->method('getPriceInfo')
            ->will($this->returnValue($priceInfoMock));

        $amountBaseMock = $this->getMockBuilder('Magento\Framework\Pricing\Amount\Base')
            ->disableOriginalConstructor()
            ->getMock();

        $this->amountFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->equalTo($newAmount), $this->equalTo($expectedAdjustments))
            ->will($this->returnValue($amountBaseMock));
        $result = $this->model->getAmount($amount, $productMock);
        $this->assertInstanceOf('Magento\Framework\Pricing\Amount\AmountInterface', $result);
    }

    public function testGetAmountExclude()
    {
        $amount = 10;
        $fullamount = 10;
        $taxAdjustmentCode = 'tax';
        $weeeAdjustmentCode = 'weee';
        $adjustment = 5;
        $expectedAdjustments = [];

        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->setMethods(['getPriceInfo', '__wakeup'])
            ->getMock();

        $taxAdjustmentMock = $this->getMockBuilder('Magento\Tax\Pricing\Adjustment')
            ->disableOriginalConstructor()
            ->getMock();
        $taxAdjustmentMock->expects($this->once())
            ->method('getAdjustmentCode')
            ->will($this->returnValue($taxAdjustmentCode));
        $taxAdjustmentMock->expects($this->once())
            ->method('isIncludedInBasePrice')
            ->will($this->returnValue(true));
        $taxAdjustmentMock->expects($this->once())
            ->method('extractAdjustment')
            ->with($this->equalTo($amount), $this->equalTo($productMock))
            ->will($this->returnValue($adjustment));
        $taxAdjustmentMock->expects($this->once())
            ->method('applyAdjustment')
            ->with($this->equalTo($fullamount), $this->equalTo($productMock))
            ->will($this->returnValue($amount));

        $weeeAdjustmentMock = $this->getMockBuilder('Magento\Weee\Pricing\Adjustment')
            ->disableOriginalConstructor()
            ->getMock();
        $weeeAdjustmentMock->expects($this->once())
            ->method('getAdjustmentCode')
            ->will($this->returnValue($weeeAdjustmentCode));
        $weeeAdjustmentMock->expects($this->once())
            ->method('isIncludedInBasePrice')
            ->will($this->returnValue(false));
        $weeeAdjustmentMock->expects($this->once())
            ->method('isIncludedInDisplayPrice')
            ->with($this->equalTo($productMock))
            ->will($this->returnValue(true));
        $weeeAdjustmentMock->expects($this->never())
            ->method('applyAdjustment');

        $adjustments = [$taxAdjustmentMock, $weeeAdjustmentMock];

        $priceInfoMock = $this->getMockBuilder('\Magento\Framework\Pricing\PriceInfo\Base')
            ->disableOriginalConstructor()
            ->getMock();
        $priceInfoMock->expects($this->any())
            ->method('getAdjustments')
            ->will($this->returnValue($adjustments));

        $productMock->expects($this->any())
            ->method('getPriceInfo')
            ->will($this->returnValue($priceInfoMock));

        $amountBaseMock = $this->getMockBuilder('Magento\Framework\Pricing\Amount\Base')
            ->disableOriginalConstructor()
            ->getMock();

        $this->amountFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->equalTo($amount), $this->equalTo($expectedAdjustments))
            ->will($this->returnValue($amountBaseMock));
        $result = $this->model->getAmount($amount, $productMock, true);
        $this->assertInstanceOf('Magento\Framework\Pricing\Amount\AmountInterface', $result);
    }
}
