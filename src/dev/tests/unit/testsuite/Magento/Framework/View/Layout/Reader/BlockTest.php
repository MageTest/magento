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
 * Test class for \Magento\Framework\View\Layout\Reader\Block
 */
namespace Magento\Framework\View\Layout\Reader;

/**
 * Class BlockTest
 *
 * @covers Magento\Framework\View\Layout\Reader\Block
 */
class BlockTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Layout\ScheduledStructure|PHPUnit_Framework_MockObject_MockObject
     */
    protected $scheduledStructure;

    /**
     * @var \Magento\Framework\View\Layout\Reader\Context|PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\View\Layout\Reader\Pool|PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerPool;

    /**
     * @var \Magento\Framework\View\Layout\Element
     */
    protected $currentElement;

    /**
     * @var \Magento\Framework\View\Layout\Element
     */
    protected $parentElement;

    /**
     * @param string $xml
     * @return \Magento\Framework\View\Layout\Element
     */
    protected function getElement($xml)
    {
        return new \Magento\Framework\View\Layout\Element($xml);
    }

    /**
     * Prepare reader pool
     *
     * @param string $xml
     */
    protected function prepareReaderPool($xml)
    {
        $this->currentElement = $this->getElement($xml);
        $this->readerPool->expects($this->once())->method('readStructure')->with($this->context, $this->currentElement);
    }

    /**
     * Return testing instance of block
     *
     * @param array $arguments
     * @return \Magento\Framework\View\Layout\Reader\Block
     */
    protected function getBlock(array $arguments)
    {
        return (new \Magento\TestFramework\Helper\ObjectManager($this))
            ->getObject('Magento\Framework\View\Layout\Reader\Block', $arguments);
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    public function setUp()
    {
        $this->scheduledStructure = $this->getMock(
            'Magento\Framework\View\Layout\ScheduledStructure',
            [],
            [],
            '',
            false
        );
        $this->context = $this->getMock('Magento\Framework\View\Layout\Reader\Context', [], [], '', false);
        $this->readerPool = $this->getMock('Magento\Framework\View\Layout\Reader\Pool', [], [], '', false);
        $this->parentElement = $this->getElement('<' . \Magento\Framework\View\Layout\Reader\Block::TYPE_BLOCK . '/>');
    }

    /**
     * @param string $literal
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $setElementToRemoveListCount
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $isSetFlagCount
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $scheduleStructureCount
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $getScopeCount
     * @covers Magento\Framework\View\Layout\Reader\Block::process()
     * @dataProvider processDataProvider
     */
    public function testProcessBlock(
        $literal,
        $setElementToRemoveListCount,
        $isSetFlagCount,
        $scheduleStructureCount,
        $getScopeCount
    ) {
        $this->context->expects($this->once())->method('getScheduledStructure')
            ->will($this->returnValue($this->scheduledStructure));
        $this->scheduledStructure->expects($setElementToRemoveListCount)
            ->method('setElementToRemoveList')->with($literal);
        $scope = $this->getMock('Magento\Framework\App\ScopeInterface', [], [], '', false);

        $testValue = 'some_value';
        $scopeConfig = $this->getMock('Magento\Framework\App\Config', [], [], '', false);
        $scopeConfig->expects($isSetFlagCount)->method('isSetFlag')
            ->with($testValue, null, $scope)
            ->will($this->returnValue(false));

        $helper = $this->getMock('Magento\Framework\View\Layout\ScheduledStructure\Helper', [], [], '', false);
        $helper->expects($scheduleStructureCount)->method('scheduleStructure')->will($this->returnValue($literal));

        $scopeResolver = $this->getMock('Magento\Framework\App\ScopeResolverInterface', [], [], '', false);
        $scopeResolver->expects($getScopeCount)->method('getScope')->will($this->returnValue($scope));

        $this->prepareReaderPool('<' . $literal . ' ifconfig="' . $testValue . '"/>');

        /** @var \Magento\Framework\View\Layout\Reader\Block $block */
        $block = $this->getBlock(
            [
                'helper' => $helper,
                'scopeConfig' => $scopeConfig,
                'scopeResolver' => $scopeResolver,
                'readerPool' => $this->readerPool
            ]
        );
        $block->process($this->context, $this->currentElement, $this->parentElement);
    }

    /**
     * @covers Magento\Framework\View\Layout\Reader\Block::process()
     */
    public function testProcessReference()
    {
        $testName = 'test_value';
        $literal = 'referenceBlock';
        $this->context->expects($this->once())->method('getScheduledStructure')
            ->will($this->returnValue($this->scheduledStructure));
        $this->scheduledStructure->expects($this->once())->method('getStructureElementData')->with($testName, [])
            ->will($this->returnValue([]));
        $this->scheduledStructure->expects($this->once())->method('setStructureElementData')
            ->with($testName, ['actions' => [], 'arguments'=> []]);

        $this->prepareReaderPool('<' . $literal . ' name="' . $testName . '"/>');

        /** @var \Magento\Framework\View\Layout\Reader\Block $block */
        $block = $this->getBlock(['readerPool' => $this->readerPool]);
        $block->process($this->context, $this->currentElement, $this->parentElement);
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        return array(
            array('block', $this->once(), $this->once(), $this->once(), $this->once()),
            array('page', $this->never(), $this->never(), $this->never(), $this->never())
        );
    }
}
