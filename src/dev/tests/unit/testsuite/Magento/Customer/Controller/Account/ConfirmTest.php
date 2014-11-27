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

namespace Magento\Customer\Controller\Account;

use Magento\Customer\Helper\Address;
use Magento\Customer\Model\Url;
use Magento\Store\Model\ScopeInterface;

class ConfirmTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Controller\Account
     */
    protected $model;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\App\ViewInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewMock;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirectMock;

    /**
     * @var \Magento\Framework\UrlFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlFactoryMock;

    /**
     * @var \Magento\Framework\Url|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlMock;

    /**
     * @var \Magento\Customer\Service\V1\CustomerAccountServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerAccountServiceMock;

    /**
     * @var \Magento\Customer\Service\V1\Data\Customer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerServiceDataMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Customer\Helper\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressHelperMock;

    /**
     * @var \Magento\Store\Model\StoreManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Framework\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    protected function setUp()
    {
        $this->customerSessionMock = $this->getMock('\Magento\Customer\Model\Session', [], [], '', false);
        $this->requestMock = $this->getMock('Magento\Framework\App\RequestInterface', [], [], '', false);
        $this->responseMock = $this->getMock(
            'Magento\Framework\App\Response\Http', ['setRedirect', '__wakeup'], [], '', false
        );
        $this->viewMock = $this->getMock('Magento\Framework\App\ViewInterface');
        $this->redirectMock = $this->getMock('Magento\Framework\App\Response\RedirectInterface');

        $this->urlMock = $this->getMock('Magento\Framework\Url', [], [], '', false);
        $this->urlFactoryMock = $this->getMock('Magento\Framework\UrlFactory', [], [], '', false);
        $this->urlFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->urlMock));

        $this->customerAccountServiceMock =
            $this->getMockForAbstractClass('Magento\Customer\Service\V1\CustomerAccountServiceInterface');
        $this->customerServiceDataMock = $this->getMock(
            'Magento\Customer\Service\V1\Data\Customer', [], [], '', false
        );

        $this->messageManagerMock = $this->getMock('Magento\Framework\Message\Manager', [], [], '', false);
        $this->addressHelperMock = $this->getMock('Magento\Customer\Helper\Address', [], [], '', false);
        $this->storeManagerMock = $this->getMock('Magento\Store\Model\StoreManager', [], [], '', false);
        $this->storeMock = $this->getMock('Magento\Store\Model\Store', [], [], '', false);

        $this->scopeConfigMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->contextMock = $this->getMock('Magento\Framework\App\Action\Context', [], [], '', false);
        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->requestMock));
        $this->contextMock->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($this->responseMock));
        $this->contextMock->expects($this->any())
            ->method('getRedirect')
            ->will($this->returnValue($this->redirectMock));
        $this->contextMock->expects($this->any())
            ->method('getView')
            ->will($this->returnValue($this->viewMock));
        $this->contextMock->expects($this->any())
            ->method('getMessageManager')
            ->will($this->returnValue($this->messageManagerMock));

        $this->model = new \Magento\Customer\Controller\Account\Confirm(
            $this->contextMock,
            $this->customerSessionMock,
            $this->scopeConfigMock,
            $this->storeManagerMock,
            $this->customerAccountServiceMock,
            $this->addressHelperMock,
            $this->urlFactoryMock
        );
    }

    public function testIsLoggedIn()
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(true));

        $this->redirectMock->expects($this->once())
            ->method('redirect')
            ->with($this->responseMock, '*/*/', [])
            ->will($this->returnValue(false));

        $this->model->execute();
    }

    /**
     * @dataProvider getParametersDataProvider
     */
    public function testNoCustomerIdInRequest($customerId, $key)
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with($this->equalTo('id'), false)
            ->will($this->returnValue($customerId));
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with($this->equalTo('key'), false)
            ->will($this->returnValue($key));

        $exception = new \Exception('Bad request.');
        $this->messageManagerMock->expects($this->once())
            ->method('addException')
            ->with($this->equalTo($exception), $this->equalTo('There was an error confirming the account'));

        $testUrl = 'http://example.com';
        $this->urlMock->expects($this->once())
            ->method('getUrl')
            ->with($this->equalTo('*/*/index'), ['_secure' => true])
            ->will($this->returnValue($testUrl));

        $this->redirectMock->expects($this->once())
            ->method('error')
            ->with($this->equalTo($testUrl))
            ->will($this->returnValue($testUrl));

        $this->responseMock->expects($this->once())
            ->method('setRedirect')
            ->with($this->equalTo($testUrl))
            ->will($this->returnSelf());

        $this->model->execute();
    }

    /**
     * @return array
     */
    public function getParametersDataProvider()
    {
        return [
            [true, false],
            [false, true],
        ];
    }

    /**
     * @param $customerId
     * @param $key
     * @param $vatValidationEnabled
     * @param $addressType
     * @param $successMessage
     *
     * @dataProvider getSuccessMessageDataProvider
     */
    public function testSuccessMessage($customerId, $key, $vatValidationEnabled, $addressType, $successMessage)
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['id', false, $customerId],
                ['key', false, $key],
            ]);

        $this->customerAccountServiceMock->expects($this->once())
            ->method('activateCustomer')
            ->with($this->equalTo($customerId), $this->equalTo($key))
            ->will($this->returnValue($this->customerServiceDataMock));

        $this->customerSessionMock->expects($this->any())
            ->method('setCustomerDataAsLoggedIn')
            ->with($this->equalTo($this->customerServiceDataMock))
            ->will($this->returnSelf());

        $this->messageManagerMock->expects($this->any())
            ->method('addSuccess')
            ->with($this->stringContains($successMessage))
            ->will($this->returnSelf());

        $this->addressHelperMock->expects($this->once())
            ->method('isVatValidationEnabled')
            ->will($this->returnValue($vatValidationEnabled));
        $this->addressHelperMock->expects($this->any())
            ->method('getTaxCalculationAddressType')
            ->will($this->returnValue($addressType));

        $this->storeMock->expects($this->any())
            ->method('getFrontendName')
            ->will($this->returnValue('frontend'));
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));

        $this->model->execute();
    }

    /**
     * @return array
     */
    public function getSuccessMessageDataProvider()
    {
        return [
            [1, 1, false, null, __('Thank you for registering with')],
            [1, 1, true, Address::TYPE_BILLING, __('enter you billing address for proper VAT calculation')],
            [1, 1, true, Address::TYPE_SHIPPING, __('enter you shipping address for proper VAT calculation')],
        ];
    }

    /**
     * @param $customerId
     * @param $key
     * @param $backUrl
     * @param $successUrl
     * @param $resultUrl
     * @param $isSetFlag
     * @param $successMessage
     *
     * @dataProvider getSuccessRedirectDataProvider
     */
    public function testSuccessRedirect(
        $customerId,
        $key,
        $backUrl,
        $successUrl,
        $resultUrl,
        $isSetFlag,
        $successMessage
    ) {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['id', false, $customerId],
                ['key', false, $key],
                ['back_url', false, $backUrl],
            ]);

        $this->customerAccountServiceMock->expects($this->once())
            ->method('activateCustomer')
            ->with($this->equalTo($customerId), $this->equalTo($key))
            ->will($this->returnValue($this->customerServiceDataMock));

        $this->customerSessionMock->expects($this->any())
            ->method('setCustomerDataAsLoggedIn')
            ->with($this->equalTo($this->customerServiceDataMock))
            ->will($this->returnSelf());

        $this->messageManagerMock->expects($this->any())
            ->method('addSuccess')
            ->with($this->stringContains($successMessage))
            ->will($this->returnSelf());

        $this->storeMock->expects($this->any())
            ->method('getFrontendName')
            ->will($this->returnValue('frontend'));
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));

        $this->urlMock->expects($this->any())
            ->method('getUrl')
            ->with($this->equalTo('*/*/index'), ['_secure' => true])
            ->will($this->returnValue($successUrl));

        $this->redirectMock->expects($this->once())
            ->method('success')
            ->with($this->equalTo($resultUrl))
            ->will($this->returnValue($resultUrl));

        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(
                $this->equalTo(Url::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD),
                $this->equalTo(ScopeInterface::SCOPE_STORE)
            )
            ->will($this->returnValue($isSetFlag));

        $this->model->execute();
    }

    /**
     * @return array
     */
    public function getSuccessRedirectDataProvider()
    {
        return [
            [
                1,
                1,
                'http://example.com/back',
                null,
                'http://example.com/back',
                true,
                __('Thank you for registering with')
            ],
            [
                1,
                1,
                null,
                'http://example.com/success',
                'http://example.com/success',
                true,
                __('Thank you for registering with')
            ],
            [
                1,
                1,
                null,
                'http://example.com/success',
                'http://example.com/success',
                false,
                __('Thank you for registering with')
            ],
        ];
    }
}
