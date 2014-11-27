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

/**
 * Test class for \Magento\Sales\Block\Adminhtml\Order\Totals\TaxTest
 */
namespace Magento\Sales\Block\Adminhtml\Order\Totals;

class TaxTest extends \PHPUnit_Framework_TestCase
{

    /** @var  \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Block\Adminhtml\Order\Totals\Tax */
    private $taxMock;

    public function setUp()
    {
        $getCalculatedTax = [
            'tax' => 'tax',
            'shipping_tax' => 'shipping_tax'
        ];
        $taxHelperMock = $this->getMockBuilder('Magento\Tax\Helper\Data')
            ->setMethods(['getCalculatedTaxes'])
            ->disableOriginalConstructor()
            ->getMock();
        $taxHelperMock->expects($this->any())
            ->method('getCalculatedTaxes')
            ->will($this->returnValue($getCalculatedTax));

        $this->taxMock = $this->getMockBuilder('Magento\Sales\Block\Adminhtml\Order\Totals\Tax')
            ->setConstructorArgs($this->_getConstructArguments($taxHelperMock))
            ->setMethods(['getOrder', 'getSource'])
            ->getMock();

    }
    
    /**
     * Test method for getFullTaxInfo
     *
     * @param \Magento\Sales\Model\Order $source
     * @param array $getCalculatedTax
     * @param array $getShippingTax
     * @param array $expectedResult
     *
     * @dataProvider getFullTaxInfoDataProvider
     */
    public function testGetFullTaxInfo($source, $expectedResult)
    {
        $this->taxMock->expects($this->once())
            ->method('getOrder')
            ->will($this->returnValue($source));

        $actualResult = $this->taxMock->getFullTaxInfo();
        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * Test method for getFullTaxInfo with invoice or creditmemo
     *
     * @param \Magento\Sales\Model\Order\Invoice|\Magento\Sales\Model\Order\Creditmemo $source
     * @param array $expectedResult
     *
     * @dataProvider getCreditAndInvoiceFullTaxInfoDataProvider
     */
    public function testGetFullTaxInfoWithCreditAndInvoice(
        $source,
        $expectedResult
    ) {
        $this->taxMock->expects($this->once())
            ->method('getSource')
            ->will($this->returnValue($source));

        $actualResult = $this->taxMock->getFullTaxInfo();
        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * Provide the tax helper mock as a constructor argument
     *
     * @param $taxHelperMock
     * @return array
     */
    protected function _getConstructArguments($taxHelperMock)
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        return $objectManagerHelper->getConstructArguments(
            'Magento\Sales\Block\Adminhtml\Order\Totals\Tax',
            ['taxHelper' => $taxHelperMock]
        );
    }

    /**
     * Data provider.
     * 1st Case : $source is not an instance of \Magento\Sales\Model\Order
     * 2nd Case : getCalculatedTaxes and getShippingTax return value
     *
     * @return array
     */
    public function getFullTaxInfoDataProvider()
    {
        $salesModelOrderMock = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->getMock();
        return [
            'source is not an instance of \Magento\Sales\Model\Order' =>
                [null, []],
            'source is an instance of \Magento\Sales\Model\Order and has reasonable data' =>
                [
                    $salesModelOrderMock,
                    [
                        'tax' => 'tax',
                        'shipping_tax' => 'shipping_tax',
                    ]
                ]
        ];
    }

    /**
     * Data provider.
     * 1st Case : $current an instance of \Magento\Sales\Model\Invoice
     * 2nd Case : $current an instance of \Magento\Sales\Model\Creditmemo
     *
     * @return array
     */
    public function getCreditAndInvoiceFullTaxInfoDataProvider()
    {
        $invoiceMock = $this->getMockBuilder('Magento\Sales\Model\Order\Invoice')
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup'])
            ->getMock();
        $creditMemoMock = $this->getMockBuilder('Magento\Sales\Model\Order\Creditmemo')
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup'])
            ->getMock();

        $expected = [
            'tax' => 'tax',
            'shipping_tax' => 'shipping_tax',
        ];
        return [
            'invoice' =>
                [$invoiceMock, $expected],
            'creditMemo' =>
                [$creditMemoMock, $expected]
        ];
    }
}
