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
namespace Magento\Framework\Data\Collection;

class DbTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Data\Collection\Db
     */
    protected $collection;

    /**
     * @var \Magento\Framework\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Core\Model\EntityFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityFactoryMock;

    /**
     * @var \Magento\Framework\Data\Collection\Db\FetchStrategyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fetchStrategyMock;

    protected function setUp()
    {
        $this->fetchStrategyMock = $this->getMock(
            'Magento\Framework\Data\Collection\Db\FetchStrategy\Query', array('fetchAll'), array(), '', false
        );
        $this->entityFactoryMock = $this->getMock(
            'Magento\Core\Model\EntityFactory', array('create'), array(), '', false
        );
        $this->loggerMock = $this->getMock('Magento\Framework\Logger', array('log'), array(), '', false);
        $this->collection = new \Magento\Framework\Data\Collection\Db(
            $this->entityFactoryMock,
            $this->loggerMock,
            $this->fetchStrategyMock
        );
    }

    protected function tearDown()
    {
        unset($this->collection);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Zend_Db_Adapter_Abstract
     */
    public function testSetAddOrder()
    {
        $adapter = $this->getMockForAbstractClass(
            'Zend_Db_Adapter_Abstract',
            array(),
            '',
            false,
            true,
            true,
            array('fetchAll')
        );
        $this->collection->setConnection($adapter);

        $select = $this->collection->getSelect();
        $this->assertEmpty($select->getPart(\Zend_Db_Select::ORDER));

        /* Direct access to select object is available and many places are using it for sort order declaration */
        $select->order('select_field', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);
        $this->collection->addOrder('some_field', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);
        $this->collection->setOrder('other_field', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);
        $this->collection->addOrder('other_field', \Magento\Framework\Data\Collection::SORT_ORDER_DESC);

        $this->collection->load();
        $selectOrders = $select->getPart(\Zend_Db_Select::ORDER);
        $this->assertEquals(array('select_field', 'ASC'), array_shift($selectOrders));
        $this->assertEquals('some_field ASC', (string)array_shift($selectOrders));
        $this->assertEquals('other_field DESC', (string)array_shift($selectOrders));
        $this->assertEmpty(array_shift($selectOrders));

        return $adapter;
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject|Zend_Db_Adapter_Abstract $adapter
     * @depends testSetAddOrder
     */
    public function testUnshiftOrder($adapter)
    {
        $this->collection->setConnection($adapter);
        $this->collection->addOrder('some_field', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);
        $this->collection->unshiftOrder('other_field', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);

        $this->collection->load();
        $selectOrders = $this->collection->getSelect()->getPart(\Zend_Db_Select::ORDER);
        $this->assertEquals('other_field ASC', (string)array_shift($selectOrders));
        $this->assertEquals('some_field ASC', (string)array_shift($selectOrders));
        $this->assertEmpty(array_shift($selectOrders));
    }

    /**
     * Test that adding field to filter builds proper sql WHERE condition
     */
    public function testAddFieldToFilter()
    {
        $adapter = $this->getMock('Zend_Db_Adapter_Pdo_Mysql', array('prepareSqlCondition'), array(), '', false);
        $adapter->expects(
            $this->any()
        )->method(
            'prepareSqlCondition'
        )->with(
            $this->stringContains('is_imported'),
            $this->anything()
        )->will(
            $this->returnValue('is_imported = 1')
        );
        $this->collection->setConnection($adapter);
        $select = $this->collection->getSelect()->from('test');

        $this->collection->addFieldToFilter('is_imported', array('eq' => '1'));
        $this->assertEquals('SELECT `test`.* FROM `test` WHERE (is_imported = 1)', $select->assemble());
    }

    /**
     * Test that adding multiple fields to filter at once
     * builds proper sql WHERE condition and created conditions are joined with OR
     */
    public function testAddFieldToFilterWithMultipleParams()
    {
        $adapter = $this->getMock('Zend_Db_Adapter_Pdo_Mysql', array('prepareSqlCondition'), array(), '', false);
        $adapter->expects(
            $this->at(0)
        )->method(
            'prepareSqlCondition'
        )->with(
            '`weight`',
            array('in' => array(1, 3))
        )->will(
            $this->returnValue('weight in (1, 3)')
        );
        $adapter->expects(
            $this->at(1)
        )->method(
            'prepareSqlCondition'
        )->with(
            '`name`',
            array('like' => 'M%')
        )->will(
            $this->returnValue("name like 'M%'")
        );
        $this->collection->setConnection($adapter);
        $select = $this->collection->getSelect()->from("test");

        $this->collection->addFieldToFilter(
            array('weight', 'name'),
            array(array('in' => array(1, 3)), array('like' => 'M%'))
        );

        $this->assertEquals(
            "SELECT `test`.* FROM `test` WHERE ((weight in (1, 3)) OR (name like 'M%'))",
            $select->assemble()
        );

        $adapter->expects(
            $this->at(0)
        )->method(
            'prepareSqlCondition'
        )->with(
            '`is_imported`',
            $this->anything()
        )->will(
            $this->returnValue('is_imported = 1')
        );

        $this->collection->addFieldToFilter('is_imported', array('eq' => '1'));
        $this->assertEquals(
            "SELECT `test`.* FROM `test` WHERE ((weight in (1, 3)) OR (name like 'M%')) AND (is_imported = 1)",
            $select->assemble()
        );
    }

    /**
     * Test that adding field to filter by value which contains question mark produce correct SQL
     */
    public function testAddFieldToFilterValueContainsQuestionMark()
    {
        $adapter = $this->getMock(
            'Zend_Db_Adapter_Pdo_Mysql',
            array('select', 'prepareSqlCondition', 'supportStraightJoin'),
            array(),
            '',
            false
        );
        $adapter->expects(
            $this->once()
        )->method(
            'prepareSqlCondition'
        )->with(
            '`email`',
            array('like' => 'value?')
        )->will(
            $this->returnValue('email LIKE \'%value?%\'')
        );
        $adapter->expects($this->once())
            ->method('select')
            ->will($this->returnValue(new \Magento\Framework\DB\Select($adapter)));
        $this->collection->setConnection($adapter);

        $select = $this->collection->getSelect()->from('test');
        $this->collection->addFieldToFilter('email', array('like' => 'value?'));
        $this->assertEquals("SELECT `test`.* FROM `test` WHERE (email LIKE '%value?%')", $select->assemble());
    }

    /**
     * Test that field is quoted when added to SQL via addFieldToFilter()
     */
    public function testAddFieldToFilterFieldIsQuoted()
    {
        $adapter = $this->getMock(
            'Zend_Db_Adapter_Pdo_Mysql',
            array('quoteIdentifier', 'prepareSqlCondition'),
            array(),
            '',
            false
        );
        $adapter->expects(
            $this->once()
        )->method(
            'quoteIdentifier'
        )->with(
            'email'
        )->will(
            $this->returnValue('`email`')
        );
        $adapter->expects(
            $this->any()
        )->method(
            'prepareSqlCondition'
        )->with(
            $this->stringContains('`email`'),
            $this->anything()
        )->will(
            $this->returnValue('`email` = "foo@example.com"')
        );
        $this->collection->setConnection($adapter);
        $select = $this->collection->getSelect()->from('test');

        $this->collection->addFieldToFilter('email', array('eq' => 'foo@example.com'));
        $this->assertEquals('SELECT `test`.* FROM `test` WHERE (`email` = "foo@example.com")', $select->assemble());
    }

    /**
     * Test that after cloning collection $this->_select in initial and cloned collections
     * do not reference the same object
     *
     * @covers \Magento\Framework\Data\Collection\Db::__clone
     */
    public function testClone()
    {
        $adapter = $this->getMockForAbstractClass('Zend_Db_Adapter_Abstract', array(), '', false);
        $this->collection->setConnection($adapter);
        $this->assertInstanceOf('Zend_Db_Select', $this->collection->getSelect());

        $clonedCollection = clone $this->collection;

        $this->assertInstanceOf('Zend_Db_Select', $clonedCollection->getSelect());
        $this->assertNotSame(
            $clonedCollection->getSelect(),
            $this->collection->getSelect(),
            'Collection was cloned but $this->_select in both initial and cloned collections reference the same object'
        );
    }

    /**
     * @param bool $printQuery
     * @param bool $printFlag
     * @param string $query
     * @param string $expected
     *
     * @dataProvider printLogQueryPrintingDataProvider
     */
    public function testPrintLogQueryPrinting($printQuery, $printFlag, $query, $expected)
    {
        $this->expectOutputString($expected);
        $this->collection->setFlag('print_query', $printFlag);
        $this->collection->printLogQuery($printQuery, false, $query);
    }

    public function printLogQueryPrintingDataProvider()
    {
        return array(
            array(false, false, 'some_query', ''),
            array(true, false, 'some_query', 'some_query'),
            array(false, true, 'some_query', 'some_query')
        );
    }

    /**
     * @param bool $logQuery
     * @param bool $logFlag
     * @param int $expectedCalls
     *
     * @dataProvider printLogQueryLoggingDataProvider
     */
    public function testPrintLogQueryLogging($logQuery, $logFlag, $expectedCalls)
    {
        $this->collection->setFlag('log_query', $logFlag);
        $this->loggerMock->expects($this->exactly($expectedCalls))->method('log');
        $this->collection->printLogQuery(false, $logQuery, 'some_query');
    }

    public function printLogQueryLoggingDataProvider()
    {
        return array(
            array(true, false, 1),
            array(false, true, 1),
            array(false, false, 0),
        );
    }

    /**
     * @expectedException \Zend_Exception
     * @expectedExceptionMessage dbModel read resource does not implement \Zend_Db_Adapter_Abstract
     */
    public function testSetConnectionException()
    {
        $adapter = new \stdClass();
        $this->collection->setConnection($adapter);
    }

    public function testFetchItem()
    {
        $data = array(1 => 'test');
        $counter = 0;
        $statementMock = $this->getMock('Zend_Db_Statement_Pdo', array('fetch'), array(), '', false);
        $statementMock->expects($this->exactly(2))
            ->method('fetch')
            ->will($this->returnCallback(function () use (&$counter, $data) {
                return ++$counter % 2 ? array() : $data;
            }));

        $adapterMock = $this->getMock('Zend_Db_Adapter_Pdo_Mysql',
            array('select', 'query'), array(), '', false);
        $selectMock = $this->getMock(
            'Magento\Framework\DB\Select', array(), array('adapter' => $adapterMock)
        );
        $adapterMock->expects($this->once())
            ->method('query')
            ->with($selectMock, $this->anything())
            ->will($this->returnValue($statementMock));
        $adapterMock->expects($this->once())
            ->method('select')
            ->will($this->returnValue($selectMock));

        $this->collection->setConnection($adapterMock);
        $this->assertFalse($this->collection->fetchItem());

        $objectMock = $this->getMock('Magento\Framework\Object', array('setData'), array());
        $objectMock->expects($this->once())
            ->method('setData')
            ->with($data);
        $this->entityFactoryMock->expects($this->once())
            ->method('create')
            ->with('Magento\Framework\Object')
            ->will($this->returnValue($objectMock));

        $this->assertEquals($objectMock, $this->collection->fetchItem());
    }

    public function testGetSize()
    {
        $countSql = 500;
        $adapterMock = $this->getMock(
            'Zend_Db_Adapter_Pdo_Mysql',
            array('select', 'quoteInto', 'prepareSqlCondition', 'fetchOne'),
            array(),
            '',
            false
        );
        $selectMock = $this->getMock(
            'Magento\Framework\DB\Select',
            array('orWhere', 'where', 'reset', 'columns'),
            array('adapter' => $adapterMock)
        );
        $selectMock->expects($this->exactly(4))
            ->method('reset');
        $selectMock->expects($this->once())
            ->method('columns')
            ->with('COUNT(*)');
        $adapterMock->expects($this->once())
            ->method('select')
            ->will($this->returnValue($selectMock));
        $adapterMock->expects($this->exactly(2))
            ->method('quoteInto')
            ->will($this->returnValueMap(array(
                        array('testField1=?', 'testValue1', null, null, 'testField1=testValue1'),
                        array('testField4=?', 'testValue4', null, null, 'testField4=testValue4'),
                    )));
        $selectMock->expects($this->once())
            ->method('orWhere')
            ->with('testField1=testValue1');
        $selectMock->expects($this->exactly(3))
            ->method('where')
            ->will($this->returnValueMap(array(
                array('testValue2', $this->returnSelf()),
                array(
                    'testField3 = testValue3',
                    null,
                    \Magento\Framework\DB\Select::TYPE_CONDITION,
                    $this->returnSelf()
                ),
                array('testField4=testValue4', $this->returnSelf()),
            )));
        $adapterMock->expects($this->once())
            ->method('prepareSqlCondition')
            ->with('testField3', 'testValue3')
            ->will($this->returnValue('testField3 = testValue3'));
        $adapterMock->expects($this->once())
            ->method('fetchOne')
            ->with($selectMock, array())
            ->will($this->returnValue($countSql));

        $this->collection->addFilter('testField1', 'testValue1', 'or');
        $this->collection->addFilter('testField2', 'testValue2', 'string');
        $this->collection->addFilter('testField3', 'testValue3', 'public');
        $this->collection->addFilter('testField4', 'testValue4');
        $this->collection->setConnection($adapterMock);
        $this->assertEquals($countSql, $this->collection->getSize());
        $this->assertEquals($countSql, $this->collection->getSize());
    }

    public function testGetSelectSql()
    {
        $adapterMock = $this->getMock('Zend_Db_Adapter_Pdo_Mysql', array('select'), array(), '', false);
        $selectMock = $this->getMock(
            'Magento\Framework\DB\Select', array('__toString'), array('adapter' => $adapterMock)
        );
        $adapterMock->expects($this->once())
            ->method('select')
            ->will($this->returnValue($selectMock));

        $sql = 'query';
        $selectMock->expects($this->once())
            ->method('__toString')
            ->will($this->returnValue($sql));

        $this->collection->setConnection($adapterMock);
        $this->assertEquals($sql, $this->collection->getSelectSql(true));
        $this->assertEquals($selectMock, $this->collection->getSelectSql());
    }

    public function testGetData()
    {
        $adapterMock = $this->getMock(
            'Zend_Db_Adapter_Pdo_Mysql',
            array('select', 'quoteInto', 'prepareSqlCondition', 'fetchOne'),
            array(),
            '',
            false
        );
        $selectMock = $this->getMock(
            'Magento\Framework\DB\Select',
            array('orWhere', 'where', 'reset', 'columns'),
            array('adapter' => $adapterMock)
        );
        $selectMock->expects($this->once())
            ->method('where')
            ->with('aliasField3 = testValue3', null, \Magento\Framework\DB\Select::TYPE_CONDITION)
            ->will($this->returnSelf());

        $adapterMock->expects($this->once())
            ->method('select')
            ->will($this->returnValue($selectMock));
        $adapterMock->expects($this->once())
            ->method('prepareSqlCondition')
            ->with('aliasField3', 'testValue3')
            ->will($this->returnValue('aliasField3 = testValue3'));

        $this->collection->addFilter('testField3', 'testValue3', 'public');
        $this->collection->addFilterToMap('testFilter', 'testAlias', 'testGroup');
        $this->collection->addFilterToMap('testField3', 'aliasField3');

        $this->collection->setConnection($adapterMock);
        $this->assertNull($this->collection->getData());
    }

    /**
     * @dataProvider distinctDataProvider
     */
    public function testDistinct($flag, $expectedFlag)
    {
        $adapterMock = $this->getMock('Zend_Db_Adapter_Pdo_Mysql', array('select'), array(), '', false);
        $selectMock = $this->getMock(
            'Magento\Framework\DB\Select',
            array('distinct'),
            array('adapter' => $adapterMock)
        );
        $adapterMock->expects($this->once())
            ->method('select')
            ->will($this->returnValue($selectMock));
        $selectMock->expects($this->once())
            ->method('distinct')
            ->with($expectedFlag);

        $this->collection->setConnection($adapterMock);
        $this->collection->distinct($flag);
    }

    public function distinctDataProvider()
    {
        return array(
            array(true, true),
            array(false, false),
        );
    }

    public function testToOptionHash()
    {
        $data = array(10 => 'test');
        $adapterMock = $this->getMock('Zend_Db_Adapter_Pdo_Mysql',
            array('select', 'query'), array(), '', false);
        $selectMock = $this->getMock(
            'Magento\Framework\DB\Select', array(), array('adapter' => $adapterMock)
        );
        $adapterMock->expects($this->once())
            ->method('select')
            ->will($this->returnValue($selectMock));

        $this->fetchStrategyMock->expects($this->once())
            ->method('fetchAll')
            ->with($selectMock, array())
            ->will($this->returnValue(array($data)));

        $objectMock = $this->getMock(
            'Magento\Framework\Object',
            array('addData', 'setIdFieldName', 'getData'),
            array()
        );
        $objectMock->expects($this->once())
            ->method('addData')
            ->with($data);
        $objectMock->expects($this->any())
            ->method('getData')
            ->will($this->returnValueMap(array(
                array(null, null, 10),
                array('name', null, 'test'),
            )));
        $this->entityFactoryMock->expects($this->once())
            ->method('create')
            ->with('Magento\Framework\Object')
            ->will($this->returnValue($objectMock));

        $this->collection->setConnection($adapterMock);
        $this->collection->loadData(false, false);
        $this->collection->loadData(false, false);
        $this->assertEquals($data, $this->collection->toOptionHash());
    }
}
