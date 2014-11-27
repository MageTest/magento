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

namespace Magento\Framework\View;

/**
 * Test for view BlockPool model
 */
class BlockPoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BlockPool
     */
    protected $blockPool;

    /**
     * Block factory
     * @var \Magento\Framework\View\Element\BlockFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $blockFactory;

    protected function setUp()
    {
        $this->blockFactory = $this->getMockBuilder('Magento\Framework\View\Element\BlockFactory')
            ->disableOriginalConstructor()
            ->setMethods(['createBlock'])
            ->getMock();
        $this->blockPool = new BlockPool($this->blockFactory);
    }

    public function testAdd()
    {
        $blockName = 'testName';
        $blockClass = '\Magento\Framework\View\BlockPoolTestBlock';
        $arguments = ['key' => 'value'];

        $block = $this->getMock('Magento\Framework\View\BlockPoolTestBlock');

        $this->blockFactory->expects($this->atLeastOnce())
            ->method('createBlock')
            ->with($blockClass, $arguments)
            ->will($this->returnValue($block));

        $this->assertEquals($this->blockPool, $this->blockPool->add($blockName, $blockClass, $arguments));

        $this->assertEquals([$blockName => $block], $this->blockPool->get());
        $this->assertEquals($block, $this->blockPool->get($blockName));
        $this->assertNull($this->blockPool->get('someWrongName'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid Block class name: NotExistingBlockClass
     */
    public function testAddWithException()
    {
        $this->blockPool->add('BlockPoolTestBlock', 'NotExistingBlockClass');
    }
}

/**
 * Class BlockPoolTestBlock mock
 */
class BlockPoolTestBlock implements \Magento\Framework\View\Element\BlockInterface
{
    /**
     * Produce and return block's html output
     *
     * @return string
     */
    public function toHtml()
    {
        return '';
    }
}
