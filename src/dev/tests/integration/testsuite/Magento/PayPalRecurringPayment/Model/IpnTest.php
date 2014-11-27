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
namespace Magento\PayPalRecurringPayment\Model;

/**
 * @magentoAppArea frontend
 */
class IpnTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * Test processIpnRequest() currency check for recurring payment
     *
     * @param string $currencyCode
     * @dataProvider currencyProvider
     * @magentoDataFixture Magento/PayPalRecurringPayment/_files/recurring_payment.php
     * @magentoConfigFixture current_store payment/paypal_direct/active 1
     * @magentoConfigFixture current_store payment/paypal_express/active 1
     * @magentoConfigFixture current_store paypal/general/merchant_country US
     * @magentoConfigFixture current_store sales_email/order/enabled 0
     */
    public function testProcessIpnRequestRecurringCurrency($currencyCode)
    {
        $ipnData = require __DIR__ . '/../_files/ipn_recurring_payment.php';
        $ipnData['mc_currency'] = $currencyCode;

        /** @var  $ipnFactory \Magento\Paypal\Model\IpnFactory */
        $ipnFactory = $this->_objectManager->create(
            'Magento\Paypal\Model\IpnFactory',
            array('mapping' => array('recurring_payment' => 'Magento\PayPalRecurringPayment\Model\Ipn'))
        );

        $model = $ipnFactory->create(array('data' => $ipnData, 'curlFactory' => $this->_createMockedHttpAdapter()));
        $model->processIpnRequest();

        $recurringPayment = $this->_objectManager->create('Magento\RecurringPayment\Model\Payment');
        $recurringPayment->loadByInternalReferenceId('5-33949e201adc4b03fbbceafccba893ce');
        $orderIds = $recurringPayment->getChildOrderIds();
        $this->assertEquals(1, count($orderIds));
        $order = $this->_objectManager->create('Magento\Sales\Model\Order');
        $order->load($orderIds[0]);
        $this->_assertOrder($order, $currencyCode);
    }

    /**
     * Perform order state and status assertions depending on currency code
     *
     * @param \Magento\Sales\Model\Order $order
     * @param string $currencyCode
     */
    protected function _assertOrder($order, $currencyCode)
    {
        if ($currencyCode == 'USD') {
            $this->assertEquals('complete', $order->getState());
            $this->assertEquals('complete', $order->getStatus());
        } else {
            $this->assertEquals('payment_review', $order->getState());
            $this->assertEquals('fraud', $order->getStatus());
        }
    }

    /**
     * Data provider for currency check tests
     *
     * @static
     * @return array
     */
    public static function currencyProvider()
    {
        return array(array('USD'), array('EUR'));
    }

    /**
     * Mocked HTTP adapter to get VERIFIED PayPal IPN postback result
     *
     * @return \Magento\Framework\HTTP\Adapter\Curl
     */
    protected function _createMockedHttpAdapter()
    {
        $factory = $this->getMock('Magento\Framework\HTTP\Adapter\CurlFactory', array('create'), array(), '', false);
        $adapter = $this->getMock('Magento\Framework\HTTP\Adapter\Curl', array('read', 'write'), array(), '', false);

        $adapter->expects($this->once())->method('read')->with()->will($this->returnValue("\nVERIFIED"));

        $adapter->expects($this->once())->method('write');

        $factory->expects($this->once())->method('create')->with()->will($this->returnValue($adapter));
        return $factory;
    }
}
