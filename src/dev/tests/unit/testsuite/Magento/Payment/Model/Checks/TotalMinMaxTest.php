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

namespace Magento\Payment\Model\Checks;

class TotalMinMaxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Payment min total value
     */
    const PAYMENT_MIN_TOTAL = 2;

    /**
     * Payment max total value
     */
    const PAYMENT_MAX_TOTAL = 5;

    /**
     * @dataProvider paymentMethodDataProvider
     * @param int $baseGrandTotal
     * @param bool $expectation
     */
    public function testIsApplicable($baseGrandTotal, $expectation)
    {
        $paymentMethod = $this->getMockBuilder(
            'Magento\Payment\Model\Checks\PaymentMethodChecksInterface'
        )->disableOriginalConstructor()->setMethods([])->getMock();
        $paymentMethod->expects($this->at(0))->method('getConfigData')->with(
            TotalMinMax::MIN_ORDER_TOTAL
        )->will($this->returnValue(self::PAYMENT_MIN_TOTAL));
        $paymentMethod->expects($this->at(1))->method('getConfigData')->with(
            TotalMinMax::MAX_ORDER_TOTAL
        )->will($this->returnValue(self::PAYMENT_MAX_TOTAL));

        $quote = $this->getMockBuilder('Magento\Sales\Model\Quote')->disableOriginalConstructor()->setMethods(
            ['getBaseGrandTotal', '__wakeup']
        )->getMock();
        $quote->expects($this->once())->method('getBaseGrandTotal')->will($this->returnValue($baseGrandTotal));

        $model = new TotalMinMax();
        $this->assertEquals($expectation, $model->isApplicable($paymentMethod, $quote));
    }

    /**
     * @return array
     */
    public function paymentMethodDataProvider()
    {
        return [[1, false], [6, false], [3, true]];
    }
}
