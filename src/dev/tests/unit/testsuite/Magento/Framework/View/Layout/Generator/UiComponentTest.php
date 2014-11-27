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

namespace Magento\Framework\View\Layout\Generator;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Layout\ScheduledStructure;
use Magento\Framework\View\Layout;

class UiComponentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var ScheduledStructure|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scheduledStructureMock;

    /**
     * @var Layout\Reader\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerContextMock;

    /**
     * @var \Magento\Framework\View\Element\UiComponentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $uiComponentFactoryMock;

    /**
     * @var \Magento\Framework\Data\Argument\InterpreterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $argumentInterpreterMock;

    /**
     * @var \Magento\Framework\View\Layout\Generator\UiComponent
     */
    protected $uiComponent;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->argumentInterpreterMock = $this->getMockBuilder('Magento\Framework\Data\Argument\InterpreterInterface')
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->uiComponentFactoryMock = $this->getMockBuilder('Magento\Framework\View\Element\UiComponentFactory')
            ->disableOriginalConstructor()->getMock();
        $this->scheduledStructureMock = $this->getMockBuilder('Magento\Framework\View\Layout\ScheduledStructure')
            ->disableOriginalConstructor()->getMock();

        $this->uiComponent = $this->objectManagerHelper->getObject(
            'Magento\Framework\View\Layout\Generator\UiComponent',
            [
                'uiComponentFactory' => $this->uiComponentFactoryMock,
                'argumentInterpreter' => $this->argumentInterpreterMock
            ]
        );
    }

    public function testProcess()
    {
        $this->prepareScheduledStructure();

        $this->readerContextMock = $this->getMockBuilder('Magento\Framework\View\Layout\Reader\Context')
            ->disableOriginalConstructor()->getMock();

        $this->readerContextMock->expects($this->any())
            ->method('getScheduledStructure')
            ->willReturn($this->scheduledStructureMock);

        $generatorContextMock = $this->getMockBuilder('Magento\Framework\View\Layout\Generator\Context')
            ->disableOriginalConstructor()->getMock();

        $structureMock = $this->getMockBuilder('Magento\Framework\View\Layout\Data\Structure')
            ->disableOriginalConstructor()->getMock();

        $structureMock->expects($this->once())
            ->method('addToParentGroup')
            ->with(UiComponent::TYPE, 'new_group')
            ->willReturnSelf();

        $layoutMock = $this->getMockBuilder('Magento\Framework\View\LayoutInterface')->getMockForAbstractClass();

        $generatorContextMock->expects($this->any())
            ->method('getStructure')
            ->willReturn($structureMock);
        $generatorContextMock->expects($this->any())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $this->uiComponentFactoryMock->expects($this->any())
            ->method('setLayout')
            ->with($layoutMock)
            ->willReturnSelf();

        $blockMock = $this->getMockBuilder('Magento\Framework\View\Element\AbstractBlock')
            ->disableOriginalConstructor()->getMock();

        $this->uiComponentFactoryMock->expects($this->any())
            ->method('createUiComponent')
            ->with(
                'component_name',
                UiComponent::TYPE,
                ['attribute_1' => 'value_1', 'attribute_2' => 'value_2']
            )->willReturn($blockMock);

        $this->argumentInterpreterMock->expects($this->any())
            ->method('evaluate')
            ->will($this->returnValueMap([
                [['key_1' => 'value_1'], 'value_1'],
                [['key_2' => 'value_2'], 'value_2']
            ]));

        $layoutMock->expects($this->any())
            ->method('setBlock')
            ->with(UiComponent::TYPE, $blockMock)
            ->willReturnSelf();

        $this->uiComponent->process($this->readerContextMock, $generatorContextMock);
    }

    protected function prepareScheduledStructure()
    {
        $this->scheduledStructureMock->expects($this->any())
            ->method('getElements')
            ->willReturn([
                UiComponent::TYPE => [
                    UiComponent::TYPE,
                    [
                        'attributes' => [
                            'group'   => 'new_group',
                            'component' => 'component_name'
                        ],
                        'arguments'  => [
                            'attribute_1' => ['key_1' => 'value_1'],
                            'attribute_2' => ['key_2' => 'value_2'],
                        ]
                    ]
                ]
            ]);
    }
}
