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

namespace Magento\Sales\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Helper\Data
     */
    protected $helper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\App\Config\ScopeConfigInterface
     */
    protected $scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Sales\Model\Store
     */
    protected $storeMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $contextMock = $this->getMockBuilder('Magento\Framework\App\Helper\Context')
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfigMock = $this->getMockBuilder('Magento\Framework\App\Config')
            ->setMethods(['isSetFlag'])
            ->disableOriginalConstructor()
            ->getMock();

        $storeManagerMock = $this->getMockBuilder('Magento\Framework\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $appStateMock = $this->getMockBuilder('Magento\Framework\App\State')
            ->disableOriginalConstructor()
            ->getMock();

        $pricingCurrencyMock = $this->getMockBuilder('Magento\Framework\Pricing\PriceCurrencyInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->helper = new \Magento\Sales\Helper\Data(
            $contextMock,
            $this->scopeConfigMock,
            $storeManagerMock,
            $appStateMock,
            $pricingCurrencyMock
        );

        $this->storeMock = $this->getMockBuilder('Magento\Sales\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();

        $this->quoteMock = $this->getMockBuilder('Magento\Sales\Model\Quote')
            ->setMethods(['getHasError', 'setHasError', 'addMessage', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testCheckQuoteAmountExistingError()
    {
        $this->quoteMock->expects($this->once())
            ->method('getHasError')
            ->will($this->returnValue(true));

        $this->quoteMock->expects($this->never())
            ->method('setHasError');

        $this->quoteMock->expects($this->never())
            ->method('addMessage');

        $this->assertSame(
            $this->helper,
            $this->helper->checkQuoteAmount($this->quoteMock, Data::MAXIMUM_AVAILABLE_NUMBER + 1)
        );
    }

    public function testCheckQuoteAmountAmountLessThanAvailable()
    {
        $this->quoteMock->expects($this->once())
            ->method('getHasError')
            ->will($this->returnValue(false));

        $this->quoteMock->expects($this->never())
            ->method('setHasError');

        $this->quoteMock->expects($this->never())
            ->method('addMessage');

        $this->assertSame(
            $this->helper,
            $this->helper->checkQuoteAmount($this->quoteMock, Data::MAXIMUM_AVAILABLE_NUMBER - 1)
        );
    }

    public function testCheckQuoteAmountAmountGreaterThanAvailable()
    {
        $this->quoteMock->expects($this->once())
            ->method('getHasError')
            ->will($this->returnValue(false));

        $this->quoteMock->expects($this->once())
            ->method('setHasError')
            ->with(true);

        $this->quoteMock->expects($this->once())
            ->method('addMessage')
            ->with(__('This item price or quantity is not valid for checkout.'));

        $this->assertSame(
            $this->helper,
            $this->helper->checkQuoteAmount($this->quoteMock, Data::MAXIMUM_AVAILABLE_NUMBER + 1)
        );
    }

    /**
     * @dataProvider getScopeConfigValue
     */
    public function testCanSendNewOrderConfirmationEmail($scopeConfigValue)
    {
        $this->setupScopeConfigIsSetFlag(
            \Magento\Sales\Model\Order\Email\Container\OrderIdentity::XML_PATH_EMAIL_ENABLED,
            $scopeConfigValue
        );

        $this->assertEquals($scopeConfigValue, $this->helper->canSendNewOrderConfirmationEmail($this->storeMock));
    }

    /**
     * @dataProvider getScopeConfigValue
     * @return void
     */
    public function testCanSendNewOrderEmail($scopeConfigValue)
    {
        $this->setupScopeConfigIsSetFlag(
            \Magento\Sales\Model\Order\Email\Container\OrderIdentity::XML_PATH_EMAIL_ENABLED,
            $scopeConfigValue
        );

        $this->assertEquals($scopeConfigValue, $this->helper->canSendNewOrderEmail($this->storeMock));
    }

    /**
     * @dataProvider getScopeConfigValue
     * @return void
     */
    public function testCanSendOrderCommentEmail($scopeConfigValue)
    {
        $this->setupScopeConfigIsSetFlag(
            \Magento\Sales\Model\Order\Email\Container\OrderCommentIdentity::XML_PATH_EMAIL_ENABLED,
            $scopeConfigValue
        );

        $this->assertEquals($scopeConfigValue, $this->helper->canSendOrderCommentEmail($this->storeMock));
    }

    /**
     * @dataProvider getScopeConfigValue
     * @return void
     */
    public function testCanSendNewShipmentEmail($scopeConfigValue)
    {
        $this->setupScopeConfigIsSetFlag(
            \Magento\Sales\Model\Order\Email\Container\ShipmentIdentity::XML_PATH_EMAIL_ENABLED,
            $scopeConfigValue
        );

        $this->assertEquals($scopeConfigValue, $this->helper->canSendNewShipmentEmail($this->storeMock));
    }

    /**
     * @dataProvider getScopeConfigValue
     * @return void
     */
    public function testCanSendShipmentCommentEmail($scopeConfigValue)
    {
        $this->setupScopeConfigIsSetFlag(
            \Magento\Sales\Model\Order\Email\Container\ShipmentCommentIdentity::XML_PATH_EMAIL_ENABLED,
            $scopeConfigValue
        );

        $this->assertEquals($scopeConfigValue, $this->helper->canSendShipmentCommentEmail($this->storeMock));
    }

    /**
     * @dataProvider getScopeConfigValue
     */
    public function testCanSendNewInvoiceEmail($scopeConfigValue)
    {
        $this->setupScopeConfigIsSetFlag(
            \Magento\Sales\Model\Order\Email\Container\InvoiceIdentity::XML_PATH_EMAIL_ENABLED,
            $scopeConfigValue
        );

        $this->assertEquals($scopeConfigValue, $this->helper->canSendNewInvoiceEmail($this->storeMock));
    }

    /**
     * @dataProvider getScopeConfigValue
     */
    public function testCanSendInvoiceCommentEmail($scopeConfigValue)
    {
        $this->setupScopeConfigIsSetFlag(
            \Magento\Sales\Model\Order\Email\Container\InvoiceCommentIdentity::XML_PATH_EMAIL_ENABLED,
            $scopeConfigValue
        );

        $this->assertEquals($scopeConfigValue, $this->helper->canSendInvoiceCommentEmail($this->storeMock));
    }

    /**
     * @dataProvider getScopeConfigValue
     * @return void
     */
    public function testCanSendNewCreditmemoEmail($scopeConfigValue)
    {
        $this->setupScopeConfigIsSetFlag(
            \Magento\Sales\Model\Order\Email\Container\CreditmemoIdentity::XML_PATH_EMAIL_ENABLED,
            $scopeConfigValue
        );

        $this->assertEquals($scopeConfigValue, $this->helper->canSendNewCreditmemoEmail($this->storeMock));
    }

    /**
     * @dataProvider getScopeConfigValue
     * @return void
     */
    public function testCanSendCreditmemoCommentEmail($scopeConfigValue)
    {
        $this->setupScopeConfigIsSetFlag(
            \Magento\Sales\Model\Order\Email\Container\CreditmemoCommentIdentity::XML_PATH_EMAIL_ENABLED,
            $scopeConfigValue
        );

        $this->assertEquals($scopeConfigValue, $this->helper->canSendCreditmemoCommentEmail($this->storeMock));
    }

    /**
     * Sets up the scope config mock which will return a specified value for a config flag.
     *
     * @param string $flagName
     * @param bool $returnValue
     * @return void
     */
    protected function setupScopeConfigIsSetFlag($flagName, $returnValue)
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(
                $flagName,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->storeMock
            )
            ->will($this->returnValue($returnValue));
    }

    /**
     * @return array
     */
    public function getScopeConfigValue()
    {
        return [
            [true],
            [false]
        ];
    }

}
