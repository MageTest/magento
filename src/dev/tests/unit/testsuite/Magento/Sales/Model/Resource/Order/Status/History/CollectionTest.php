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

namespace Magento\Sales\Model\Resource\Order\Status\History;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Resource\Order\Status\History\Collection
     */
    protected $collection;

    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Zend_Db_Select|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectMock;

    /**
     * @var \Magento\Sales\Model\Order\Status\History|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $historyItemMock;

    /**
     * @var \Magento\Framework\Model\Resource\Db\AbstractDb|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Framework\Data\Collection\Db\FetchStrategyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fetchStrategyMock;

    /**
     * @var \Magento\Core\Model\EntityFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityFactoryMock;

    public function setUp()
    {
        $this->eventManagerMock = $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false);
        $this->connectionMock = $this->getMock('Magento\Framework\DB\Adapter\Pdo\Mysql', [], [], '', false);
        $this->selectMock = $this->getMock('Zend_Db_Select', [], [], '', false);
        $this->historyItemMock = $this->getMock(
            'Magento\Sales\Model\Order\Status\History',
            ['__wakeup', 'addData'],
            [],
            '',
            false
        );
        $this->resourceMock = $this->getMockForAbstractClass(
            'Magento\Framework\Model\Resource\Db\AbstractDb',
            [],
            '',
            false,
            true,
            true,
            ['getReadConnection', 'getMainTable', 'getTable', '__wakeup']
        );
        $this->fetchStrategyMock = $this->getMockForAbstractClass(
            'Magento\Framework\Data\Collection\Db\FetchStrategyInterface'
        );
        $this->entityFactoryMock = $this->getMock('Magento\Core\Model\EntityFactory', [], [], '', false);

        $this->resourceMock->expects($this->any())->method('getReadConnection')->will(
            $this->returnValue($this->connectionMock)
        );
        $this->resourceMock->expects($this->any())->method('getTable')->will($this->returnArgument(0));

        $this->connectionMock->expects($this->any())->method('quoteIdentifier')->will($this->returnArgument(0));
        $this->connectionMock->expects($this->atLeastOnce())
            ->method('select')
            ->will($this->returnValue($this->selectMock));

        $data = [['data']];
        $this->historyItemMock->expects($this->once())
            ->method('addData')
            ->with($this->equalTo($data[0]))
            ->will($this->returnValue($this->historyItemMock));

        $this->fetchStrategyMock->expects($this->once())
            ->method('fetchAll')
            ->will($this->returnValue($data));

        $this->entityFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->historyItemMock));

        $logger = $this->getMock('Magento\Framework\Logger', [], [], '', false);
        $this->collection = new \Magento\Sales\Model\Resource\Order\Status\History\Collection(
            $this->entityFactoryMock,
            $logger,
            $this->fetchStrategyMock,
            $this->eventManagerMock,
            $this->connectionMock,
            $this->resourceMock
        );
    }

    public function testGetUnnotifiedForInstance()
    {
        $orderId = 100000512;
        $entityType = 'order';

        $order = $this->getMock('Magento\Sales\Model\Order', ['__wakeup', 'getEntityType', 'getId'], [], '', false);
        $order->expects($this->once())
            ->method('getEntityType')
            ->will($this->returnValue($entityType));
        $order->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($orderId));

        $this->connectionMock = $this->collection->getResource()->getReadConnection();
        $this->connectionMock->expects($this->exactly(3))
            ->method('prepareSqlCondition')
            ->will(
                $this->returnValueMap(
                    [
                        ['entity_name', $entityType, 'sql-string'],
                        ['is_customer_notified', 0, 'sql-string'],
                        ['parent_id', $orderId, 'sql-string']
                    ]
                )
            );
        $result = $this->collection->getUnnotifiedForInstance($order);
        $this->assertEquals($this->historyItemMock, $result);
    }
}
 