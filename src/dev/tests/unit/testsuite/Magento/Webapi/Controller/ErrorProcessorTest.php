<?php
/**
 * Test Webapi Error Processor.
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

use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Webapi\Exception as WebapiException;

class ErrorProcessorTest extends \PHPUnit_Framework_TestCase
{
    /** @var ErrorProcessor */
    protected $_errorProcessor;

    /** @var \Magento\Core\Helper\Data */
    protected $_helperMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_appStateMock;

    /** @var \Magento\Framework\Logger */
    protected $_loggerMock;

    protected function setUp()
    {
        /** Set up mocks for SUT. */
        $this->_helperMock = $this->getMockBuilder(
            'Magento\Core\Helper\Data'
        )->disableOriginalConstructor()->getMock();

        $this->_appStateMock = $this->getMockBuilder('Magento\Framework\App\State')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_loggerMock = $this->getMockBuilder('Magento\Framework\Logger')->disableOriginalConstructor()->getMock();

        $filesystemMock = $this->getMockBuilder('\Magento\Framework\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();

        /** Initialize SUT. */
        $this->_errorProcessor = new ErrorProcessor(
            $this->_helperMock,
            $this->_appStateMock,
            $this->_loggerMock,
            $filesystemMock
        );

        parent::setUp();
    }

    protected function tearDown()
    {
        unset($this->_errorProcessor);
        unset($this->_helperMock);
        unset($this->_appStateMock);
        parent::tearDown();
    }

    /**
     * Test render method in JSON format.
     */
    public function testRenderJson()
    {
        $_SERVER['HTTP_ACCEPT'] = 'json';
        /** Assert that jsonEncode method will be executed once. */
        $this->_helperMock->expects(
            $this->once()
        )->method(
            'jsonEncode'
        )->will(
            $this->returnCallback(array($this, 'callbackJsonEncode'), $this->returnArgument(0))
        );
        /** Init output buffering to catch output via echo function. */
        ob_start();
        $this->_errorProcessor->render('Message');
        /** Get output buffer. */
        $actualResult = ob_get_contents();
        ob_end_clean();
        $expectedResult = '{"messages":{"error":[{"code":500,"message":"Message"}]}}';
        $this->assertEquals($expectedResult, $actualResult, 'Invalid rendering in JSON.');
    }

    /**
     * Callback function for RenderJson and RenderJsonInDeveloperMode tests.
     *
     * Method encodes data to JSON and returns it.
     *
     * @param $data
     * @return string
     */
    public function callbackJsonEncode($data)
    {
        return json_encode($data);
    }

    /**
     * Test render method in JSON format with turned on developer mode.
     */
    public function testRenderJsonInDeveloperMode()
    {
        $_SERVER['HTTP_ACCEPT'] = 'json';
        /** Mock app to return enabled developer mode flag. */
        $this->_appStateMock->expects($this->any())->method('getMode')->will($this->returnValue('developer'));
        /** Assert that jsonEncode method will be executed once. */
        $this->_helperMock->expects(
            $this->once()
        )->method(
            'jsonEncode'
        )->will(
            $this->returnCallback(array($this, 'callbackJsonEncode'), $this->returnArgument(0))
        );
        ob_start();
        $this->_errorProcessor->render('Message', 'Message trace.', 401);
        $actualResult = ob_get_contents();
        ob_end_clean();
        $expectedResult = '{"messages":{"error":[{"code":401,"message":"Message","trace":"Message trace."}]}}';
        $this->assertEquals($expectedResult, $actualResult, 'Invalid rendering in JSON.');
    }

    /**
     * Test render method in XML format.
     */
    public function testRenderXml()
    {
        $_SERVER['HTTP_ACCEPT'] = 'xml';
        /** Init output buffering to catch output via echo function. */
        ob_start();
        $this->_errorProcessor->render('Message');
        /** Get output buffer. */
        $actualResult = ob_get_contents();
        ob_end_clean();
        $expectedResult = '<?xml version="1.0"?><error><messages><error><data_item><code>500</code>' .
            '<message><![CDATA[Message]]></message></data_item></error></messages></error>';
        $this->assertEquals($expectedResult, $actualResult, 'Invalid rendering in XML.');
    }

    /**
     * Test render method in XML format with turned on developer mode.
     */
    public function testRenderXmlInDeveloperMode()
    {
        $_SERVER['HTTP_ACCEPT'] = 'xml';
        /** Mock app to return enabled developer mode flag. */
        $this->_appStateMock->expects($this->any())->method('getMode')->will($this->returnValue('developer'));
        /** Init output buffering to catch output via echo function. */
        ob_start();
        $this->_errorProcessor->render('Message', 'Trace message.', 401);
        /** Get output buffer. */
        $actualResult = ob_get_contents();
        ob_end_clean();
        $expectedResult = '<?xml version="1.0"?><error><messages><error><data_item><code>401</code><message>' .
            '<![CDATA[Message]]></message><trace><![CDATA[Trace message.]]></trace></data_item></error>' .
            '</messages></error>';
        $this->assertEquals($expectedResult, $actualResult, 'Invalid rendering in XML with turned on developer mode.');
    }

    /**
     * Test default render format is JSON.
     */
    public function testRenderDefaultFormat()
    {
        /** Set undefined rendering format. */
        $_SERVER['HTTP_ACCEPT'] = 'undefined';
        /** Assert that jsonEncode method will be executed at least once. */
        $this->_helperMock->expects($this->atLeastOnce())->method('jsonEncode');
        $this->_errorProcessor->render('Message');
    }

    /**
     * Test maskException method with turned on developer mode.
     */
    public function testMaskExceptionInDeveloperMode()
    {
        /** Mock app isDeveloperMode to return true. */
        $this->_appStateMock->expects($this->once())->method('getMode')->will($this->returnValue('developer'));
        /** Init Logical exception. */
        $errorMessage = 'Error Message';
        $logicalException = new \LogicException($errorMessage);
        /** Assert that Logic exception is converted to WebapiException without message obfuscation. */
        $maskedException = $this->_errorProcessor->maskException($logicalException);
        $this->assertInstanceOf('Magento\Webapi\Exception', $maskedException);
        $this->assertEquals(
            $errorMessage,
            $maskedException->getMessage(),
            'Exception was masked incorrectly in developer mode.'
        );
    }

    /**
     * Test sendResponse method with various exceptions
     *
     * @dataProvider dataProviderForSendResponseExceptions
     */
    public function testMaskException($exception, $expectedHttpCode, $expectedMessage, $expectedDetails)
    {
        /** Assert that exception was logged. */
        // TODO:MAGETWO-21077 $this->_loggerMock->expects($this->once())->method('logException');
        $maskedException = $this->_errorProcessor->maskException($exception);
        $this->assertMaskedException(
            $maskedException,
            $expectedHttpCode,
            $expectedMessage,
            $expectedDetails
        );
    }

    public function dataProviderForSendResponseExceptions()
    {
        return array(
            'NoSuchEntityException' => array(
                new NoSuchEntityException(
                    NoSuchEntityException::MESSAGE_DOUBLE_FIELDS,
                    [
                        'fieldName' => 'detail1',
                        'fieldValue' => 'value1',
                        'field2Name' => 'resource_id',
                        'field2Value' => 'resource10',
                    ]
                ),
                \Magento\Webapi\Exception::HTTP_NOT_FOUND,
                NoSuchEntityException::MESSAGE_DOUBLE_FIELDS,
                [
                    'fieldName' => 'detail1',
                    'fieldValue' => 'value1',
                    'field2Name' => 'resource_id',
                    'field2Value' => 'resource10',
                ],
            ),
            'NoSuchEntityException (Empty message)' => array(
                new NoSuchEntityException(),
                WebapiException::HTTP_NOT_FOUND,
                'No such entity.',
                []
            ),
            'AuthorizationException' => array(
                new AuthorizationException(
                    AuthorizationException::NOT_AUTHORIZED,
                    ['consumer_id' => '3', 'resources' => '4']
                ),
                WebapiException::HTTP_UNAUTHORIZED,
                AuthorizationException::NOT_AUTHORIZED,
                ['consumer_id' => '3', 'resources' => '4']
            ),
            'Exception' => array(
                new \Exception('Non service exception', 5678),
                WebapiException::HTTP_INTERNAL_ERROR,
                'Internal Error. Details are available in Magento log file. Report ID:',
                []
            )
        );
    }

    /**
     * Assert that masked exception contains expected data.
     *
     * @param \Exception $maskedException
     * @param int $expectedHttpCode
     * @param string $expectedMessage
     * @param array $expectedDetails
     */
    public function assertMaskedException(
        $maskedException,
        $expectedHttpCode,
        $expectedMessage,
        $expectedDetails
    ) {
        /** All masked exceptions must be WebapiException */
        $expectedType = 'Magento\Webapi\Exception';
        $this->assertInstanceOf(
            $expectedType,
            $maskedException,
            "Masked exception type is invalid: expected '{$expectedType}', given '" . get_class(
                $maskedException
            ) . "'."
        );
        /** @var $maskedException WebapiException */
        $this->assertEquals(
            $expectedHttpCode,
            $maskedException->getHttpCode(),
            "Masked exception HTTP code is invalid: expected '{$expectedHttpCode}', " .
            "given '{$maskedException->getHttpCode()}'."
        );
        $this->assertContains(
            $expectedMessage,
            $maskedException->getMessage(),
            "Masked exception message is invalid: expected '{$expectedMessage}', " .
            "given '{$maskedException->getMessage()}'."
        );
        $this->assertEquals($expectedDetails, $maskedException->getDetails(), "Masked exception details are invalid.");
    }
}
