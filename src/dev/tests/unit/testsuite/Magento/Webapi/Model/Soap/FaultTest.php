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
namespace Magento\Webapi\Model\Soap;

/**
 * Test SOAP fault model.
 */
class FaultTest extends \PHPUnit_Framework_TestCase
{
    const WSDL_URL = 'http://host.com/?wsdl&services=customerV1';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /** @var \Magento\Webapi\Model\Soap\Server */
    protected $_soapServerMock;

    /** @var \Magento\Webapi\Model\Soap\Fault */
    protected $_soapFault;

    /** @var \PHPUnit_Framework_MockObject_MockObject*/
    protected $_localeResolverMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_appStateMock;

    protected function setUp()
    {
        $this->_requestMock = $this->getMock('\Magento\Framework\App\RequestInterface');
        /** Initialize SUT. */
        $message = "Soap fault reason.";
        $details = array('param1' => 'value1', 'param2' => 2);
        $code = 111;
        $webapiException = new \Magento\Webapi\Exception(
            $message,
            $code,
            \Magento\Webapi\Exception::HTTP_INTERNAL_ERROR,
            $details
        );
        $this->_soapServerMock = $this->getMockBuilder(
            'Magento\Webapi\Model\Soap\Server'
        )->disableOriginalConstructor()->getMock();
        $this->_soapServerMock->expects($this->any())->method('generateUri')->will($this->returnValue(self::WSDL_URL));

        $this->_localeResolverMock = $this->getMockBuilder(
            'Magento\Framework\Locale\Resolver'
        )->disableOriginalConstructor()->getMock();
        $this->_localeResolverMock->expects(
            $this->any()
        )->method(
            'getLocale'
        )->will(
            $this->returnValue(new \Zend_Locale('en_US'))
        );

        $this->_appStateMock = $this->getMock('\Magento\Framework\App\State', array(), array(), '', false);

        $this->_soapFault = new \Magento\Webapi\Model\Soap\Fault(
            $this->_requestMock,
            $this->_soapServerMock,
            $webapiException,
            $this->_localeResolverMock,
            $this->_appStateMock
        );
        parent::setUp();
    }

    protected function tearDown()
    {
        unset($this->_soapFault);
        unset($this->_requestMock);
        parent::tearDown();
    }

    public function testToXmlDeveloperModeOff()
    {
        $this->_appStateMock->expects($this->any())->method('getMode')->will($this->returnValue('production'));
        $wsdlUrl = urlencode(self::WSDL_URL);
        $expectedResult = <<<XML
<?xml version="1.0" encoding="utf-8" ?>
<env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope" xmlns:m="{$wsdlUrl}">
    <env:Body>
        <env:Fault>
            <env:Code>
                <env:Value>env:Receiver</env:Value>
            </env:Code>
            <env:Reason>
                <env:Text xml:lang="en">Soap fault reason.</env:Text>
            </env:Reason>
            <env:Detail>
                <m:GenericFault>
                    <m:Parameters>
                        <m:GenericFaultParameter>
                            <m:key>param1</m:key>
                            <m:value>value1</m:value>
                        </m:GenericFaultParameter>
                        <m:GenericFaultParameter>
                            <m:key>param2</m:key>
                            <m:value>2</m:value>
                        </m:GenericFaultParameter>
                    </m:Parameters>
                </m:GenericFault>
            </env:Detail>
        </env:Fault>
    </env:Body>
</env:Envelope>
XML;

        $actualXml = $this->_soapFault->toXml();
        $this->assertEquals(
            $this->_sanitizeXML($expectedResult),
            $this->_sanitizeXML($actualXml),
            'Wrong SOAP fault message with default parameters.'
        );
    }

    public function testToXmlDeveloperModeOn()
    {
        $this->_appStateMock->expects($this->any())->method('getMode')->will($this->returnValue('developer'));
        $actualXml = $this->_soapFault->toXml(true);
        $this->assertContains('<m:Trace>', $actualXml, 'Exception trace is not found in XML.');
    }

    /**
     * Test getSoapFaultMessage method.
     *
     * @dataProvider dataProviderForGetSoapFaultMessageTest
     */
    public function testGetSoapFaultMessage(
        $faultReason,
        $faultCode,
        $additionalParameters,
        $expectedResult,
        $assertMessage
    ) {
        $actualResult = $this->_soapFault->getSoapFaultMessage($faultReason, $faultCode, $additionalParameters);
        $wsdlUrl = urlencode(self::WSDL_URL);
        $this->assertEquals(
            $this->_sanitizeXML(str_replace('{wsdl_url}', $wsdlUrl, $expectedResult)),
            $this->_sanitizeXML($actualResult),
            $assertMessage
        );
    }

