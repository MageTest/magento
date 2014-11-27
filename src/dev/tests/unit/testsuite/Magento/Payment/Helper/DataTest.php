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

namespace Magento\Payment\Helper;

use Magento\TestFramework\Matcher\MethodInvokedAtIndex;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Payment\Helper\Data */
    private $helper;

    /**  @var \PHPUnit_Framework_MockObject_MockObject */
    private $scopeConfig;

    /**  @var \PHPUnit_Framework_MockObject_MockObject */
    private $initialConfig;

    /**  @var \PHPUnit_Framework_MockObject_MockObject */
    private $methodFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $layoutMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $appEmulation;

    protected function setUp()
    {
        $context = $this->getMock('Magento\Framework\App\Helper\Context', [], [], '', false);
        $this->scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface', [], [], '', false);
        $this->layoutMock = $this->getMock('Magento\Framework\View\LayoutInterface', [], [], '', false);
        $layoutFactoryMock = $this->getMockBuilder('Magento\Framework\View\LayoutFactory')
            ->disableOriginalConstructor()->getMock();
        $layoutFactoryMock->expects($this->once())->method('create')->willReturn($this->layoutMock);

        $this->methodFactory = $this->getMock('Magento\Payment\Model\Method\Factory', [], [], '', false);
        $this->appEmulation = $this->getMock('Magento\Core\Model\App\Emulation', [], [], '', false);
        $paymentConfig = $this->getMock('Magento\Payment\Model\Config', [], [], '', false);
        $this->initialConfig = $this->getMock('Magento\Framework\App\Config\Initial', [], [], '', false);

        $this->helper = new \Magento\Payment\Helper\Data(
            $context,
            $this->scopeConfig,
            $layoutFactoryMock,
            $this->methodFactory,
            $this->appEmulation,
            $paymentConfig,
            $this->initialConfig
        );
    }

    /**
     * @param string $code
     * @param string $class
     * @param string $methodInstance
     * @dataProvider getMethodInstanceDataProvider
     */
    public function testGetMethodInstance($code, $class, $methodInstance)
    {
        $this->scopeConfig->expects(
            $this->once()
        )->method(
            'getValue'
        )->will(
            $this->returnValue(
                $class
            )
        );
        $this->methodFactory->expects(
            $this->any()
        )->method(
            'create'
        )->with(
            $class
        )->will(
            $this->returnValue(
                $methodInstance
            )
        );

        $this->assertEquals($methodInstance, $this->helper->getMethodInstance($code));
    }

    /**
     * @param array $methodA
     * @param array $methodB
     *
     * @dataProvider getSortMethodsDataProvider
     */
    public function testSortMethods(array $methodA, array $methodB)
    {
        $this->initialConfig->expects($this->once())
            ->method('getData')
            ->will(
                $this->returnValue(
                    [
                        \Magento\Payment\Helper\Data::XML_PATH_PAYMENT_METHODS => [
                            $methodA['code'] => $methodA['data'],
                            $methodB['code'] => $methodB['data'],
                            'empty' => [],

                        ]
                    ]
                )
            );

        $this->scopeConfig->expects(new MethodInvokedAtIndex(0))
            ->method('getValue')
            ->with(
                    sprintf('%s/%s/model', Data::XML_PATH_PAYMENT_METHODS, $methodA['code'])
            )
            ->will($this->returnValue('Magento\Payment\Model\Method\AbstractMethod'));
        $this->scopeConfig->expects(new MethodInvokedAtIndex(1))
            ->method('getValue')
            ->with(
                sprintf('%s/%s/model', Data::XML_PATH_PAYMENT_METHODS, $methodB['code'])
            )
            ->will($this->returnValue('Magento\Payment\Model\Method\AbstractMethod'));
        $this->scopeConfig->expects(new MethodInvokedAtIndex(2))
            ->method('getValue')
            ->with(sprintf('%s/%s/model', Data::XML_PATH_PAYMENT_METHODS, 'empty'))
            ->will($this->returnValue(null));

        $methodInstanceMockA = $this->getMock(
            'Magento\Framework\Object',
            array('isAvailable','getConfigData'),
            array(),
            '',
            false
        );
        $methodInstanceMockA->expects($this->any())
            ->method('isAvailable')
            ->will($this->returnValue(true));
        $methodInstanceMockA->expects($this->any())
            ->method('getConfigData')
            ->will($this->returnValue($methodA['data']['sort_order']));

        $methodInstanceMockB = $this->getMock(
            'Magento\Framework\Object',
            array('isAvailable','getConfigData'),
            array(),
            '',
            false
        );
        $methodInstanceMockB->expects($this->any())
            ->method('isAvailable')
            ->will($this->returnValue(true));
        $methodInstanceMockB->expects($this->any())
            ->method('getConfigData')
            ->will($this->returnValue($methodB['data']['sort_order']));

        $this->methodFactory->expects($this->at(0))
            ->method('create')
            ->will($this->returnValue($methodInstanceMockA));

        $this->methodFactory->expects($this->at(1))
            ->method('create')
            ->will($this->returnValue($methodInstanceMockB));

        $sortedMethods = $this->helper->getStoreMethods();
        $this->assertTrue(array_shift($sortedMethods)->getSortOrder() < array_shift($sortedMethods)->getSortOrder());
    }

    public function testGetMethodFormBlock()
    {
        list($blockType, $methodCode) = ['method_block_type', 'method_code'];

        $methodMock = $this->getMockBuilder('Magento\Payment\Model\MethodInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $layoutMock = $this->getMockBuilder('Magento\Framework\View\LayoutInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $blockMock = $this->getMockBuilder('Magento\Framework\View\Element\BlockInterface')
            ->disableOriginalConstructor()
            ->setMethods(['setMethod', 'toHtml'])
            ->getMock();

        $methodMock->expects($this->once())->method('getFormBlockType')->willReturn($blockType);
        $methodMock->expects($this->once())->method('getCode')->willReturn($methodCode);
        $layoutMock->expects($this->once())->method('createBlock')
            ->with($blockType, $methodCode)
            ->willReturn($blockMock);
        $blockMock->expects($this->once())->method('setMethod')->with($methodMock);

        $this->assertSame($blockMock, $this->helper->getMethodFormBlock($methodMock, $layoutMock));
    }

    public function testGetInfoBlock()
    {
        $blockType = 'method_block_type';

        $methodMock = $this->getMockBuilder('Magento\Payment\Model\MethodInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getCode', 'getFormBlockType', 'getTitle', 'getInfoBlockType'])
            ->getMock();
        $infoMock = $this->getMockBuilder('Magento\Payment\Model\Info')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $blockMock = $this->getMockBuilder('Magento\Framework\View\Element\BlockInterface')
            ->disableOriginalConstructor()
            ->setMethods(['setInfo', 'toHtml'])
            ->getMock();

        $infoMock->expects($this->once())->method('getMethodInstance')->willReturn($methodMock);
        $methodMock->expects($this->once())->method('getInfoBlockType')->willReturn($blockType);
        $this->layoutMock->expects($this->once())->method('createBlock')
            ->with($blockType)
            ->willReturn($blockMock);
        $blockMock->expects($this->once())->method('setInfo')->with($infoMock);

        $this->assertSame($blockMock, $this->helper->getInfoBlock($infoMock));
    }

    public function testGetInfoBlockHtml()
    {
        list($storeId, $blockHtml, $secureMode, $blockType) = [1, 'HTML MARKUP', true, 'method_block_type'];

        $methodMock = $this->getMockBuilder('Magento\Payment\Model\MethodInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getCode', 'getFormBlockType', 'getTitle', 'getInfoBlockType', 'setStore'])
            ->getMock();
        $infoMock = $this->getMockBuilder('Magento\Payment\Model\Info')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $paymentBlockMock = $this->getMockBuilder('Magento\Framework\View\Element\BlockInterface')
            ->disableOriginalConstructor()
            ->setMethods(['setArea', 'setIsSecureMode', 'getMethod', 'setStore', 'toHtml', 'setInfo'])
            ->getMock();

        $this->appEmulation->expects($this->once())->method('startEnvironmentEmulation')->with($storeId);
        $infoMock->expects($this->once())->method('getMethodInstance')->willReturn($methodMock);
        $methodMock->expects($this->once())->method('getInfoBlockType')->willReturn($blockType);
        $this->layoutMock->expects($this->once())->method('createBlock')
            ->with($blockType)
            ->willReturn($paymentBlockMock);
        $paymentBlockMock->expects($this->once())->method('setInfo')->with($infoMock);
        $paymentBlockMock->expects($this->once())->method('setArea')
            ->with(\Magento\Framework\App\Area::AREA_FRONTEND)
            ->willReturnSelf();
        $paymentBlockMock->expects($this->once())->method('setIsSecureMode')
            ->with($secureMode);
        $paymentBlockMock->expects($this->once())->method('getMethod')
            ->willReturn($methodMock);
        $methodMock->expects($this->once())->method('setStore')->with($storeId);
        $paymentBlockMock->expects($this->once())->method('toHtml')
            ->willReturn($blockHtml);
        $this->appEmulation->expects($this->once())->method('stopEnvironmentEmulation');

        $this->assertEquals($blockHtml, $this->helper->getInfoBlockHtml($infoMock, $storeId));
    }

    public function getMethodInstanceDataProvider()
    {
        return array(
            ['method_code', 'method_class', 'method_instance'],
            ['method_code', false, false]
        );
    }

    public function getSortMethodsDataProvider()
    {
        return array(
            array(
                array('code' => 'methodA', 'data' => ['sort_order' => 0]),
                array('code' => 'methodB', 'data' => ['sort_order' => 1])
            ),
            array(
                array('code' => 'methodA', 'data' => ['sort_order' => 2]),
                array('code' => 'methodB', 'data' => ['sort_order' => 1]),
            )
        );
    }
}
