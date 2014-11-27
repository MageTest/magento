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
namespace Magento\Framework\DB\Ddl;

class TriggerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\DB\Ddl\Trigger
     */
    protected $_object;

    public function setUp()
    {
        $this->_object = new \Magento\Framework\DB\Ddl\Trigger();
    }

    /**
     * Test getListOfEvents() method
     */
    public function testGetListOfEvents()
    {
        $actualEventTypes = \Magento\Framework\DB\Ddl\Trigger::getListOfEvents();
        $this->assertInternalType('array', $actualEventTypes);
        $this->assertCount(3, $actualEventTypes);
        $this->assertTrue(in_array(\Magento\Framework\DB\Ddl\Trigger::EVENT_INSERT, $actualEventTypes));
        $this->assertTrue(in_array(\Magento\Framework\DB\Ddl\Trigger::EVENT_UPDATE, $actualEventTypes));
        $this->assertTrue(in_array(\Magento\Framework\DB\Ddl\Trigger::EVENT_DELETE, $actualEventTypes));
    }

    /**
     * Test getListOfTimes() method
     */
    public function testGetListOfTimes()
    {
        $actualTimeTypes = \Magento\Framework\DB\Ddl\Trigger::getListOfTimes();
        $this->assertInternalType('array', $actualTimeTypes);
        $this->assertCount(2, $actualTimeTypes);
        $this->assertTrue(in_array(\Magento\Framework\DB\Ddl\Trigger::TIME_AFTER, $actualTimeTypes));
        $this->assertTrue(in_array(\Magento\Framework\DB\Ddl\Trigger::TIME_BEFORE, $actualTimeTypes));
    }

    /**
     * Test case for getName() after setName()
     */
    public function testGetNameWithSetName()
    {
        $triggerName = 'TEST_TRIGGER_NAME' . mt_rand(100, 999);

        $this->_object->setName($triggerName);
        $this->assertEquals(strtolower($triggerName), $this->_object->getName());
    }

    /**
     * Test case for setName() with exception
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Trigger name should be a string
     */
    public function testSetNameWithException()
    {
        $triggerName = new \stdClass();
        //non string

        $this->_object->setName($triggerName);
    }

    /**
     * Test case for setTable() with exception
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Trigger table name should be a string
     */
    public function testSetTableWithException()
    {
        $tableName = new \stdClass();
        //non string

        $this->_object->setTable($tableName);
    }

    /**
     * Test case for getName()
     *
     * @expectedException \Zend_Db_Exception
     * @expectedExceptionMessage Trigger name is not defined
     */
    public function testGetNameWithException()
    {
        $tableName = 'TEST_TABLE_NAME_' . mt_rand(100, 999);
        $event = \Magento\Framework\DB\Ddl\Trigger::EVENT_INSERT;

        $this->_object->setTable($tableName)->setTime(\Magento\Framework\DB\Ddl\Trigger::TIME_AFTER)->setEvent($event);

        $this->_object->getName();
    }

    /**
     * Test case for getTime() with Exception
     *
     * @expectedException \Zend_Db_Exception
     * @expectedExceptionMessage Trigger time is not defined
     */
    public function testGetTimeWithException()
    {
        $tableName = 'TEST_TABLE_NAME_' . mt_rand(100, 999);
        $event = \Magento\Framework\DB\Ddl\Trigger::EVENT_INSERT;

        $this->_object->setTable($tableName)->setEvent($event);

        $this->_object->getTime();
    }

    /**
     * Test case for getTable()
     *
     * @expectedException \Zend_Db_Exception
     * @expectedExceptionMessage Trigger table name is not defined
     */
    public function testGetTableWithException()
    {
        $event = \Magento\Framework\DB\Ddl\Trigger::EVENT_INSERT;

        $this->_object->setTime(\Magento\Framework\DB\Ddl\Trigger::TIME_AFTER)->setEvent($event);

        $this->_object->getTable();
    }

    /**
     * Test case for getEvent() with Exception
     *
     * @expectedException \Zend_Db_Exception
     * @expectedExceptionMessage Trigger event is not defined
     */
    public function testGetEventWithException()
    {
        $tableName = 'TEST_TABLE_NAME_' . mt_rand(100, 999);

        $this->_object->setTable($tableName)->setTime(\Magento\Framework\DB\Ddl\Trigger::TIME_AFTER);

        $this->_object->getEvent();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Trigger unsupported event type
     */
    public function testWrongEventTypeException()
    {
        $this->_object->setEvent('UNSUPORT EVENT TYPE');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Trigger unsupported time type
     */
    public function testWrongTimeTypeException()
    {
        $this->_object->setTime('UNSUPORT TIME TYPE');
    }

    /**
     * Test case for setTable() with exception
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Trigger statement should be a string
     */
    public function testAddStatementWithException()
    {
        $statement = new \stdClass();
        //non string

        $this->_object->addStatement($statement);
    }

    /**
     * Data provider for setBody function
     */
    public function getStatementsDataProvider()
    {
        return array(array('SQL', array('SQL;')), array('SQL;', array('SQL;')));
    }

    /**
     * Test addStatement and getStatements for trigger
     *
     * @dataProvider getStatementsDataProvider
     */
    public function testAddStatement($param, $expected)
    {
        $this->_object->addStatement($param);
        $this->assertEquals($expected, $this->_object->getStatements());
    }

    /**
     * Test getStatements method
     */
    public function testGetStatements()
    {
        $this->assertEquals(array(), $this->_object->getStatements());
    }
}
