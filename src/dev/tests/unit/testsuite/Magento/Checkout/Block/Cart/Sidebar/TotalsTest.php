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
namespace Magento\Checkout\Block\Cart\Sidebar;

use Magento\Framework\Object;

class TotalsTest extends \PHPUnit_Framework_TestCase
{
    const SUBTOTAL = 10;

    /**
     * @var \Magento\Checkout\Block\Cart\Sidebar\Totals
     */
    protected $totalsObj;

    /**
     * @var \Magento\Sales\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quote;

    protected function setUp()
    {
        parent::setUp();
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->quote = $this->getMockBuilder('Magento\Sales\Model\Quote')
            ->disableOriginalConstructor()
            ->setMethods(['getTotals', '__wakeup'])
            ->getMock();

        $checkoutSession = $this->getMockBuilder('\Magento\Checkout\Model\Session')
            ->disableOriginalConstructor()
            ->setMethods(['getQuote', '__wakeup'])
            ->getMock();

        $checkoutSession->expects($this->once())
            ->method('getQuote')
            ->will($this->returnValue($this->quote));

        $this->totalsObj = $objectManager->getObject(
            '\Magento\Checkout\Block\Cart\Sidebar\Totals',
            ['checkoutSession' => $checkoutSession]
        );
    }

    public function testGetSubtotal()
    {
        $subtotal = new Object(['value' => self::SUBTOTAL]);
        $totals = ['subtotal' => $subtotal];
        $this->quote->expects($this->once())
            ->method('getTotals')
            ->will($this->returnValue($totals));

        $this->assertEquals(self::SUBTOTAL, $this->totalsObj->getSubtotal());
    }

    public function testGetSubtotalZero()
    {
        $totals = [];
        $this->quote->expects($this->once())
            ->method('getTotals')
            ->will($this->returnValue($totals));

        $this->assertEquals(0, $this->totalsObj->getSubtotal());
    }
}
