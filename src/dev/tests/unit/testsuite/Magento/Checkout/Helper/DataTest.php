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
namespace Magento\Checkout\Helper;

use Magento\TestFramework\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var Data
     */
    private $_helper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_transportBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_translator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected  $_checkoutSession;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected  $_scopeConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected  $_collectionFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected  $_storeManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected  $_context;

    protected function setUp()
    {
        $this->_translator = $this->getMockBuilder('Magento\Framework\Translate\Inline\StateInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_context = $this->getMock('\Magento\Framework\App\Helper\Context', array(), array(), '', false);
        $this->_eventManager = $this->getMockForAbstractClass('\Magento\Framework\Event\ManagerInterface');
        $this->_context->expects($this->once())
            ->method('getEventManager')
            ->will($this->returnValue($this->_eventManager));
        $this->_scopeConfig = $this->getMock('\Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_scopeConfig->expects($this->any())
            ->method('getValue')
            ->will(
                $this->returnValueMap(
                    array(
                        array(
                            'checkout/payment_failed/template',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                            8,
                            'fixture_email_template_payment_failed'
                        ),
                        array(
                            'checkout/payment_failed/receiver',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                            8,
                            'sysadmin'
                        ),
                        array(
                            'trans_email/ident_sysadmin/email',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                            8,
                            'sysadmin@example.com'
                        ),
                        array(
                            'trans_email/ident_sysadmin/name',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                            8,
                            'System Administrator'
                        ),
                        array(
                            'checkout/payment_failed/identity',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                            8,
                            'noreply@example.com'
                        ),
                        array(
                            'carriers/ground/title',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                            null,
                            'Ground Shipping'
                        ),
                        array(
                            'payment/fixture-payment-method/title',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                            null,
                            'Check Money Order'
                        ),
                        array(
                            'checkout/options/onepage_checkout_enabled',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                            null,
                            'One Page Checkout'
                        )
                    )
                )
            );

        $this->_storeManager = $this->getMockForAbstractClass('\Magento\Framework\StoreManagerInterface');

        $this->_checkoutSession = $this->getMock('\Magento\Checkout\Model\Session', array(), array(), '', false);

        $localeDate = $this->getMock(
            '\Magento\Framework\Stdlib\DateTime\TimezoneInterface',
            array(),
            array(),
            '',
            false
        );
        $localeDate->expects($this->any())->method('date')->will($this->returnValue('Oct 02, 2013'));

        $this->_transportBuilder = $this->getMockBuilder('Magento\Framework\Mail\Template\TransportBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $this->priceCurrency = $this->getMockBuilder('Magento\Framework\Pricing\PriceCurrencyInterface')->getMock();

        $this->_helper = new Data(
            $this->_context,
            $this->_scopeConfig,
            $this->_storeManager,
            $this->_checkoutSession,
            $localeDate,
            $this->_transportBuilder,
            $this->_translator,
            $this->priceCurrency
        );
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSendPaymentFailedEmail()
    {
        $shippingAddress = new \Magento\Framework\Object(array('shipping_method' => 'ground_transportation'));
        $billingAddress = new \Magento\Framework\Object(array('street' => 'Fixture St'));

        $this->_transportBuilder->expects(
            $this->once()
        )->method(
            'setTemplateOptions'
        )->with(
            array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => 8)
        )->will(
            $this->returnSelf()
        );

        $this->_transportBuilder->expects(
            $this->once()
        )->method(
            'setTemplateIdentifier'
        )->with(
            'fixture_email_template_payment_failed'
        )->will(
            $this->returnSelf()
        );

        $this->_transportBuilder->expects(
            $this->once()
        )->method(
            'setFrom'
        )->with(
            'noreply@example.com'
        )->will(
            $this->returnSelf()
        );

        $this->_transportBuilder->expects(
            $this->once()
        )->method(
            'addTo'
        )->with(
            'sysadmin@example.com',
            'System Administrator'
        )->will(
            $this->returnSelf()
        );

        $this->_transportBuilder->expects(
            $this->once()
        )->method(
            'setTemplateVars'
        )->with(
            array(
                'reason' => 'test message',
                'checkoutType' => 'onepage',
                'dateAndTime' => 'Oct 02, 2013',
                'customer' => 'John Doe',
                'customerEmail' => 'john.doe@example.com',
                'billingAddress' => $billingAddress,
                'shippingAddress' => $shippingAddress,
                'shippingMethod' => 'Ground Shipping',
                'paymentMethod' => 'Check Money Order',
                'items' => "Product One  x 2  USD 10<br />\nProduct Two  x 3  USD 60<br />\n",
                'total' => 'USD 70'
            )
        )->will(
            $this->returnSelf()
        );

        $this->_transportBuilder->expects($this->once())->method('addBcc')->will($this->returnSelf());
        $this->_transportBuilder->expects(
            $this->once()
        )->method(
            'getTransport'
        )->will(
            $this->returnValue($this->getMock('Magento\Framework\Mail\TransportInterface'))
        );

        $this->_translator->expects($this->at(1))->method('suspend');
        $this->_translator->expects($this->at(1))->method('resume');

        $productOne = $this->getMock('\Magento\Catalog\Model\Product', array(), array(), '', false);
        $productOne->expects($this->once())->method('getName')->will($this->returnValue('Product One'));
        $productOne->expects($this->once())->method('getFinalPrice')->with(2)->will($this->returnValue(10));

        $productTwo = $this->getMock('\Magento\Catalog\Model\Product', array(), array(), '', false);
        $productTwo->expects($this->once())->method('getName')->will($this->returnValue('Product Two'));
        $productTwo->expects($this->once())->method('getFinalPrice')->with(3)->will($this->returnValue(60));

        $quote = new \Magento\Framework\Object(
            array(
                'store_id' => 8,
                'store_currency_code' => 'USD',
                'grand_total' => 70,
                'customer_firstname' => 'John',
                'customer_lastname' => 'Doe',
                'customer_email' => 'john.doe@example.com',
                'billing_address' => $billingAddress,
                'shipping_address' => $shippingAddress,
                'payment' => new \Magento\Framework\Object(array('method' => 'fixture-payment-method')),
                'all_visible_items' => array(
                    new \Magento\Framework\Object(array('product' => $productOne, 'qty' => 2)),
                    new \Magento\Framework\Object(array('product' => $productTwo, 'qty' => 3))
                )
            )
        );
        $this->assertSame($this->_helper, $this->_helper->sendPaymentFailedEmail($quote, 'test message'));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function testGetCheckout()
    {
        $this->assertEquals($this->_checkoutSession, $this->_helper->getCheckout());
    }

    public function testGetQuote()
    {
        $quoteMock = $this->getMock('\Magento\Sales\Model\Quote', array(), array(), '', false);
        $this->_checkoutSession->expects($this->once())->method('getQuote')->will($this->returnValue($quoteMock));
        $this->assertEquals($quoteMock, $this->_helper->getQuote());
    }

    public function testFormatPrice()
    {
        $price = 5.5;
        $quoteMock = $this->getMock('\Magento\Sales\Model\Quote', array(), array(), '', false);
        $storeMock = $this->getMock(
            'Magento\Store\Model\Store',
            array('formatPrice', '__wakeup'),
            array(),
            '',
            false
        );
        $this->_checkoutSession->expects($this->once())->method('getQuote')->will($this->returnValue($quoteMock));
        $quoteMock->expects($this->once())->method('getStore')->will($this->returnValue($storeMock));
        $this->priceCurrency->expects($this->once())->method('format')->will($this->returnValue('5.5'));
        $this->assertEquals('5.5', $this->_helper->formatPrice($price));
    }

    public function testConvertPrice()
    {
        $price = 5.5;
        $this->priceCurrency->expects($this->once())->method('convertAndFormat')->willReturn($price);
        $this->assertEquals(5.5, $this->_helper->convertPrice($price));
    }

    public function testCanOnepageCheckout()
    {
        $this->_scopeConfig->expects($this->once())->method('getValue')->with(
            'checkout/options/onepage_checkout_enabled',
            'store'
        )->will($this->returnValue(true));
        $this->assertTrue($this->_helper->canOnepageCheckout());
    }

    public function testIsContextCheckout()
    {
        $objectManagerHelper = new ObjectManager($this);
        $context = $objectManagerHelper->getObject(
            'Magento\Framework\App\Helper\Context'
        );
        $helper = $objectManagerHelper->getObject(
            'Magento\Checkout\Helper\Data',
            ['context' => $context]
        );
        $context->getRequest()->expects($this->once())->method('getParam')->with('context')->will(
            $this->returnValue('checkout')
        );
        $this->assertTrue($helper->isContextCheckout());
    }

    public function testIsCustomerMustBeLogged()
    {
        $this->_scopeConfig->expects($this->once())->method('isSetFlag')->with(
            'checkout/options/customer_must_be_logged',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )->will($this->returnValue(true));
        $this->assertTrue($this->_helper->isCustomerMustBeLogged());
    }

    public function testGetPriceInclTax()
    {
        $itemMock = $this->getMock('Magento\Framework\Object', array('getPriceInclTax'), array(), '', false);
        $itemMock->expects($this->exactly(2))->method('getPriceInclTax')->will($this->returnValue(5.5));
        $this->assertEquals(5.5, $this->_helper->getPriceInclTax($itemMock));
    }

    public function testGetPriceInclTaxWithoutTax()
    {
        $qty = 1;
        $taxAmount = 1;
        $discountTaxCompensation = 1;
        $rowTotal = 15;
        $roundPrice = 17;
        $expected = 17;
        $storeManager = $this->getMock('\Magento\Framework\StoreManagerInterface', array(), array(), '', false);
        $objectManagerHelper = new ObjectManager($this);
        $helper = $objectManagerHelper->getObject(
            '\Magento\Checkout\Helper\Data',
            [
                'storeManager' => $storeManager,
                'priceCurrency' => $this->priceCurrency,
            ]
        );
        $itemMock = $this->getMock(
            'Magento\Framework\Object',
            array('getPriceInclTax', 'getQty', 'getTaxAmount', 'getDiscountTaxCompensation', 'getRowTotal'),
            array(),
            '',
            false
        );
        $itemMock->expects($this->once())->method('getPriceInclTax')->will($this->returnValue(false));
        $itemMock->expects($this->exactly(2))->method('getQty')->will($this->returnValue($qty));
        $itemMock->expects($this->never())->method('getQtyOrdered');
        $itemMock->expects($this->once())->method('getTaxAmount')->will($this->returnValue($taxAmount));
        $itemMock->expects($this->once())
            ->method('getDiscountTaxCompensation')->will($this->returnValue($discountTaxCompensation));
        $itemMock->expects($this->once())->method('getRowTotal')->will($this->returnValue($rowTotal));
        $this->priceCurrency->expects($this->once())->method('round')->with($roundPrice)->willReturn($roundPrice);
        $this->assertEquals($expected, $helper->getPriceInclTax($itemMock));
    }

    public function testGetSubtotalInclTax()
    {
        $rowTotalInclTax = 5.5;
        $expected = 5.5;
        $itemMock = $this->getMock('Magento\Framework\Object', array('getRowTotalInclTax'), array(), '', false);
        $itemMock->expects($this->exactly(2))->method('getRowTotalInclTax')->will($this->returnValue($rowTotalInclTax));
        $this->assertEquals($expected, $this->_helper->getSubtotalInclTax($itemMock));
    }

    public function testGetSubtotalInclTaxNegative()
    {
        $taxAmount = 1;
        $discountTaxCompensation = 1;
        $rowTotal = 15;
        $expected = 17;
        $itemMock = $this->getMock(
            'Magento\Framework\Object',
            array('getRowTotalInclTax', 'getTaxAmount', 'getDiscountTaxCompensation', 'getRowTotal'),
            array(),
            '',
            false
        );
        $itemMock->expects($this->once())->method('getRowTotalInclTax')->will($this->returnValue(false));
        $itemMock->expects($this->once())->method('getTaxAmount')->will($this->returnValue($taxAmount));
        $itemMock->expects($this->once())
            ->method('getDiscountTaxCompensation')->will($this->returnValue($discountTaxCompensation));
        $itemMock->expects($this->once())->method('getRowTotal')->will($this->returnValue($rowTotal));
        $this->assertEquals($expected, $this->_helper->getSubtotalInclTax($itemMock));
    }

    public function testGetBasePriceInclTaxWithoutQty()
    {
        $storeManager = $this->getMock('\Magento\Framework\StoreManagerInterface', array(), array(), '', false);
        $objectManagerHelper = new ObjectManager($this);
        $helper = $objectManagerHelper->getObject(
            '\Magento\Checkout\Helper\Data',
            [
                'storeManager' => $storeManager,
                'priceCurrency' => $this->priceCurrency,
            ]
        );
        $itemMock = $this->getMock('Magento\Framework\Object', array('getQty'), array(), '', false);
        $itemMock->expects($this->once())->method('getQty');
        $this->priceCurrency->expects($this->once())->method('round');
        $helper->getPriceInclTax($itemMock);
    }

    public function testGetBasePriceInclTax()
    {
        $storeManager = $this->getMock('\Magento\Framework\StoreManagerInterface', array(), array(), '', false);
        $objectManagerHelper = new ObjectManager($this);
        $helper = $objectManagerHelper->getObject(
            '\Magento\Checkout\Helper\Data',
            [
                'storeManager' => $storeManager,
                'priceCurrency' => $this->priceCurrency,
            ]
        );
        $itemMock = $this->getMock('Magento\Framework\Object', array('getQty', 'getQtyOrdered'), array(), '', false);
        $itemMock->expects($this->once())->method('getQty')->will($this->returnValue(false));
        $itemMock->expects($this->exactly(2))->method('getQtyOrdered')->will($this->returnValue(5.5));
        $this->priceCurrency->expects($this->once())->method('round');
        $helper->getBasePriceInclTax($itemMock);
    }

    public function testGetBaseSubtotalInclTax()
    {
        $itemMock = $this->getMock(
            'Magento\Framework\Object',
            array('getBaseTaxAmount', 'getBaseDiscountTaxCompensation', 'getBaseRowTotal'),
            array(),
            '',
            false
        );
        $itemMock->expects($this->once())->method('getBaseTaxAmount');
        $itemMock->expects($this->once())->method('getBaseDiscountTaxCompensation');
        $itemMock->expects($this->once())->method('getBaseRowTotal');
        $this->_helper->getBaseSubtotalInclTax($itemMock);
    }

    public function testIsAllowedGuestCheckoutWithoutStore()
    {
        $quoteMock = $this->getMock('\Magento\Sales\Model\Quote', array(), array(), '', false);
        $store = null;
        $quoteMock->expects($this->once())->method('getStoreId')->will($this->returnValue(1));
        $this->_scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->will($this->returnValue(true));
        $this->assertTrue($this->_helper->isAllowedGuestCheckout($quoteMock, $store));
    }
}
