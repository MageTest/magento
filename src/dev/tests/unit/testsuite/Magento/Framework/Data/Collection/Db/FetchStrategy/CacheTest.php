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
namespace Magento\Framework\Data\Collection\Db\FetchStrategy;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Data\Collection\Db\FetchStrategy\Cache
     */
    private $_object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_cache;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_fetchStrategy;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_select;

    /**
     * @var array
     */
    private $_fixtureData = array(
        array('column_one' => 'row_one_value_one', 'column_two' => 'row_one_value_two'),
        array('column_one' => 'row_two_value_one', 'column_two' => 'row_two_value_two')
    );

    protected function setUp()
    {
        $this->_select = $this->getMock('Zend_Db_Select', array('assemble'), array(), '', false);
        $this->_select->expects(
            $this->once()
        )->method(
            'assemble'
        )->will(
            $this->returnValue('SELECT * FROM fixture_table')
        );

        $this->_cache = $this->getMockForAbstractClass('Magento\Framework\Cache\FrontendInterface');
        $this->_fetchStrategy = $this->getMockForAbstractClass(
            'Magento\Framework\Data\Collection\Db\FetchStrategyInterface'
        );

        $this->_object = new \Magento\Framework\Data\Collection\Db\FetchStrategy\Cache(
            $this->_cache,
            $this->_fetchStrategy,
            'fixture_',
            array('fixture_tag_one', 'fixture_tag_two'),
            86400
        );
    }

    protected function tearDown()
    {
        $this->_object = null;
        $this->_cache = null;
        $this->_fetchStrategy = null;
        $this->_select = null;
    }

    public function testFetchAllCached()
    {
        $this->_cache->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            'fixture_06a6b0cfd83bf997e76b1b403df86569'
        )->will(
            $this->returnValue(serialize($this->_fixtureData))
        );
        $this->_fetchStrategy->expects($this->never())->method('fetchAll');
        $this->_cache->expects($this->never())->method('save');
        $this->assertEquals($this->_fixtureData, $this->_object->fetchAll($this->_select, array()));
    }

    public function testFetchAllDelegation()
    {
        $cacheId = 'fixture_06a6b0cfd83bf997e76b1b403df86569';
        $bindParams = array('param_one' => 'value_one', 'param_two' => 'value_two');
        $this->_cache->expects($this->once())->method('load')->with($cacheId)->will($this->returnValue(false));
        $this->_fetchStrategy->expects(
            $this->once()
        )->method(
            'fetchAll'
        )->with(
            $this->_select,
            $bindParams
        )->will(
            $this->returnValue($this->_fixtureData)
        );
        $this->_cache->expects(
            $this->once()
        )->method(
            'save'
        )->with(
            serialize($this->_fixtureData),
            $cacheId,
            array('fixture_tag_one', 'fixture_tag_two'),
            86400
        );
        $this->assertEquals($this->_fixtureData, $this->_object->fetchAll($this->_select, $bindParams));
    }
}
