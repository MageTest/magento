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
namespace Magento\Framework\Exception;

/**
 * Class LocalizedExceptionTest
 *
 * @package Magento\Framework\Exception
 */
class LocalizedExceptionTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Phrase\RendererInterface */
    private $defaultRenderer;

    /** @var string */
    private $renderedMessage;

    public function setUp()
    {
        $this->defaultRenderer = \Magento\Framework\Phrase::getRenderer();
        $rendererMock = $this->getMockBuilder('Magento\Framework\Phrase\Renderer\Placeholder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->renderedMessage = 'rendered message';
        $rendererMock->expects($this->once())
            ->method('render')
            ->will($this->returnValue($this->renderedMessage));
        \Magento\Framework\Phrase::setRenderer($rendererMock);
    }

    public function tearDown()
    {
        \Magento\Framework\Phrase::setRenderer($this->defaultRenderer);
    }

    /** @dataProvider constructorParametersDataProvider */
    public function testConstructor($message, $params, $expectedLogMessage)
    {
        $cause = new \Exception();
        $localizeException = new LocalizedException(
            $message,
            $params,
            $cause
        );

        $this->assertEquals(0, $localizeException->getCode());

        $this->assertEquals($message, $localizeException->getRawMessage());
        $this->assertEquals($this->renderedMessage, $localizeException->getMessage());
        $this->assertEquals($expectedLogMessage, $localizeException->getLogMessage());
        
        $this->assertSame($cause, $localizeException->getPrevious());
    }

    public function constructorParametersDataProvider()
    {
        return [
            'withNoNameParameters' => [
                'message %1 %2',
                ['parameter1',
                 'parameter2'],
                'message parameter1 parameter2',
            ],
            'withNamedParameters'  => [
                'message %key1 %key2',
                ['key1' => 'parameter1',
                 'key2' => 'parameter2'],
                'message parameter1 parameter2',
            ],
            'withoutParameters'    => [
                'message',
                [],
                'message',
                'message',
            ],
        ];
    }

    public function testGetRawMessage()
    {
        $message =  'message %1 %2';
        $params = [
            'parameter1',
            'parameter2'
        ];
        $cause = new \Exception();
        $localizeException = new LocalizedException(
            $message,
            $params,
            $cause
        );
        $this->assertEquals($message, $localizeException->getRawMessage());
    }

    public function testGetParameters()
    {
        $message =  'message %1 %2';
        $params = [
            'parameter1',
            'parameter2'
        ];
        $cause = new \Exception();
        $localizeException = new LocalizedException(
            $message,
            $params,
            $cause
        );

        $this->assertEquals($params, $localizeException->getParameters());
    }

    public function testGetLogMessage()
    {
        $message =  'message %1 %2';
        $params = [
            'parameter1',
            'parameter2'
        ];
        $cause = new \Exception();

        $localizeException = new LocalizedException(
            $message,
            $params,
            $cause
        );
        $expectedLogMessage = 'message parameter1 parameter2';
        $this->assertEquals($expectedLogMessage, $localizeException->getLogMessage());
    }
}
