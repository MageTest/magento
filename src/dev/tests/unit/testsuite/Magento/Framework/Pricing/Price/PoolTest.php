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

namespace Magento\Framework\Pricing\Price;

/**
 * Test for Pool
 */
class PoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Pricing\Price\Pool
     */
    protected $pool;

    /**
     * @var array
     */
    protected $prices;

    /**
     * @var array
     */
    protected $target;

    /**
     * \Iterator
     */
    protected $targetPool;

    /**
     * Test setUp
     */
    public function setUp()
    {
        $this->prices = [
            'regular_price' => 'RegularPrice',
            'special_price' => 'SpecialPrice'
        ];
        $this->target = [
            'group_price' => 'TargetGroupPrice',
            'regular_price' => 'TargetRegularPrice'
        ];
        $this->targetPool = new Pool($this->target);
        $this->pool = new Pool($this->prices, $this->targetPool);
    }

    /**
     * test mergedConfiguration
     */
    public function testMergedConfiguration()
    {
        $expected = new Pool([
            'regular_price' => 'RegularPrice',
            'special_price' => 'SpecialPrice',
            'group_price' => 'TargetGroupPrice'
        ]);
        $this->assertEquals($expected, $this->pool);
    }

    /**
     * Test get method
     */
    public function testGet()
    {
        $this->assertEquals('RegularPrice', $this->pool->get('regular_price'));
        $this->assertEquals('SpecialPrice', $this->pool->get('special_price'));
        $this->assertEquals('TargetGroupPrice', $this->pool->get('group_price'));
    }

    /**
     * Test abilities of ArrayAccess interface
     */
    public function testArrayAccess()
    {
        $this->assertEquals('RegularPrice', $this->pool['regular_price']);
        $this->assertEquals('SpecialPrice', $this->pool['special_price']);
        $this->assertEquals('TargetGroupPrice', $this->pool['group_price']);
        $this->pool['fake_price'] = 'FakePrice';
        $this->assertEquals('FakePrice', $this->pool['fake_price']);
        $this->assertTrue(isset($this->pool['fake_price']));
        unset($this->pool['fake_price']);
        $this->assertFalse(isset($this->pool['fake_price']));
        $this->assertNull($this->pool['fake_price']);
    }

    /**
     * Test abilities of Iterator interface
     */
    public function testIterator()
    {
        foreach ($this->pool as $code => $class) {
            $this->assertEquals($this->pool[$code], $class);
        }
    }
}
