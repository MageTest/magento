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

namespace Magento\Framework\View\Layout\Reader;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Layout\ScheduledStructure;

class MoveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\View\Layout\Reader\Move
     */
    protected $move;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var ScheduledStructure|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scheduledStructureMock;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->scheduledStructureMock = $this->getMockBuilder('Magento\Framework\View\Layout\ScheduledStructure')
            ->disableOriginalConstructor()->getMock();
        $this->contextMock = $this->getMockBuilder('Magento\Framework\View\Layout\Reader\Context')
            ->disableOriginalConstructor()->getMock();

        $this->contextMock->expects($this->any())
            ->method('getScheduledStructure')
            ->willReturn($this->scheduledStructureMock);

        $this->move = $this->objectManagerHelper->getObject('Magento\Framework\View\Layout\Reader\Move');
    }

    /**
     * @param \Magento\Framework\View\Layout\Element $currentElement
     * @param string $destination
     * @param string $siblingName
     * @param bool $isAfter
     * @param string $alias
     * @param \Magento\Framework\View\Layout\Element $parentElement
     *
     * @dataProvider processDataProvider
     */
    public function testProcess($currentElement, $destination, $siblingName, $isAfter, $alias, $parentElement)
    {
        $this->scheduledStructureMock->expects($this->any())
            ->method('setElementToMove')
            ->with(
                (string)$currentElement->getAttribute('element'),
                [$destination, $siblingName, $isAfter, $alias]
            );
        $this->move->process($this->contextMock, $currentElement, $parentElement);
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        return [
            'move_before' => [
                'element' => new \Magento\Framework\View\Layout\Element('
                    <move element="product" destination="product.info" before="before.block" as="as.product.info"/>
                '),
                'destination' => 'product.info',
                'siblingName' => 'before.block',
                'isAfter' => false,
                'alias' => 'as.product.info',
                'parentElement' => new \Magento\Framework\View\Layout\Element('<element/>')
            ],
            'move_after' => [
                'element' => new \Magento\Framework\View\Layout\Element('
                    <move element="product" destination="product.info" after="after.block" as="as.product.info"/>
                '),
                'destination' => 'product.info',
                'siblingName' => 'after.block',
                'isAfter' => true,
                'alias' => 'as.product.info',
                'parentElement' => new \Magento\Framework\View\Layout\Element('<element/>')
            ]
        ];
    }

    /**
     * @expectedException \Magento\Framework\Exception
     */
    public function testProcessInvalidData()
    {
        $invalidElement = new \Magento\Framework\View\Layout\Element('<move element="product" into="product.info"/>');
        $this->move->process($this->contextMock, $invalidElement, $invalidElement);
    }
}
