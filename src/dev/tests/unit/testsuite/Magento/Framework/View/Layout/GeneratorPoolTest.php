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

/**
 * Test class for \Magento\Framework\View\Layout\Element
 */
namespace Magento\Framework\View\Layout;

class GeneratorPoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerContextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $generatorContextMock;

    /**
     * @var ScheduledStructure
     */
    protected $scheduledStructure;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $structureMock;

    /**
     * @var GeneratorPool
     */
    protected $model;

    protected function setUp()
    {
        // ScheduledStructure
        $this->readerContextMock = $this->getMockBuilder('Magento\Framework\View\Layout\Reader\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->scheduledStructure = new ScheduledStructure;
        $this->readerContextMock->expects($this->any())->method('getScheduledStructure')
            ->willReturn($this->scheduledStructure);

        // Data\Structure
        $this->generatorContextMock = $this->getMockBuilder('Magento\Framework\View\Layout\Generator\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->structureMock = $this->getMockBuilder('Magento\Framework\View\Layout\Data\Structure')
            ->disableOriginalConstructor()
            ->setMethods(['reorderChildElement'])
            ->getMock();
        $this->generatorContextMock->expects($this->any())->method('getStructure')
            ->willReturn($this->structureMock);

        $this->helperMock = $this->getMockBuilder('Magento\Framework\View\Layout\ScheduledStructure\Helper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new GeneratorPool($this->helperMock, $this->getGeneratorsMocks());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject[]
     */
    protected function getGeneratorsMocks()
    {
        $firstGenerator = $this->getMock('Magento\Framework\View\Layout\GeneratorInterface');
        $firstGenerator->expects($this->any())->method('getType')->willReturn('first_generator');
        $firstGenerator->expects($this->atLeastOnce())->method('process');

        $secondGenerator = $this->getMock('Magento\Framework\View\Layout\GeneratorInterface');
        $secondGenerator->expects($this->any())->method('getType')->willReturn('second_generator');
        $secondGenerator->expects($this->atLeastOnce())->method('process');
        return [$firstGenerator, $secondGenerator];
    }

    /**
     * @dataProvider processDataProvider
     */
    public function testProcess($schedule, $expectedSchedule)
    {
        foreach ($schedule['structure'] as $structureElement) {
            $this->scheduledStructure->setStructureElement($structureElement, []);
        }

        $moveMap = [];
        foreach ($schedule['move'] as $elementName => $move) {
            $this->scheduledStructure->setElementToMove($elementName, $move);
            list ($destination, $sibling, $isAfter) = $move;
            $moveMap[] = [$destination, $elementName, $sibling, $isAfter];
        }
        $invocation = $this->structureMock->expects($this->any())->method('reorderChildElement');
        call_user_func_array([$invocation, 'withConsecutive'], $moveMap);

        foreach ($schedule['remove'] as $remove) {
            $this->scheduledStructure->setElementToRemoveList($remove);
        }

        $this->helperMock->expects($this->atLeastOnce())->method('scheduleElement')
            ->with($this->scheduledStructure, $this->structureMock, $this->anything())
            ->willReturnCallback(function ($scheduledStructure, $structure, $elementName) use ($schedule) {
                /** @var $scheduledStructure ScheduledStructure */
                /** @var $structure Data\Structure */
                $this->assertContains($elementName, $schedule['structure']);
                $scheduledStructure->unsetStructureElement($elementName);
                $scheduledStructure->setElement($elementName, []);
                $structure->createStructuralElement($elementName, 'block', 'someClass');
            });

        $this->model->process($this->readerContextMock, $this->generatorContextMock);
        $this->assertEquals($expectedSchedule, $this->scheduledStructure->getElements());
    }

    /**
     * Data provider fo testProcess
     *
     * @return array
     */
    public function processDataProvider()
    {
        return [
            [
                'schedule' => [
                    'structure' => [
                        'first.element',
                        'second.element',
                        'third.element',
                        'remove.element'
                    ],
                    'move' => [
                        'third.element' => ['second.element', 'sibling', false, 'alias']
                    ],
                    'remove' => ['remove.element']
                ],
                'expectedScheduledElements' => [
                    'first.element' => [], 'second.element' => [], 'third.element' => []
                ]
            ],
        ];
    }
}
