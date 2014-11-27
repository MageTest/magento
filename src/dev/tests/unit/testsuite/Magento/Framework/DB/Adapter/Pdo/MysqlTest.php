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
 * \Magento\Framework\DB\Adapter\Pdo\Mysql class test
 */
namespace Magento\Framework\DB\Adapter\Pdo;

class MysqlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Custom error handler message
     */
    const CUSTOM_ERROR_HANDLER_MESSAGE = 'Custom error handler message';

    /**
     * Adapter for test
     *
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_adapter;

    /**
     * Mock DB adapter for DDL query tests
     *
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_mockAdapter;

    /**
     * Setup
     */
    protected function setUp()
    {
        $this->_mockAdapter = $this->getMock(
            'Magento\Framework\DB\Adapter\Pdo\Mysql',
            array('beginTransaction', 'getTransactionLevel'),
            array(),
            '',
            false
        );

        $this->_mockAdapter->expects($this->any())
             ->method('getTransactionLevel')
             ->will($this->returnValue(1));

        $this->_adapter = $this->getMock(
            'Magento\Framework\DB\Adapter\Pdo\Mysql',
            array(
                'getCreateTable',
                '_connect',
                '_beginTransaction',
                '_commit',
                '_rollBack',
                'query',
                '_debugWriteToFile',
                'fetchRow'
            ),
            array(
                'dbname' => 'not_exists',
                'username' => 'not_valid',
                'password' => 'not_valid',
            ),
            '',
            false
        );

        $profiler = $this->getMock(
            'Zend_Db_Profiler'
        );

        $resourceProperty = new \ReflectionProperty(
            get_class($this->_adapter),
            '_profiler'
        );
        $resourceProperty->setAccessible(true);
        $resourceProperty->setValue($this->_adapter, $profiler);
    }

    /**
     * @dataProvider bigintResultProvider
     */
    public function testPrepareColumnValueForBigint($value, $expectedResult)
    {
        $result = $this->_adapter->prepareColumnValue(
            array('DATA_TYPE' => 'bigint'),
            $value
        );
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data Provider for testPrepareColumnValueForBigint
     */
    public function bigintResultProvider()
    {
        return array(
            array(1, 1),
            array(0, 0),
            array(-1, -1),
            array(1.0, 1),
            array(0.0, 0),
            array(-1.0, -1),
            array(1e-10, 0),
            array(7.9, 8),
            array(PHP_INT_MAX, PHP_INT_MAX),
            array(2147483647 + 1, '2147483648'),
            array(9223372036854775807 + 1, '9223372036854775808'),
            array(9223372036854775807, '9223372036854775807'),
            array(9223372036854775807.3423424234, '9223372036854775808'),
            array(2147483647 * pow(10, 10)+12, '21474836470000001024'),
            array(9223372036854775807 * pow(10, 10)+12, '92233720368547758080000000000'),
            array((0.099999999999999999999999995+0.2+0.3+0.4+0.5)*10, '15'),
            array('21474836470000000012', '21474836470000001024'),
            array(0x5468792130ABCDEF, '6082244480221302255'),
        );
    }

    /**
     * Test not DDL query inside transaction
     *
     * @dataProvider sqlQueryProvider
     */
    public function testCheckNotDdlTransaction($query)
    {
        try {
            $this->_mockAdapter->query($query);
        } catch (\Exception $e) {
            $this->assertNotContains(
                $e->getMessage(),
                \Magento\Framework\DB\Adapter\AdapterInterface::ERROR_DDL_MESSAGE
            );
        }

        $select = new \Zend_Db_Select($this->_mockAdapter);
        $select->from('user');
        try {
            $this->_mockAdapter->query($select);
        } catch (\Exception $e) {
            $this->assertNotContains(
                $e->getMessage(),
                \Magento\Framework\DB\Adapter\AdapterInterface::ERROR_DDL_MESSAGE
            );
        }
    }

    /**
     * Test DDL query inside transaction in Developer mode
     *
     * @dataProvider ddlSqlQueryProvider
     * @expectedException \Exception
     * @expectedExceptionMessage DDL statements are not allowed in transactions
     */
    public function testCheckDdlTransaction($ddlQuery)
    {
        $this->_mockAdapter->query($ddlQuery);
    }

    /**
     * Data Provider for testCheckDdlTransaction
     */
    public static function ddlSqlQueryProvider()
    {
        return array(
            array('CREATE table user sasdasd'),
            array('ALTER table user'),
            array('TRUNCATE table user'),
            array('RENAME table user'),
            array('DROP table user'),
        );
    }

    /**
     * Data Provider for testCheckNotDdlTransaction
     */
    public static function sqlQueryProvider()
    {
        return array(
            array('SELECT * FROM user'),
            array('UPDATE user'),
            array('DELETE from user'),
            array('INSERT into user'),
        );
    }

    /**
     * Test Asymmetric transaction rollback failure
     */
    public function testAsymmetricRollBackFailure()
    {
        try {
            $this->_adapter->rollBack();
            throw new \Exception('Test Failed!');
        } catch (\Exception $e) {
            $this->assertEquals(
                \Magento\Framework\DB\Adapter\AdapterInterface::ERROR_ASYMMETRIC_ROLLBACK_MESSAGE,
                $e->getMessage()
            );
        }
    }

    /**
     * Test Asymmetric transaction commit failure
     */
    public function testAsymmetricCommitFailure()
    {
        try {
            $this->_adapter->commit();
            throw new \Exception('Test Failed!');
        } catch (\Exception $e) {
            $this->assertEquals(
                \Magento\Framework\DB\Adapter\AdapterInterface::ERROR_ASYMMETRIC_COMMIT_MESSAGE,
                $e->getMessage()
            );
        }
    }

    /**
     * Test Asymmetric transaction commit success
     */
    public function testAsymmetricCommitSuccess()
    {
        $this->assertEquals(0, $this->_adapter->getTransactionLevel());
        $this->_adapter->beginTransaction();
        $this->assertEquals(1, $this->_adapter->getTransactionLevel());
        $this->_adapter->commit();
        $this->assertEquals(0, $this->_adapter->getTransactionLevel());
    }

    /**
     * Test Asymmetric transaction rollback success
     */
    public function testAsymmetricRollBackSuccess()
    {
        $this->assertEquals(0, $this->_adapter->getTransactionLevel());
        $this->_adapter->beginTransaction();
        $this->assertEquals(1, $this->_adapter->getTransactionLevel());
        $this->_adapter->rollBack();
        $this->assertEquals(0, $this->_adapter->getTransactionLevel());
    }

    /**
     * Test successfull nested transaction
     */
    public function testNestedTransactionCommitSuccess()
    {
        $this->_adapter->expects($this->exactly(2))
            ->method('_connect');
        $this->_adapter->expects($this->once())
            ->method('_beginTransaction');
        $this->_adapter->expects($this->once())
            ->method('_commit');

        $this->_adapter->beginTransaction();
        $this->_adapter->beginTransaction();
        $this->_adapter->beginTransaction();
        $this->assertEquals(3, $this->_adapter->getTransactionLevel());
        $this->_adapter->commit();
        $this->_adapter->commit();
        $this->_adapter->commit();
        $this->assertEquals(0, $this->_adapter->getTransactionLevel());
    }

    /**
     * Test successfull nested transaction
     */
    public function testNestedTransactionRollBackSuccess()
    {
        $this->_adapter->expects($this->exactly(2))
            ->method('_connect');
        $this->_adapter->expects($this->once())
            ->method('_beginTransaction');
        $this->_adapter->expects($this->once())
            ->method('_rollBack');

        $this->_adapter->beginTransaction();
        $this->_adapter->beginTransaction();
        $this->_adapter->beginTransaction();
        $this->assertEquals(3, $this->_adapter->getTransactionLevel());
        $this->_adapter->rollBack();
        $this->_adapter->rollBack();
        $this->_adapter->rollBack();
        $this->assertEquals(0, $this->_adapter->getTransactionLevel());
    }

    /**
     * Test successfull nested transaction
     */
    public function testNestedTransactionLastRollBack()
    {
        $this->_adapter->expects($this->exactly(2))
            ->method('_connect');
        $this->_adapter->expects($this->once())
            ->method('_beginTransaction');
        $this->_adapter->expects($this->once())
            ->method('_rollBack');

        $this->_adapter->beginTransaction();
        $this->_adapter->beginTransaction();
        $this->_adapter->beginTransaction();
        $this->assertEquals(3, $this->_adapter->getTransactionLevel());
        $this->_adapter->commit();
        $this->_adapter->commit();
        $this->_adapter->rollBack();
        $this->assertEquals(0, $this->_adapter->getTransactionLevel());
    }

    /**
     * Test incomplete Roll Back in a nested transaction
     */
    public function testIncompleteRollBackFailureOnCommit()
    {
        $this->_adapter->expects($this->exactly(2))
            ->method('_connect');

        try {
            $this->_adapter->beginTransaction();
            $this->_adapter->beginTransaction();
            $this->_adapter->rollBack();
            $this->_adapter->commit();
            throw new \Exception('Test Failed!');
        } catch (\Exception $e) {
            $this->assertEquals(
                \Magento\Framework\DB\Adapter\AdapterInterface::ERROR_ROLLBACK_INCOMPLETE_MESSAGE,
                $e->getMessage()
            );
            $this->_adapter->rollBack();
        }
    }

    /**
     * Test incomplete Roll Back in a nested transaction
     */
    public function testIncompleteRollBackFailureOnBeginTransaction()
    {
        $this->_adapter->expects($this->exactly(2))
            ->method('_connect');

        try {
            $this->_adapter->beginTransaction();
            $this->_adapter->beginTransaction();
            $this->_adapter->rollBack();
            $this->_adapter->beginTransaction();
            throw new \Exception('Test Failed!');
        } catch (\Exception $e) {
            $this->assertEquals(
                \Magento\Framework\DB\Adapter\AdapterInterface::ERROR_ROLLBACK_INCOMPLETE_MESSAGE,
                $e->getMessage()
            );
            $this->_adapter->rollBack();
        }
    }

    /**
     * Test incomplete Roll Back in a nested transaction
     */
    public function testSequentialTransactionsSuccess()
    {
        $this->_adapter->expects($this->exactly(4))
            ->method('_connect');
        $this->_adapter->expects($this->exactly(2))
            ->method('_beginTransaction');
        $this->_adapter->expects($this->once())
            ->method('_rollBack');
        $this->_adapter->expects($this->once())
            ->method('_commit');

        $this->_adapter->beginTransaction();
        $this->_adapter->beginTransaction();
        $this->_adapter->beginTransaction();
        $this->_adapter->rollBack();
        $this->_adapter->rollBack();
        $this->_adapter->rollBack();

        $this->_adapter->beginTransaction();
        $this->_adapter->commit();
    }

    /**
     * Test that column names are quoted in ON DUPLICATE KEY UPDATE section
     */
    public function testInsertOnDuplicateWithQuotedColumnName()
    {
        $table = 'some_table';
        $data = array(
            'index' => 'indexValue',
            'row' => 'rowValue',
            'select' => 'selectValue',
            'insert' => 'insertValue',
        );
        $fields = array('select', 'insert');
        $sqlQuery = "INSERT INTO `some_table` (`index`,`row`,`select`,`insert`) VALUES (?, ?, ?, ?) "
            . "ON DUPLICATE KEY UPDATE `select` = VALUES(`select`), `insert` = VALUES(`insert`)";

        $stmtMock = $this->getMock('Zend_Db_Statement_Pdo', array(), array(), '', false);
        $bind = array('indexValue', 'rowValue', 'selectValue', 'insertValue');
        $this->_adapter->expects($this->once())
            ->method('query')
            ->with($sqlQuery, $bind)
            ->will($this->returnValue($stmtMock));

        $this->_adapter->insertOnDuplicate($table, $data, $fields);
    }

    public function testSelectsByRange()
    {
        $rangeField = 'test_id';
        $tableName = 'test';

        $this->_adapter->expects($this->once())
            ->method('fetchRow')
            ->with(
                $this->_adapter->select()
                    ->from(
                        $tableName,
                        [
                            new \Zend_Db_Expr('MIN(' . $this->_adapter->quoteIdentifier($rangeField) . ') AS min'),
                            new \Zend_Db_Expr('MAX(' . $this->_adapter->quoteIdentifier($rangeField) . ') AS max'),
                        ]
                    )
            )
            ->will($this->returnValue(['min' => 1, 'max' => 200]));
        $this->_adapter->expects($this->any())
            ->method('quote')
            ->will(
                $this->returnCallback(
                    function ($values) {
                        if (!is_array($values)) {
                            $values = [$values];
                        }
                        foreach ($values as &$value) {
                            $value = "'" . $value . "'";
                        }
                        return implode(',', $values);
                    }
                )
            );

        $expectedSelect = $this->_adapter->select()
            ->from($tableName);

        $result = $this->_adapter->selectsByRange($rangeField, $expectedSelect, 50);
        $this->assertCount(200/50, $result);
        $prepareField = $this->_adapter->quoteIdentifier($tableName)
            . '.' . $this->_adapter->quoteIdentifier($rangeField);
        $this->assertEquals(
            $this->_adapter->select()
                ->from($tableName)
                ->where($prepareField . ' >= ?', 1)
                ->where($prepareField . ' < ?', 51),
            $result[0]
        );
        $this->assertEquals(
            $this->_adapter->select()
                ->from($tableName)
                ->where($prepareField . ' >= ?', 51)
                ->where($prepareField . ' < ?', 101),
            $result[1]
        );
        $this->assertEquals(
            $this->_adapter->select()
                ->from($tableName)
                ->where($prepareField . ' >= ?', 101)
                ->where($prepareField . ' < ?', 151),
            $result[2]
        );
        $this->assertEquals(
            $this->_adapter->select()
                ->from($tableName)
                ->where($prepareField . ' >= ?', 151)
                ->where($prepareField . ' < ?', 201),
            $result[3]
        );
    }

    /**
     * @param array $options
     * @param string $expectedQuery
     *
     * @dataProvider addColumnDataProvider
     * @covers \Magento\Framework\DB\Adapter\Pdo\Mysql::addColumn
     * @covers \Magento\Framework\DB\Adapter\Pdo\Mysql::_getColumnDefinition
     */
    public function testAddColumn($options, $expectedQuery)
    {
        $adapter = $this->getMock(
            '\Magento\Framework\DB\Adapter\Pdo\Mysql',
            array('tableColumnExists', '_getTableName', 'rawQuery', 'resetDdlCache', 'quote'), array(), '', false
        );

        $adapter->expects($this->any())->method('_getTableName')->will($this->returnArgument(0));
        $adapter->expects($this->any())->method('quote')->will($this->returnArgument(0));
        $adapter->expects($this->once())->method('rawQuery')->with($expectedQuery);
        $adapter->addColumn('tableName', 'columnName', $options);
    }

    /**
     * @return array
     */
    public function addColumnDataProvider()
    {
        return array(
            array(
                'columnData' => array(
                    'TYPE'        => 'integer',
                    'IDENTITY'    => true,
                    'UNSIGNED'    => true,
                    'NULLABLE'    => false,
                    'DEFAULT'     => null,
                    'COLUMN_NAME' => 'Some field',
                    'COMMENT'     => 'Some field',
                    'AFTER'       => 'Previous field'
                ),
                'expectedQuery' =>
                    'ALTER TABLE `tableName` ADD COLUMN `columnName` int UNSIGNED '
                    . 'NOT NULL default  auto_increment COMMENT Some field AFTER `Previous field` ',
            )
        );
    }
}
