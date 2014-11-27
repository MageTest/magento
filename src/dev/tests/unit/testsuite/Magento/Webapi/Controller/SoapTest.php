<?php
/**
 * Test SOAP controller class.
 *
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
namespace Magento\Webapi\Controller;

class SoapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Webapi\Controller\Soap
     */
    protected $_soapController;

    /**
     * @var \Magento\Webapi\Model\Soap\Server
     */
    protected $_soapServerMock;

    /**
     * @var \Magento\Webapi\Model\Soap\Wsdl\Generator
     */
    protected $_wsdlGeneratorMock;

    /**
     * @var \Magento\Webapi\Controller\Soap\Request
     */
    protected $_requestMock;

    /**
     * @var \Magento\Webapi\Controller\Response
     */
    protected $_responseMock;

    /**
     * @var \Magento\Webapi\Controller\ErrorProcessor
     */
    protected $_errorProcessorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeMock;

    /**
     * Set up Controller object.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->_soapServerMock = $this->getMockBuilder('Magento\Webapi\Model\Soap\Server')
            ->disableOriginalConstructor()
            ->setMethods(array('getApiCharset', 'generateUri', 'handle'))
            ->getMock();
        $this->_wsdlGeneratorMock = $this->getMockBuilder('Magento\Webapi\Model\Soap\Wsdl\Generator')
            ->disableOriginalConstructor()
            ->setMethods(array('generate'))
            ->getMock();
        $this->_requestMock = $this->getMockBuilder('Magento\Webapi\Controller\Soap\Request')
            ->disableOriginalConstructor()
            ->setMethods(array('getParam', 'getRequestedServices'))
            ->getMock();
        $this->_responseMock = $this->getMockBuilder('Magento\Webapi\Controller\Response')
            ->disableOriginalConstructor()
            ->setMethods(array('clearHeaders', 'setHeader', 'sendResponse'))
            ->getMock();
        $this->_errorProcessorMock = $this->getMockBuilder('Magento\Webapi\Controller\ErrorProcessor')
            ->disableOriginalConstructor()
            ->setMethods(array('maskException'))
            ->getMock();
        $this->_appStateMock =  $this->getMock('\Magento\Framework\App\State', array(), array(), '', false);
        $localeMock =  $this->getMockBuilder('Magento\Framework\Locale')
            ->disableOriginalConstructor()
            ->setMethods(array('getLanguage'))
            ->getMock();
        $localeMock->expects($this->any())->method('getLanguage')->will($this->returnValue('en'));

        $localeResolverMock = $this->getMockBuilder(
            'Magento\Framework\Locale\Resolver'
        )->disableOriginalConstructor()->setMethods(
            array('getLocale')
        )->getMock();
        $localeResolverMock->expects($this->any())->method('getLocale')->will($this->returnValue($localeMock));

        $layoutMock = $this->getMock('Magento\Framework\View\LayoutInterface');

        $this->_responseMock->expects($this->any())->method('clearHeaders')->will($this->returnSelf());
        $this->_soapServerMock->expects($this->any())->method('setWSDL')->will($this->returnSelf());
        $this->_soapServerMock->expects($this->any())->method('setEncoding')->will($this->returnSelf());
        $this->_soapServerMock->expects($this->any())->method('setReturnResponse')->will($this->returnSelf());
        $pathProcessorMock = $this->getMock('Magento\Webapi\Model\PathProcessor', [], [], '', false);
        $areaListMock = $this->getMock('Magento\Framework\App\AreaList', array(), array(), '', false);
        $areaMock = $this->getMock('Magento\Framework\App\AreaInterface');
        $areaListMock->expects($this->any())->method('getArea')->will($this->returnValue($areaMock));
        $this->_soapController = new \Magento\Webapi\Controller\Soap(
            $this->_requestMock,
            $this->_responseMock,
            $this->_wsdlGeneratorMock,
            $this->_soapServerMock,
            $this->_errorProcessorMock,
            $this->_appStateMock,
            $layoutMock,
            $localeResolverMock,
            $pathProcessorMock,
            $areaListMock
        );
    }

    /**
     * Test successful WSDL content generation.
     */
    public function testDispatchWsdl()
    {
        $this->_mockGetParam(\Magento\Webapi\Model\Soap\Server::REQUEST_PARAM_WSDL, 1);
        $wsdl = 'Some WSDL content';
        $this->_wsdlGeneratorMock->expects($this->any())->method('generate')->will($this->returnValue($wsdl));

        $this->_soapController->dispatch($this->_requestMock);
        $this->assertEquals($wsdl, $this->_responseMock->getBody());
    }

    /**
     * Test successful SOAP action request dispatch.
     */
    public function testDispatchSoapRequest()
    {
        $this->_soapServerMock->expects($this->once())->method('handle');
        $response = $this->_soapController->dispatch($this->_requestMock);
        $this->assertEquals(200, $response->getHttpResponseCode());
    }

    /**
     * Test handling exception during dispatch.
     */
    public function testDispatchWithException()
    {
        $exceptionMessage = 'some error message';
        $exception = new \Magento\Webapi\Exception($exceptionMessage);
        $this->_soapServerMock->expects($this->any())->method('handle')->will($this->throwException($exception));
        $this->_errorProcessorMock->expects(
            $this->any()
        )->method(
            'maskException'
        )->will(
            $this->returnValue($exception)
        );
        $encoding = "utf-8";
        $this->_soapServerMock->expects($this->any())->method('getApiCharset')->will($this->returnValue($encoding));

        $this->_soapController->dispatch($this->_requestMock);

        $expectedMessage = <<<EXPECTED_MESSAGE
<?xml version="1.0" encoding="{$encoding}"?>
<env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope" >
   <env:Body>
      <env:Fault>
         <env:Code>
            <env:Value>env:Sender</env:Value>
         </env:Code>
         <env:Reason>
            <env:Text xml:lang="en">some error message</env:Text>
         </env:Reason>
      </env:Fault>
   </env:Body>
</env:Envelope>
EXPECTED_MESSAGE;
        $this->assertXmlStringEqualsXmlString($expectedMessage, $this->_responseMock->getBody());
    }

    /**
     * Mock getParam() of request object to return given value.
     *
     * @param $param
     * @param $value
     */
    protected function _mockGetParam($param, $value)
    {
        $this->_requestMock->expects(
            $this->once()
        )->method(
            'getParam'
        )->with(
            $param
        )->will(
            $this->returnValue($value)
        );
    }
}

