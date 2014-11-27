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

namespace Magento\Paypal\Block\Express;

class ReviewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\View\Asset\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $assetRepo;

    /**
     * @var Review
     */
    protected $model;

    protected function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $layout = $this->getMock('Magento\Framework\View\LayoutInterface', [], [], '', false);
        $eventManager = $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false);
        $scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface', [], [], '', false);

        $scopeConfig->expects($this->any())
            ->method('getValue')
            ->with(
                $this->stringContains('advanced/modules_disable_output/'),
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )->will($this->returnValue(false));

        $urlBuilder = $this->getMock('Magento\Framework\UrlInterface');
        $urlBuilder->expects($this->any())->method('getUrl')->will($this->returnArgument(0));

        $context = $this->getMock(
            'Magento\Framework\View\Element\Template\Context',
            ['getLayout', 'getEventManager', 'getScopeConfig', 'getRequest', 'getAssetRepository', 'getUrlBuilder'],
            [],
            '',
            false
        );

        $this->request = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $this->assetRepo = $this->getMock('Magento\Framework\View\Asset\Repository', [], [], '', false);

        $context->expects($this->any())->method('getLayout')->will($this->returnValue($layout));
        $context->expects($this->any())->method('getEventManager')->will($this->returnValue($eventManager));
        $context->expects($this->any())->method('getScopeConfig')->will($this->returnValue($scopeConfig));
        $context->expects($this->any())->method('getRequest')->will($this->returnValue($this->request));
        $context->expects($this->any())->method('getAssetRepository')->will($this->returnValue($this->assetRepo));
        $context->expects($this->any())->method('getUrlBuilder')->will($this->returnValue($urlBuilder));

        $this->model = $helper->getObject('Magento\Paypal\Block\Express\Review', ['context' => $context]);
    }

    /**
     * @param bool $isSecure
     * @dataProvider getViewFileUrlDataProvider
     */
    public function testGetViewFileUrl($isSecure)
    {
        $this->request->expects($this->once())->method('isSecure')->will($this->returnValue($isSecure));
        $this->assetRepo->expects($this->once())
            ->method('getUrlWithParams')
            ->with('some file', $this->callback(function ($value) use ($isSecure) {
                return isset($value['_secure']) && $value['_secure'] === $isSecure;
            }))
            ->will($this->returnValue('result url'));
        $this->assertEquals('result url', $this->model->getViewFileUrl('some file'));
    }

    public function getViewFileUrlDataProvider()
    {
        return [[true], [false]];
    }

    public function testBeforeToHtmlWhenQuoteIsNotVirtual()
    {
        $quote = $this->_getQuoteMock();
        $quote->expects($this->any())->method('getIsVirtual')->will($this->returnValue(false));
        $quote->setMayEditShippingMethod('MayEditShippingMethod');

        $shippingRate = new \Magento\Framework\Object(['code' => 'Rate 1']);
        $shippingRates = [
            [$shippingRate]
        ];
        $quote->getShippingAddress()
            ->expects($this->any())
            ->method('getGroupedAllShippingRates')
            ->will($this->returnValue($shippingRates));
        $quote->getShippingAddress()
            ->expects($this->any())
            ->method('getShippingMethod')
            ->will($this->returnValue($shippingRate->getCode()));

        $this->model->setQuote($quote);
        $this->model->toHtml();

        $this->assertEquals(
            $this->model->getPaymentMethodTitle(),
            $quote->getPayment()->getMethodInstance()->getTitle()
        );
        $this->assertTrue($this->model->getShippingRateRequired());
        $this->assertSame($shippingRates, $this->model->getShippingRateGroups());
        $this->assertSame($shippingRate, $this->model->getCurrentShippingRate());
        $this->assertNotNull($this->model->getCanEditShippingAddress());
        $this->assertEquals($quote->getMayEditShippingMethod(), $this->model->getCanEditShippingMethod());
        $this->assertContains('paypal/express/saveShippingMethod', $this->model->getShippingMethodSubmitUrl());
        $this->assertContains('paypal/express/edit', $this->model->getEditUrl());
        $this->assertContains('paypal/express/placeOrder', $this->model->getPlaceOrderUrl());
    }

    public function testBeforeToHtmlWhenQuoteIsVirtual()
    {
        $quote = $this->_getQuoteMock();
        $quote->expects($this->any())->method('getIsVirtual')->will($this->returnValue(true));
        $this->model->setQuote($quote);
        $this->model->toHtml();
        $this->assertEquals(
            $this->model->getPaymentMethodTitle(),
            $quote->getPayment()->getMethodInstance()->getTitle()
        );
        $this->assertFalse($this->model->getShippingRateRequired());
        $this->assertContains('paypal/express/edit', $this->model->getEditUrl());
        $this->assertContains('paypal/express/placeOrder', $this->model->getPlaceOrderUrl());
    }

    /**
     * Create mock of sales quote model
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getQuoteMock()
    {
        $methodInstance = new \Magento\Framework\Object(['title' => 'Payment Method']);
        $payment = $this->getMock('Magento\Sales\Model\Quote\Payment', [], [], '', false);
        $payment->expects($this->any())->method('getMethodInstance')->will($this->returnValue($methodInstance));

        $quote = $this->getMock('Magento\Sales\Model\Quote', [], [], '', false);
        $quote->expects($this->any())->method('getPayment')->will($this->returnValue($payment));
        $quote->setPayment($payment);

        $address = $this->getMockBuilder('Magento\Sales\Model\Quote\Address')
            ->disableOriginalConstructor()
            ->setMethods(['getShippingMethod', 'getGroupedAllShippingRates', '__wakeup'])
            ->getMock();
        $quote->expects($this->any())->method('getShippingAddress')->will($this->returnValue($address));

        return $quote;
    }
}
