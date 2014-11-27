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
namespace Magento\Framework\DB;

/**
 * Class QueryTest
 */
class QueryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectMock;

    /**
     * @var \Magento\Framework\Api\CriteriaInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $criteriaMock;

    /**
     * @var \Magento\Framework\Model\Resource\Db\AbstractDb|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \Zend_Db_Statement_Pdo|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fetchStmtMock;

    /**
     * @var \Magento\Framework\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Framework\Data\Collection\Db\FetchStrategyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fetchStrategyMock;

    /**
     * @var \Magento\Framework\DB\Query
     */
    protected $query;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->selectMock = $this->getMock(
            'Magento\Framework\DB\Select',
            ['reset', 'columns', 'getAdapter'],
            [],
            '',
            false
        );
        $this->criteriaMock = $this->getMockForAbstractClass(
            'Magento\Framework\Api\CriteriaInterface',
            [],
            '',
            false,
            true,
            true,
            []
        );
        $this->resourceMock = $this->getMockForAbstractClass(
            'Magento\Framework\Model\Resource\Db\AbstractDb',
            [],
            '',
            false,
            true,
            true,
            ['getIdFieldName']
        );
        $this->fetchStmtMock = $this->getMock(
            'Zend_Db_Statement_Pdo',
            ['fetch'],
            [],
            '',
            false
        );
        $this->loggerMock = $this->getMock(
            'Magento\Framework\Logger',
            [],
            [],
            '',
            false
        );
        $this->fetchStrategyMock = $this->getMockForAbstractClass(
            'Magento\Framework\Data\Collection\Db\FetchStrategyInterface',
            [],
            '',
            false,
            true,
            true,
            []
        );

        $this->query = $objectManager->getObject(
            'Magento\Framework\DB\Query',
            [
                'select' => $this->selectMock,
                'criteria' => $this->criteriaMock,
                'resource' => $this->resourceMock,
                'fetchStrategy' => $this->fetchStrategyMock
            ]
        );
    }

    /**
     * Run test getAllIds method
     *
     * @return void
     */
    public function testGetAllIds()
    {
        $adapterMock = $this->getMockForAbstractClass(
            'Zend_Db_Adapter_Abstract',
            [],
            '',
            false,
            true,
            true,
            ['fetchCol']
        );
        $this->resourceMock->expects($this->once())
            ->method('getIdFieldName')
            ->will($this->returnValue('return-value'));
        $this->selectMock->expects($this->once())
            ->method('getAdapter')
            ->will($this->returnValue($adapterMock));
        $adapterMock->expects($this->once())
            ->method('fetchCol')
            ->will($this->returnValue('fetch-result'));

        $this->assertEquals('fetch-result', $this->query->getAllIds());
    }

    /**
     * Run test getSize method
     *
     * @return void
     */
    public function testGetSize()
    {
        $adapterMock = $this->getMockForAbstractClass(
            'Zend_Db_Adapter_Abstract',
            [],
            '',
            false,
            true,
            true,
            ['fetchOne']
        );

        $this->selectMock->expects($this->once())
            ->method('columns')
            ->with('COUNT(*)');
        $this->selectMock->expects($this->once())
            ->method('getAdapter')
            ->will($this->returnValue($adapterMock));
        $adapterMock->expects($this->once())
            ->method('fetchOne')
            ->will($this->returnValue(10.689));

        $this->assertEquals(10, $this->query->getSize());
    }

    /**
     * Run test fetchAll method
     *
     * @return void
     */
    public function testFetchAll()
    {
        $this->fetchStrategyMock->expects($this->once())
            ->method('fetchAll')
            ->will($this->returnValue('return-value'));

        $this->assertEquals('return-value', $this->query->fetchAll());
    }

    /**
     * Run test fetchItem method
     *
     * @return void
     */
    public function testFetchItem()
    {
        $adapterMock = $this->getMockForAbstractClass(
            'Zend_Db_Adapter_Abstract',
            [],
            '',
            false,
            true,
            true,
            ['query']
        );
        $this->selectMock->expects($this->once())
            ->method('getAdapter')
            ->will($this->returnValue($adapterMock));
        $adapterMock->expects($this->once())
            ->method('query')
            ->will($this->returnValue($this->fetchStmtMock));
        $this->fetchStmtMock->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue(null));

        $this->assertEquals([], $this->query->fetchItem());
    }
}