    /**
     * Data provider for GetSoapFaultMessage test.
     *
     * @return array
     */
    public function dataProviderForGetSoapFaultMessageTest()
    {
        /** Include file with all expected SOAP fault XMLs. */
        $expectedXmls = include __DIR__ . '/../../_files/soap_fault/soap_fault_expected_xmls.php';

        //Each array contains data for SOAP Fault Message, Expected XML, and Assert Message.
        return array(
            'ArrayDataDetails' => array(
                'Fault reason',
                'Sender',
                array(
                    Fault::NODE_DETAIL_PARAMETERS => array('key1' => 'value1', 'key2' => 'value2', 'value3'),
                    Fault::NODE_DETAIL_TRACE => 'Trace',
                    'Invalid' => 'This node should be skipped'
                ),
                $expectedXmls['expectedResultArrayDataDetails'],
                'SOAP fault message with associated array data details is invalid.'
            ),
            'IndexArrayDetails' => array(
                'Fault reason',
                'Sender',
                array('value1', 'value2'),
                $expectedXmls['expectedResultIndexArrayDetails'],
                'SOAP fault message with index array data details is invalid.'
            ),
            'EmptyArrayDetails' => array(
                'Fault reason',
                'Sender',
                array(),
                $expectedXmls['expectedResultEmptyArrayDetails'],
                'SOAP fault message with empty array data details is invalid.'
            ),
            'ObjectDetails' => array(
                'Fault reason',
                'Sender',
                (object)array('key' => 'value'),
                $expectedXmls['expectedResultObjectDetails'],
                'SOAP fault message with object data details is invalid.'
            ),
            'ComplexDataDetails' => array(
                'Fault reason',
                'Sender',
                array(Fault::NODE_DETAIL_PARAMETERS => array('key' => array('sub_key' => 'value'))),
                $expectedXmls['expectedResultComplexDataDetails'],
                'SOAP fault message with complex data details is invalid.'
            )
        );
    }

    public function testConstructor()
    {
        $message = "Soap fault reason.";
        $details = array('param1' => 'value1', 'param2' => 2);
        $code = 111;
        $webapiException = new \Magento\Webapi\Exception(
            $message,
            $code,
            \Magento\Webapi\Exception::HTTP_INTERNAL_ERROR,
            $details
        );
        $soapFault = new \Magento\Webapi\Model\Soap\Fault(
            $this->_requestMock,
            $this->_soapServerMock,
            $webapiException,
            $this->_localeResolverMock,
            $this->_appStateMock
        );
        $actualXml = $soapFault->toXml();
        $wsdlUrl = urlencode(self::WSDL_URL);
        $expectedXml = <<<FAULT_XML
<?xml version="1.0" encoding="utf-8" ?>
<env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope" xmlns:m="{$wsdlUrl}">
    <env:Body>
        <env:Fault>
            <env:Code>
                <env:Value>env:Receiver</env:Value>
            </env:Code>
            <env:Reason>
                <env:Text xml:lang="en">{$message}</env:Text>
            </env:Reason>
            <env:Detail>
                <m:GenericFault>
                    <m:Parameters>
                        <m:GenericFaultParameter>
                            <m:key>param1</m:key>
                            <m:value>value1</m:value>
                        </m:GenericFaultParameter>
                        <m:GenericFaultParameter>
                            <m:key>param2</m:key>
                            <m:value>2</m:value>
                        </m:GenericFaultParameter>
                    </m:Parameters>
                </m:GenericFault>
            </env:Detail>
        </env:Fault>
    </env:Body>
</env:Envelope>
FAULT_XML;

        $this->assertEquals(
            $this->_sanitizeXML($expectedXml),
            $this->_sanitizeXML($actualXml),
            "Soap fault is invalid."
        );
    }

    /**
     * Convert XML to string.
     *
     * @param string $xmlString
     * @return string
     */
    protected function _sanitizeXML($xmlString)
    {
        $dom = new \DOMDocument(1.0);
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        // Only useful for "pretty" output with saveXML()
        $dom->loadXML($xmlString);
        // Must be done AFTER preserveWhiteSpace and formatOutput are set
        return $dom->saveXML();
    }
}
