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
namespace Magento\Sales\Model\AdminOrder;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @magentoAppArea adminhtml
 */
class CreateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\AdminOrder\Create
     */
    protected $_model;

    /** @var \Magento\Framework\Message\ManagerInterface */
    protected $_messageManager;

    protected function setUp()
    {
        parent::setUp();
        $this->_messageManager = Bootstrap::getObjectManager()->get('Magento\Framework\Message\ManagerInterface');
        $this->_model = Bootstrap::getObjectManager()->create(
            'Magento\Sales\Model\AdminOrder\Create',
            array('messageManager' => $this->_messageManager)
        );
    }

    /**
     * @magentoDataFixture Magento/Downloadable/_files/product_downloadable.php
     * @magentoDataFixture Magento/Downloadable/_files/order_with_downloadable_product.php
     */
    public function testInitFromOrderShippingAddressSameAsBillingWhenEmpty()
    {
        /** @var $order \Magento\Sales\Model\Order */
        $order = Bootstrap::getObjectManager()->create('Magento\Sales\Model\Order');
        $order->loadByIncrementId('100000001');
        $this->assertFalse($order->getShippingAddress());

        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get('Magento\Framework\Registry')->unregister('rule_data');
        $this->_model->initFromOrder($order);

        $this->assertFalse($order->getShippingAddress());
    }

    /**
     * @magentoDataFixture Magento/Downloadable/_files/product_downloadable.php
     * @magentoDataFixture Magento/Downloadable/_files/order_with_downloadable_product.php
     * @magentoDataFixture Magento/Sales/_files/order_shipping_address_same_as_billing.php
     */
    public function testInitFromOrderShippingAddressSameAsBillingWhenSame()
    {
        /** @var $order \Magento\Sales\Model\Order */
        $order = Bootstrap::getObjectManager()->create('Magento\Sales\Model\Order');
        $order->loadByIncrementId('100000001');

        $this->assertNull($order->getShippingAddress()->getSameAsBilling());

        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get('Magento\Framework\Registry')->unregister('rule_data');
        $this->_model->initFromOrder($order);

        $this->assertTrue($order->getShippingAddress()->getSameAsBilling());
    }

    /**
     * @magentoDataFixture Magento/Downloadable/_files/product_downloadable.php
     * @magentoDataFixture Magento/Downloadable/_files/order_with_downloadable_product.php
     * @magentoDataFixture Magento/Sales/_files/order_shipping_address_different_to_billing.php
     */
    public function testInitFromOrderShippingAddressSameAsBillingWhenDifferent()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = Bootstrap::getObjectManager();

        /** @var $order \Magento\Sales\Model\Order */
        $order = $objectManager->create('Magento\Sales\Model\Order');
        $order->loadByIncrementId('100000001');

        $this->assertNull($order->getShippingAddress()->getSameAsBilling());

        $objectManager->get('Magento\Framework\Registry')->unregister('rule_data');
        $this->_model->initFromOrder($order);

        $this->assertFalse($order->getShippingAddress()->getSameAsBilling());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_paid_with_payflowpro.php
     */
    public function testInitFromOrderCcInformationDeleted()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = Bootstrap::getObjectManager();

        /** @var $order \Magento\Sales\Model\Order */
        $order = $objectManager->create('Magento\Sales\Model\Order');
        $order->loadByIncrementId('100000001');

        $payment = $order->getPayment();
        $this->assertEquals('5', $payment->getCcExpMonth());
        $this->assertEquals('2016', $payment->getCcExpYear());
        $this->assertEquals('AE', $payment->getCcType());
        $this->assertEquals('0005', $payment->getCcLast4());

        $objectManager->get('Magento\Framework\Registry')->unregister('rule_data');
        $payment = $this->_model->initFromOrder($order)->getQuote()->getPayment();

        $this->assertNull($payment->getCcExpMonth());
        $this->assertNull($payment->getCcExpYear());
        $this->assertNull($payment->getCcType());
        $this->assertNull($payment->getCcLast4());
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetCustomerWishlistNoCustomerId()
    {
        /** @var \Magento\Backend\Model\Session\Quote $session */
        $session = Bootstrap::getObjectManager()->create('Magento\Backend\Model\Session\Quote');
        $session->setCustomerId(null);
        $this->assertFalse(
            $this->_model->getCustomerWishlist(true),
            'If customer ID is not set to session, false is expected to be returned.'
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testGetCustomerWishlist()
    {
        $customerIdFromFixture = 1;
        $productIdFromFixture = 1;
        /** @var \Magento\Backend\Model\Session\Quote $session */
        $session = Bootstrap::getObjectManager()->create('Magento\Backend\Model\Session\Quote');
        $session->setCustomerId($customerIdFromFixture);

        /** Test new wishlist creation for the customer specified above */
        /** @var \Magento\Wishlist\Model\Wishlist $wishlist */
        $wishlist = $this->_model->getCustomerWishlist(true);
        $this->assertInstanceOf(
            'Magento\Wishlist\Model\Wishlist',
            $wishlist,
            'New wishlist is expected to be created if existing customer does not have one yet.'
        );
        $this->assertEquals(0, $wishlist->getItemsCount(), 'New wishlist must be empty just after creation.');

        /** Add new item to wishlist and try to get it using getCustomerWishlist once again */
        $wishlist->addNewItem($productIdFromFixture)->save();
        $updatedWishlist = $this->_model->getCustomerWishlist(true);
        $this->assertEquals(
            1,
            $updatedWishlist->getItemsCount(),
            'Wishlist must contain a product which was added to it earlier.'
        );

        /** Try to load wishlist from cache in the class after it is deleted from DB */
        $wishlist->delete();
        $this->assertSame(
            $updatedWishlist,
            $this->_model->getCustomerWishlist(false),
            'Wishlist cached in class variable is expected to be returned.'
        );
        $this->assertNotSame(
            $updatedWishlist,
            $this->_model->getCustomerWishlist(true),
            'New wishlist is expected to be created when cache is forced to be refreshed.'
        );
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testSetBillingAddress()
    {
        $addressData = $this->_getValidAddressData();
        /** Validate data before creating address object */
        $this->_model->setIsValidate(true)->setBillingAddress($addressData);
        $this->assertInstanceOf(
            'Magento\Sales\Model\Quote\Address',
            $this->_model->getBillingAddress(),
            'Billing address object was not created.'
        );

        $expectedAddressData = array_merge(
            $addressData,
            array(
                'address_type' => 'billing',
                'quote_id' => $this->_model->getQuote()->getId(),
                'street' => "Line1\nLine2",
                'save_in_address_book' => 0
            )
        );
        $this->assertEquals(
            $expectedAddressData,
            $this->_model->getBillingAddress()->getData(),
            'Created billing address is invalid.'
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testSetBillingAddressValidationErrors()
    {
        $customerIdFromFixture = 1;
        /** @var \Magento\Backend\Model\Session\Quote $session */
        $session = Bootstrap::getObjectManager()->create('Magento\Backend\Model\Session\Quote');
        $session->setCustomerId($customerIdFromFixture);
        $invalidAddressData = array_merge($this->_getValidAddressData(), array('firstname' => '', 'lastname' => ''));
        /**
         * Note that validation errors are collected during setBillingAddress() call in the internal class variable,
         * but they are not set to message manager at this step.
         * They are set to message manager only during createOrder() call.
         */
        $this->_model->setIsValidate(true)->setBillingAddress($invalidAddressData);
        try {
            $this->_model->createOrder();
            $this->fail('Validation errors are expected to lead to exception during createOrder() call.');
        } catch (\Magento\Framework\Model\Exception $e) {
            /** createOrder is expected to throw exception with empty message when validation error occurs */
        }
        $errorMessages = array();
        /** @var $validationError \Magento\Framework\Message\Error */
        foreach ($this->_messageManager->getMessages()->getItems() as $validationError) {
            $errorMessages[] = $validationError->getText();
        }
        $this->assertTrue(
            in_array('Billing Address: "First Name" is a required value.', $errorMessages),
            'Expected validation message is absent.'
        );
        $this->assertTrue(
            in_array('Billing Address: "Last Name" is a required value.', $errorMessages),
            'Expected validation message is absent.'
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testCreateOrderNewCustomerDifferentAddresses()
    {
        $productIdFromFixture = 1;
        $shippingMethod = 'freeshipping_freeshipping';
        $paymentMethod = 'checkmo';
        $shippingAddressAsBilling = 0;
        $customerEmail = 'new_customer@example.com';
        $firstNameForShippingAddress = 'FirstNameForShipping';
        $orderData = array(
            'currency' => 'USD',
            'account' => array('group_id' => '1', 'email' => $customerEmail),
            'billing_address' => array_merge($this->_getValidAddressData(), array('save_in_address_book' => '1')),
            'shipping_address' => array_merge(
                $this->_getValidAddressData(),
                array('save_in_address_book' => '1', 'firstname' => $firstNameForShippingAddress)
            ),
            'shipping_method' => $shippingMethod,
            'comment' => array('customer_note' => ''),
            'send_confirmation' => true
        );
        $paymentData = array('method' => $paymentMethod);

        $this->_preparePreconditionsForCreateOrder(
            $productIdFromFixture,
            $customerEmail,
            $shippingMethod,
            $shippingAddressAsBilling,
            $paymentData,
            $orderData,
            $paymentMethod
        );
        $order = $this->_model->createOrder();
        $this->_verifyCreatedOrder($order, $shippingMethod);
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = Bootstrap::getObjectManager()->create('Magento\Customer\Model\Customer');
        $customer->load($order->getCustomerId());
        $this->assertEquals(
            $firstNameForShippingAddress,
            $customer->getPrimaryShippingAddress()->getFirstname(),
            'Shipping address is saved incorrectly.'
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testCreateOrderNewCustomer()
    {
        $productIdFromFixture = 1;
        $shippingMethod = 'freeshipping_freeshipping';
        $paymentMethod = 'checkmo';
        $shippingAddressAsBilling = 1;
        $customerEmail = 'new_customer@example.com';
        $orderData = array(
            'currency' => 'USD',
            'account' => array('group_id' => '1', 'email' => $customerEmail),
            'billing_address' => array_merge($this->_getValidAddressData(), array('save_in_address_book' => '1')),
            'shipping_method' => $shippingMethod,
            'comment' => array('customer_note' => ''),
            'send_confirmation' => false
        );
        $paymentData = array('method' => $paymentMethod);

        $this->_preparePreconditionsForCreateOrder(
            $productIdFromFixture,
            $customerEmail,
            $shippingMethod,
            $shippingAddressAsBilling,
            $paymentData,
            $orderData,
            $paymentMethod
        );
        $order = $this->_model->createOrder();
        $this->_verifyCreatedOrder($order, $shippingMethod);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testCreateOrderExistingCustomerDifferentAddresses()
    {
        $productIdFromFixture = 1;
        $customerIdFromFixture = 1;
        $customerEmailFromFixture = 'customer@example.com';
        $shippingMethod = 'freeshipping_freeshipping';
        $paymentMethod = 'checkmo';
        $shippingAddressAsBilling = 0;
        $firstNameForShippingAddress = 'FirstNameForShipping';
        $orderData = array(
            'currency' => 'USD',
            'billing_address' => array_merge($this->_getValidAddressData(), array('save_in_address_book' => '1')),
            'shipping_address' => array_merge(
                $this->_getValidAddressData(),
                array('save_in_address_book' => '1', 'firstname' => $firstNameForShippingAddress)
            ),
            'shipping_method' => $shippingMethod,
            'comment' => array('customer_note' => ''),
            'send_confirmation' => false
        );
        $paymentData = array('method' => $paymentMethod);

        $this->_preparePreconditionsForCreateOrder(
            $productIdFromFixture,
            $customerEmailFromFixture,
            $shippingMethod,
            $shippingAddressAsBilling,
            $paymentData,
            $orderData,
            $paymentMethod,
            $customerIdFromFixture
        );
        $order = $this->_model->createOrder();
        $this->_verifyCreatedOrder($order, $shippingMethod);
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = Bootstrap::getObjectManager()->create('Magento\Customer\Model\Customer');
        $customer->load($order->getCustomerId());
        $this->assertEquals(
            $firstNameForShippingAddress,
            $customer->getDefaultShippingAddress()->getFirstname(),
            'Shipping address is saved incorrectly.'
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testCreateOrderExistingCustomer()
    {
        $productIdFromFixture = 1;
        $customerIdFromFixture = 1;
        $customerEmailFromFixture = 'customer@example.com';
        $shippingMethod = 'freeshipping_freeshipping';
        $paymentMethod = 'checkmo';
        $shippingAddressAsBilling = 1;
        $orderData = array(
            'currency' => 'USD',
            'billing_address' => array_merge($this->_getValidAddressData(), array('save_in_address_book' => '1')),
            'shipping_method' => $shippingMethod,
            'comment' => array('customer_note' => ''),
            'send_confirmation' => false
        );
        $paymentData = array('method' => $paymentMethod);

        $this->_preparePreconditionsForCreateOrder(
            $productIdFromFixture,
            $customerEmailFromFixture,
            $shippingMethod,
            $shippingAddressAsBilling,
            $paymentData,
            $orderData,
            $paymentMethod,
            $customerIdFromFixture
        );
        $order = $this->_model->createOrder();
        $this->_verifyCreatedOrder($order, $shippingMethod);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetCustomerCartExistingCart()
    {
        $fixtureCustomerId = 1;

        /** Preconditions */
        /** @var \Magento\Backend\Model\Session\Quote $session */
        $session = Bootstrap::getObjectManager()->create('Magento\Backend\Model\Session\Quote');
        $session->setCustomerId($fixtureCustomerId);
        /** @var $quoteFixture \Magento\Sales\Model\Quote */
        $quoteFixture = Bootstrap::getObjectManager()->create('Magento\Sales\Model\Quote');
        $quoteFixture->load('test01', 'reserved_order_id');
        $quoteFixture->setCustomerIsGuest(false)->setCustomerId($fixtureCustomerId)->save();

        /** SUT execution */
        $customerQuote = $this->_model->getCustomerCart();
        $this->assertEquals($quoteFixture->getId(), $customerQuote->getId(), 'Quote ID is invalid.');

        /** Try to load quote once again to ensure that caching works correctly */
        $customerQuoteFromCache = $this->_model->getCustomerCart();
        $this->assertSame($customerQuote, $customerQuoteFromCache, 'Customer quote caching does not work correctly.');
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetCustomerCartNewCart()
    {
        $customerIdFromFixture = 1;
        $customerEmailFromFixture = 'customer@example.com';

        /** Preconditions */
        /** @var \Magento\Backend\Model\Session\Quote $session */
        $session = Bootstrap::getObjectManager()->create('Magento\Backend\Model\Session\Quote');
        $session->setCustomerId($customerIdFromFixture);

        /** SUT execution */
        $customerQuote = $this->_model->getCustomerCart();
        $this->assertNotEmpty($customerQuote->getId(), 'Quote ID is invalid.');
        $this->assertEquals(
            $customerEmailFromFixture,
            $customerQuote->getCustomerEmail(),
            'Customer data is preserved incorrectly in a newly quote.'
        );
    }

    /**
     * Prepare preconditions for createOrder method invocation.
     *
     * @param int $productIdFromFixture
     * @param string $customerEmail
     * @param string $shippingMethod
     * @param int $shippingAddressAsBilling
     * @param array $paymentData
     * @param array $orderData
     * @param string $paymentMethod
     * @param int|null $customerIdFromFixture
     */
    protected function _preparePreconditionsForCreateOrder(
        $productIdFromFixture,
        $customerEmail,
        $shippingMethod,
        $shippingAddressAsBilling,
        $paymentData,
        $orderData,
        $paymentMethod,
        $customerIdFromFixture = null
    ) {
        /** Disable product options */
        /** @var \Magento\Catalog\Model\Product $product */
        $product = Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
        $product->load($productIdFromFixture)->setHasOptions(false)->save();

        /** Set current customer */
        /** @var \Magento\Backend\Model\Session\Quote $session */
        $session = Bootstrap::getObjectManager()->get('Magento\Backend\Model\Session\Quote');
        if (!is_null($customerIdFromFixture)) {
            $session->setCustomerId($customerIdFromFixture);

            /** Unset fake IDs for default billing and shipping customer addresses */
            /** @var \Magento\Customer\Model\Customer $customer */
            $customer = Bootstrap::getObjectManager()->create('Magento\Customer\Model\Customer');
            $customer->load($customerIdFromFixture)->setDefaultBilling(null)->setDefaultShipping(null)->save();
        } else {
            /**
             * Customer ID must be set to session to pass \Magento\Sales\Model\AdminOrder\Create::_validate()
             * This code emulates order placement via admin panel.
             */
            $session->setCustomerId(0);
        }

        /** Emulate availability of shipping method (all are disabled by default) */
        /** @var $rate \Magento\Sales\Model\Quote\Address\Rate */
        $rate = Bootstrap::getObjectManager()->create('Magento\Sales\Model\Quote\Address\Rate');
        $rate->setCode($shippingMethod);
        $this->_model->getQuote()->getShippingAddress()->addShippingRate($rate);

        $this->_model->setShippingAsBilling($shippingAddressAsBilling);
        $this->_model->addProduct($productIdFromFixture, array('qty' => 1));
        $this->_model->setPaymentData($paymentData);
        $this->_model->setIsValidate(true)->importPostData($orderData);

        /** Check preconditions */

        $this->assertEquals(
            0,
            $this->_messageManager->getMessages()->getCount(),
            "Precondition failed: Errors occurred before SUT execution."
        );
        /** Selectively check quote data */
        $createOrderData = $this->_model->getData();
        $this->assertEquals(
            $shippingMethod,
            $createOrderData['shipping_method'],
            'Precondition failed: Shipping method specified in create order model is invalid'
        );
        $this->assertEquals(
            'FirstName',
            $createOrderData['billing_address']['firstname'],
            'Precondition failed: Address data is invalid in create order model'
        );
        $this->assertEquals(
            'Simple Product',
            $this->_model->getQuote()->getAllItems()[0]->getData('name'),
            'Precondition failed: Quote items data is invalid in create order model'
        );
        $this->assertEquals(
            $customerEmail,
            $this->_model->getQuote()->getCustomer()->getData('email'),
            'Precondition failed: Customer data is invalid in create order model'
        );
        $this->assertEquals(
            $paymentMethod,
            $this->_model->getQuote()->getPayment()->getData('method'),
            'Precondition failed: Payment method data is invalid in create order model'
        );
    }

    /**
     * Ensure that order is created correctly via createOrder().
     *
     * @param \Magento\Sales\Model\Order $order
     * @param string $shippingMethod
     */
    protected function _verifyCreatedOrder($order, $shippingMethod)
    {
        /** Selectively check order data */
        $orderData = $order->getData();
        $this->assertNotEmpty($orderData['increment_id'], 'Order increment ID is empty.');
        $this->assertEquals($this->_model->getQuote()->getId(), $orderData['quote_id'], 'Quote ID is invalid.');
        $this->assertEquals(
            $this->_model->getQuote()->getCustomer()->getEmail(),
            $orderData['customer_email'],
            'Customer email is invalid.'
        );
        $this->assertEquals(
            $this->_model->getQuote()->getCustomer()->getFirstname(),
            $orderData['customer_firstname'],
            'Customer first name is invalid.'
        );
        $this->assertEquals($shippingMethod, $orderData['shipping_method'], 'Customer first name is invalid.');
    }

    /**
     * Get valid address data for address creation.
     *
     * @return array
     */
    protected function _getValidAddressData()
    {
        return array(
            'prefix' => 'prefix',
            'firstname' => 'FirstName',
            'middlename' => 'MiddleName',
            'lastname' => 'LastName',
            'suffix' => 'suffix',
            'company' => 'Company Name',
            'street' => array(0 => 'Line1', 1 => 'Line2'),
            'city' => 'City',
            'country_id' => 'US',
            'region' => '',
            'region_id' => '1',
            'postcode' => '76868',
            'telephone' => '+8709273498729384',
            'fax' => '',
            'vat_id' => ''
        );
    }
}
