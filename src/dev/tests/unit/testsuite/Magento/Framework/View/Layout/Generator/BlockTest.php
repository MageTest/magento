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

/**
 * @covers Magento\Framework\View\Layout\Generator\Block
 */
class BlockTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Magento\Framework\View\Layout\Generator\Block::process()
     * @covers Magento\Framework\View\Layout\Generator\Block::createBlock()
     * @param string $testGroup
     * @param string $testTemplate
     * @param string $testTtl
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $addToParentGroupCount
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $setTemplateCount
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $setTtlCount
     * @dataProvider provider
     */
    public function testProcess(
        $testGroup,
        $testTemplate,
        $testTtl,
        $addToParentGroupCount,
        $setTemplateCount,
        $setTtlCount
    ) {
        $elementName = 'test_block';
        $methodName = 'setTest';
        $literal = 'block';
        $argumentData = ['argument_data'];
        $class = 'test_class';

        $scheduleStructure = $this->getMock('Magento\Framework\View\Layout\ScheduledStructure', [], [], '', false);
        $scheduleStructure->expects($this->once())->method('getElements')->will(
            $this->returnValue(
                [
                    $elementName => [
                        $literal,
                        [
                            'actions' => [
                                [
                                    $methodName,
                                    [
                                        'test_argument' => $argumentData
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            )
        );

        $scheduleStructure->expects($this->once())->method('getElement')->with($elementName)->will(
            $this->returnValue(
                [
                    '',
                    [
                        'attributes' => [
                            'class' => $class,
                            'template' => $testTemplate,
                            'ttl' => $testTtl,
                            'group' => $testGroup
                        ],
                        'arguments' => ['data' => $argumentData]
                    ]
                ]
            )
        );
        $scheduleStructure->expects($this->once())->method('unsetElement')->with($elementName);

        /**
         * @var \Magento\Framework\View\Layout\Reader\Context|\PHPUnit_Framework_MockObject_MockObject $readerContext
         */
        $readerContext = $this->getMock('Magento\Framework\View\Layout\Reader\Context', [], [], '', false);
        $readerContext->expects($this->once())->method('getScheduledStructure')
            ->will($this->returnValue($scheduleStructure));

        $layout = $this->getMock('Magento\Framework\View\LayoutInterface', [], [], '', false);

        /**
         * @var \Magento\Framework\View\Element\AbstractBlock|\PHPUnit_Framework_MockObject_MockObject $blockInstance
         */
        // explicitly set mocked methods for successful expectation of magic methods
        $blockInstance = $this->getMock(
            'Magento\Framework\View\Element\AbstractBlock',
            ['setType', 'setTemplate', 'setTtl', $methodName, 'setNameInLayout', 'addData', 'setLayout'],
            [],
            '',
            false
        );
        $blockInstance->expects($this->once())->method('setType')->with(get_class($blockInstance));
        $blockInstance->expects($this->once())->method('setNameInLayout')->with($elementName);
        $blockInstance->expects($this->once())->method('addData')->with(['data' => null]);
        $blockInstance->expects($setTemplateCount)->method('setTemplate')->with($testTemplate);
        $blockInstance->expects($setTtlCount)->method('setTtl')->with(0);
        $blockInstance->expects($this->once())->method('setLayout')->with($layout);
        $blockInstance->expects($this->once())->method($methodName)->with(null);

        $layout->expects($this->once())->method('setBlock')->with($elementName, $blockInstance);

        $structure = $this->getMock('Magento\Framework\View\Layout\Data\Structure', [], [], '', false);
        $structure->expects($addToParentGroupCount)->method('addToParentGroup')->with($elementName, $testGroup);

        /**
         * @var \PHPUnit_Framework_MockObject_MockObject $generatorContext
         */
        $generatorContext = $this->getMock('Magento\Framework\View\Layout\Generator\Context', [], [], '', false);
        $generatorContext->expects($this->once())->method('getLayout')->will($this->returnValue($layout));
        $generatorContext->expects($this->once())->method('getStructure')->will($this->returnValue($structure));

        /**
         * @var \PHPUnit_Framework_MockObject_MockObject $argumentInterpreter
         */
        $argumentInterpreter = $this->getMock(
            'Magento\Framework\Data\Argument\InterpreterInterface',
            [],
            [],
            '',
            false
        );
        $argumentInterpreter->expects($this->exactly(2))->method('evaluate')->with($argumentData);

        /** @var \Magento\Framework\View\Element\BlockFactory|\PHPUnit_Framework_MockObject_MockObject $blockFactory */
        $blockFactory = $this->getMock('Magento\Framework\View\Element\BlockFactory', [], [], '', false);
        $blockFactory->expects($this->once())->method('createBlock')->with($class, ['data' => ['data' => null]])
            ->will($this->returnValue($blockInstance));

        /** @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject $eventManager */
        $eventManager = $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false);
        $eventManager->expects($this->once())->method('dispatch')
            ->with('core_layout_block_create_after', [$literal => $blockInstance]);

        /** @var \Magento\Framework\View\Layout\Generator\Block $block */
        $block = (new \Magento\TestFramework\Helper\ObjectManager($this))
            ->getObject(
                'Magento\Framework\View\Layout\Generator\Block',
                [
                    'argumentInterpreter' => $argumentInterpreter,
                    'blockFactory' => $blockFactory,
                    'eventManager' => $eventManager
                ]
            );
        $block->process($readerContext, $generatorContext);
    }

    /**
     * @return array
     */
    public function provider()
    {
        return array(
            array('test_group', '', 'testTtl', $this->once(), $this->never(), $this->once()),
            array('', 'test_template', '', $this->never(), $this->once(), $this->never())
        );
    }
}
