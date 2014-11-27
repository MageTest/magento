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
namespace Magento\Backend\Controller\Adminhtml\Dashboard;

class TunnelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_response;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRaw;

    protected function setUp()
    {
        $this->_request = $this->getMock('Magento\Framework\App\Request\Http', array(), array(), '', false);
        $this->_response = $this->getMock('Magento\Framework\App\Response\Http', array(), array(), '', false);
        $this->_objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
    }

    protected function tearDown()
    {
        $this->_request = null;
        $this->_response = null;
        $this->_objectManager = null;
    }

    public function testTunnelAction()
    {
        $fixture = uniqid();
        $this->_request->expects($this->at(0))
            ->method('getParam')
            ->with('ga')
            ->will($this->returnValue(urlencode(base64_encode(json_encode(array(1))))));
        $this->_request->expects($this->at(1))->method('getParam')->with('h')->will($this->returnValue($fixture));
        $tunnelResponse = $this->getMock('Magento\Framework\App\Response\Http', array(), array(), '', false);
        $httpClient = $this->getMock(
            'Magento\Framework\HTTP\ZendClient',
            array('setUri', 'setParameterGet', 'setConfig', 'request', 'getHeaders')
        );
        /** @var $helper \Magento\Backend\Helper\Dashboard\Data|\PHPUnit_Framework_MockObject_MockObject */
        $helper = $this->getMock(
            'Magento\Backend\Helper\Dashboard\Data',
            array('getChartDataHash'),
            array(),
            '',
            false,
            false
        );
        $helper->expects($this->any())->method('getChartDataHash')->will($this->returnValue($fixture));

        $this->_objectManager->expects($this->at(0))
            ->method('get')
            ->with('Magento\Backend\Helper\Dashboard\Data')
            ->will($this->returnValue($helper));
        $this->_objectManager->expects($this->at(1))
            ->method('create')
            ->with('Magento\Framework\HTTP\ZendClient')
            ->will($this->returnValue($httpClient));
        $httpClient->expects($this->once())->method('setUri')->will($this->returnValue($httpClient));
        $httpClient->expects($this->once())->method('setParameterGet')->will($this->returnValue($httpClient));
        $httpClient->expects($this->once())->method('setConfig')->will($this->returnValue($httpClient));
        $httpClient->expects($this->once())->method('request')->with('GET')->will($this->returnValue($tunnelResponse));
        $tunnelResponse->expects($this->any())->method('getHeaders')
            ->will($this->returnValue(array('Content-type' => 'test_header')));
        $tunnelResponse->expects($this->any())->method('getBody')->will($this->returnValue('success_msg'));
        $this->_response->expects($this->any())->method('getBody')->will($this->returnValue('success_msg'));

        $controller = $this->_factory($this->_request, $this->_response);
        $this->resultRaw->expects($this->once())
            ->method('setHeader')
            ->with('Content-type', 'test_header')
            ->willReturnSelf();
        $this->resultRaw->expects($this->once())
            ->method('setContents')
            ->with('success_msg')
            ->willReturnSelf();

        $controller->execute();
        $this->assertEquals('success_msg', $controller->getResponse()->getBody());
    }

    public function testTunnelAction400()
    {
        $controller = $this->_factory($this->_request, $this->_response);

        $this->resultRaw->expects($this->once())
            ->method('setHeader')
            ->willReturnSelf();
        $this->resultRaw->expects($this->once())
            ->method('setHttpResponseCode')
            ->with(400)
            ->willReturnSelf();
        $this->resultRaw->expects($this->once())
            ->method('setContents')
            ->with('Service unavailable: invalid request')
            ->willReturnSelf();

        $controller->execute();
    }

    public function testTunnelAction503()
    {
        $fixture = uniqid();
        $this->_request->expects($this->at(0))
            ->method('getParam')
            ->with('ga')
            ->will($this->returnValue(urlencode(base64_encode(json_encode(array(1))))));
        $this->_request->expects($this->at(1))->method('getParam')->with('h')->will($this->returnValue($fixture));
        /** @var $helper \Magento\Backend\Helper\Dashboard\Data|PHPUnit_Framework_MockObject_MockObject */
        $helper = $this->getMock(
            'Magento\Backend\Helper\Dashboard\Data',
            array('getChartDataHash'),
            array(),
            '',
            false,
            false
        );
        $helper->expects($this->any())->method('getChartDataHash')->will($this->returnValue($fixture));

        $this->_objectManager->expects($this->at(0))
            ->method('get')
            ->with('Magento\Backend\Helper\Dashboard\Data')
            ->will($this->returnValue($helper));
        $exceptionMock = new \Exception();
        $this->_objectManager->expects($this->at(1))
            ->method('create')
            ->with('Magento\Framework\HTTP\ZendClient')
            ->will($this->throwException($exceptionMock));
        $loggerMock = $this->getMock('Magento\Framework\Logger', array('logException'), array(), '', false);
        $loggerMock->expects($this->once())->method('logException')->with($exceptionMock);
        $this->_objectManager->expects($this->at(2))
            ->method('get')
            ->with('Magento\Framework\Logger')
            ->will($this->returnValue($loggerMock));

        $controller = $this->_factory($this->_request, $this->_response);

        $this->resultRaw->expects($this->once())
            ->method('setHeader')
            ->willReturnSelf();
        $this->resultRaw->expects($this->once())
            ->method('setHttpResponseCode')
            ->with(503)
            ->willReturnSelf();
        $this->resultRaw->expects($this->once())
            ->method('setContents')
            ->with('Service unavailable: see error log for details')
            ->willReturnSelf();

        $controller->execute();
    }

    /**
     * Create the tested object
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\Response\Http|null $response
     * @return \Magento\Backend\Controller\Adminhtml\Dashboard|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function _factory($request, $response = null)
    {
        if (!$response) {
            /** @var $response \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject */
            $response = $this->getMock('Magento\Framework\App\Response\Http', array(), array(), '', false);
            $response->headersSentThrowsException = false;
        }
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $varienFront = $helper->getObject('Magento\Framework\App\FrontController');

        $arguments = array(
            'request' => $request,
            'response' => $response,
            'objectManager' => $this->_objectManager,
            'frontController' => $varienFront
        );
        $this->resultRaw = $this->getMockBuilder('Magento\Framework\Controller\Result\Raw')
            ->disableOriginalConstructor()
            ->getMock();

        $resultRawFactory = $this->getMockBuilder('Magento\Framework\Controller\Result\RawFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultRawFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->resultRaw);
        $context = $helper->getObject('Magento\Backend\App\Action\Context', $arguments);
        return new \Magento\Backend\Controller\Adminhtml\Dashboard\Tunnel($context, $resultRawFactory);
    }
}
