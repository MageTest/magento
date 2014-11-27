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

namespace Magento\Framework\View\Layout\ScheduledStructure;

use Magento\Framework\View\Layout;

/**
 * Class HelperTest
 * @covers Magento\Framework\View\Layout\ScheduledStructure\Helper
 */
class HelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $currentNodeName
     * @param string $actualNodeName
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $unsetPathElementCount
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $unsetStructureElementCount
     * @dataProvider providerScheduleStructure
     */
    public function testScheduleStructure(
        $currentNodeName,
        $actualNodeName,
        $unsetPathElementCount,
        $unsetStructureElementCount
    ) {
        $parentNodeName = 'parent_node';
        $currentNodeAs = 'currentNode';
        $after = 'after';
        $block = 'block';
        $testPath = 'test_path';
        $potentialChild = 'potential_child';

        /** @var Layout\ScheduledStructure|\PHPUnit_Framework_MockObject_MockObject $scheduledStructure */
        $scheduledStructure = $this->getMock('Magento\Framework\View\Layout\ScheduledStructure', [], [], '', false);
        $scheduledStructure->expects($this->once())->method('hasPath')
            ->with($parentNodeName)
            ->will($this->returnValue(true));
        $scheduledStructure->expects($this->any())->method('hasStructureElement')
            ->with($actualNodeName)
            ->will($this->returnValue(true));
        $scheduledStructure->expects($this->once())->method('setPathElement')
            ->with($actualNodeName, $testPath . '/' . $actualNodeName)
            ->will($this->returnValue(true));
        $scheduledStructure->expects($this->once())->method('setStructureElement')
            ->with($actualNodeName, [$block, $currentNodeAs, $parentNodeName, $after, true]);
        $scheduledStructure->expects($this->once())->method('getPath')
            ->with($parentNodeName)
            ->will($this->returnValue('test_path'));
        $scheduledStructure->expects($this->once())->method('getPaths')
            ->will($this->returnValue([$potentialChild => $testPath . '/' . $currentNodeName . '/']));
        $scheduledStructure->expects($unsetPathElementCount)->method('unsetPathElement')
            ->with($potentialChild);
        $scheduledStructure->expects($unsetStructureElementCount)->method('unsetStructureElement')
            ->with($potentialChild);

        $currentNode = new \Magento\Framework\View\Layout\Element(
            '<' . $block . ' name="' . $currentNodeName . '" as="' . $currentNodeAs . '" after="' . $after . '"/>'
        );
        $parentNode = new \Magento\Framework\View\Layout\Element('<' . $block . ' name="' . $parentNodeName . '"/>');

        /** @var Layout\ScheduledStructure\Helper $helper */
        $helper = (new \Magento\TestFramework\Helper\ObjectManager($this))
            ->getObject('Magento\Framework\View\Layout\ScheduledStructure\Helper');
        $result = $helper->scheduleStructure($scheduledStructure, $currentNode, $parentNode);
        $this->assertEquals($actualNodeName, $result);
    }

    /**
     * @return array
     */
    public function providerScheduleStructure()
    {
        return array(
            array('current_node', 'current_node', $this->once(), $this->once()),
            array('', 'parent_node_schedule_block0', $this->never(), $this->never())
        );
    }

    public function testScheduleNonExistentElement()
    {
        $key = 'key';

        /** @var Layout\ScheduledStructure|\PHPUnit_Framework_MockObject_MockObject $scheduledStructure */
        $scheduledStructure = $this->getMock('Magento\Framework\View\Layout\ScheduledStructure', [], [], '', false);
        $scheduledStructure->expects($this->once())->method('getStructureElement')->with($key)
            ->willReturn([]);
        $scheduledStructure->expects($this->once())->method('unsetPathElement')->with($key);
        $scheduledStructure->expects($this->once())->method('unsetStructureElement')->with($key);

        /** @var Layout\Data\Structure|\PHPUnit_Framework_MockObject_MockObject $scheduledStructure */
        $dataStructure = $this->getMock('Magento\Framework\View\Layout\Data\Structure', [], [], '', false);

        /** @var Layout\ScheduledStructure\Helper $helper */
        $helper = (new \Magento\TestFramework\Helper\ObjectManager($this))
            ->getObject('Magento\Framework\View\Layout\ScheduledStructure\Helper');
        $helper->scheduleElement($scheduledStructure, $dataStructure, $key);
    }

    public function testScheduleElement()
    {
        $key = 'key';
        $parentName = 'parent';
        $siblingName = 'sibling';
        $alias = 'alias';
        $block = 'block';
        $data = ['data'];

        /** @var Layout\ScheduledStructure|\PHPUnit_Framework_MockObject_MockObject $scheduledStructure */
        $scheduledStructure = $this->getMock('Magento\Framework\View\Layout\ScheduledStructure', [], [], '', false);
        $scheduledStructure->expects($this->any())->method('getStructureElement')->will(
            $this->returnValueMap(
                [
                    [
                        $key,
                        null,
                        [
                            Layout\ScheduledStructure\Helper::SCHEDULED_STRUCTURE_INDEX_TYPE => $block,
                            Layout\ScheduledStructure\Helper::SCHEDULED_STRUCTURE_INDEX_ALIAS => $alias,
                            Layout\ScheduledStructure\Helper::SCHEDULED_STRUCTURE_INDEX_PARENT_NAME => $parentName,
                            Layout\ScheduledStructure\Helper::SCHEDULED_STRUCTURE_INDEX_SIBLING_NAME => $siblingName,
                            Layout\ScheduledStructure\Helper::SCHEDULED_STRUCTURE_INDEX_IS_AFTER => true,
                        ]
                    ],
                    [$parentName, null, []]
                ]
            )
        );
        $scheduledStructure->expects($this->any())->method('getStructureElementData')->will(
            $this->returnValueMap([
                [$key, null, $data],
                [$parentName, null, $data]
            ])
        );
        $scheduledStructure->expects($this->any())->method('hasStructureElement')->will($this->returnValue(true));
        $scheduledStructure->expects($this->once())->method('setElement')->with($key, [$block, $data]);

        /** @var Layout\Data\Structure|\PHPUnit_Framework_MockObject_MockObject $scheduledStructure */
        $dataStructure = $this->getMock('Magento\Framework\View\Layout\Data\Structure', [], [], '', false);
        $dataStructure->expects($this->once())->method('createElement')->with($key, ['type' => $block]);
        $dataStructure->expects($this->once())->method('hasElement')->with($parentName)->will($this->returnValue(true));
        $dataStructure->expects($this->once())->method('setAsChild')->with($key, $parentName, $alias)
            ->will($this->returnValue(true));

        /** @var Layout\ScheduledStructure\Helper $helper */
        $helper = (new \Magento\TestFramework\Helper\ObjectManager($this))
            ->getObject('Magento\Framework\View\Layout\ScheduledStructure\Helper');
        $helper->scheduleElement($scheduledStructure, $dataStructure, $key);
    }
}
