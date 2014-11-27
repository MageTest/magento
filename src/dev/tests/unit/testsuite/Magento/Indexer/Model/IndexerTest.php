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
namespace Magento\Indexer\Model;

class IndexerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Indexer\Model\Indexer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    /**
     * @var \Magento\Indexer\Model\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Indexer\Model\ActionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $actionFactoryMock;

    /**
     * @var \Magento\Framework\Mview\ViewInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewMock;

    /**
     * @var \Magento\Indexer\Model\Indexer\StateFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stateFactoryMock;

    /**
     * @var \Magento\Indexer\Model\Indexer\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexFactoryMock;

    protected function setUp()
    {
        $this->configMock = $this->getMockForAbstractClass(
            'Magento\Indexer\Model\ConfigInterface',
            array(),
            '',
            false,
            false,
            true,
            array('getIndexer')
        );
        $this->actionFactoryMock = $this->getMock(
            'Magento\Indexer\Model\ActionFactory',
            array('get'),
            array(),
            '',
            false
        );
        $this->viewMock = $this->getMockForAbstractClass(
            'Magento\Framework\Mview\ViewInterface',
            array(),
            '',
            false,
            false,
            true,
            array('load', 'isEnabled', 'getUpdated', 'getStatus', '__wakeup', 'getId', 'suspend', 'resume')
        );
        $this->stateFactoryMock = $this->getMock(
            'Magento\Indexer\Model\Indexer\StateFactory',
            array('create'),
            array(),
            '',
            false
        );
        $this->indexFactoryMock = $this->getMock(
            'Magento\Indexer\Model\Indexer\CollectionFactory',
            array('create'),
            array(),
            '',
            false
        );
        $this->model = new Indexer(
            $this->configMock,
            $this->actionFactoryMock,
            $this->viewMock,
            $this->stateFactoryMock,
            $this->indexFactoryMock
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage indexer_id indexer does not exist.
     */
    public function testLoadWithException()
    {
        $indexId = 'indexer_id';
        $this->configMock->expects(
            $this->once()
        )->method(
            'getIndexer'
        )->with(
            $indexId
        )->will(
            $this->returnValue($this->getIndexerData())
        );
        $this->model->load($indexId);
    }

    public function testGetView()
    {
        $indexId = 'indexer_internal_name';
        $this->viewMock->expects($this->once())->method('load')->with('view_test')->will($this->returnSelf());
        $this->loadIndexer($indexId);

        $this->assertEquals($this->viewMock, $this->model->getView());
    }

    public function testGetState()
    {
        $indexId = 'indexer_internal_name';
        $stateMock = $this->getMock(
            '\Magento\Indexer\Model\Indexer\State',
            array('loadByIndexer', 'getId', '__wakeup'),
            array(),
            '',
            false
        );
        $stateMock->expects($this->once())->method('loadByIndexer')->with($indexId)->will($this->returnSelf());
        $this->stateFactoryMock->expects($this->once())->method('create')->will($this->returnValue($stateMock));

        $this->loadIndexer($indexId);

        $this->assertInstanceOf('\Magento\Indexer\Model\Indexer\State', $this->model->getState());
    }

    public function testGetLatestUpdated()
    {
        $checkValue = 1;
        $indexId = 'indexer_internal_name';
        $this->loadIndexer($indexId);

        $this->viewMock->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->viewMock->expects($this->once())->method('isEnabled')->will($this->returnValue(true));
        $this->viewMock->expects($this->any())->method('getUpdated')->will($this->returnValue($checkValue));

        $stateMock = $this->getMock(
            '\Magento\Indexer\Model\Indexer\State',
            array('load', 'getId', 'setIndexerId', '__wakeup', 'getUpdated'),
            array(),
            '',
            false
        );
        $stateMock->expects($this->once())->method('getUpdated')->will($this->returnValue(0));
        $this->stateFactoryMock->expects($this->once())->method('create')->will($this->returnValue($stateMock));

        $this->assertEquals($checkValue, $this->model->getLatestUpdated());
    }

    public function testReindexAll()
    {
        $indexId = 'indexer_internal_name';
        $this->loadIndexer($indexId);

        $stateMock = $this->getMock(
            '\Magento\Indexer\Model\Indexer\State',
            array('load', 'getId', 'setIndexerId', '__wakeup', 'getStatus', 'setStatus', 'save'),
            array(),
            '',
            false
        );
        $stateMock->expects($this->once())->method('load')->with($indexId, 'indexer_id')->will($this->returnSelf());
        $stateMock->expects($this->never())->method('setIndexerId');
        $stateMock->expects($this->once())->method('getId')->will($this->returnValue(1));
        $stateMock->expects($this->exactly(2))->method('setStatus')->will($this->returnSelf());
        $stateMock->expects($this->once())->method('getStatus')->will($this->returnValue('idle'));
        $stateMock->expects($this->exactly(2))->method('save')->will($this->returnSelf());
        $this->stateFactoryMock->expects($this->once())->method('create')->will($this->returnValue($stateMock));

        $this->viewMock->expects($this->once())->method('isEnabled')->will($this->returnValue(true));
        $this->viewMock->expects($this->once())->method('suspend');
        $this->viewMock->expects($this->once())->method('resume');

        $actionMock = $this->getMock(
            'Magento\Indexer\Model\ActionInterface',
            array('executeFull', 'executeList', 'executeRow'),
            array(),
            '',
            false
        );
        $this->actionFactoryMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            'Some\Class\Name'
        )->will(
            $this->returnValue($actionMock)
        );

        $this->model->reindexAll();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Test exception
     */
    public function testReindexAllWithException()
    {
        $indexId = 'indexer_internal_name';
        $this->loadIndexer($indexId);

        $stateMock = $this->getMock(
            '\Magento\Indexer\Model\Indexer\State',
            array('load', 'getId', 'setIndexerId', '__wakeup', 'getStatus', 'setStatus', 'save'),
            array(),
            '',
            false
        );
        $stateMock->expects($this->once())->method('load')->with($indexId, 'indexer_id')->will($this->returnSelf());
        $stateMock->expects($this->never())->method('setIndexerId');
        $stateMock->expects($this->once())->method('getId')->will($this->returnValue(1));
        $stateMock->expects($this->exactly(2))->method('setStatus')->will($this->returnSelf());
        $stateMock->expects($this->once())->method('getStatus')->will($this->returnValue('idle'));
        $stateMock->expects($this->exactly(2))->method('save')->will($this->returnSelf());
        $this->stateFactoryMock->expects($this->once())->method('create')->will($this->returnValue($stateMock));

        $this->viewMock->expects($this->once())->method('isEnabled')->will($this->returnValue(false));
        $this->viewMock->expects($this->never())->method('suspend');
        $this->viewMock->expects($this->once())->method('resume');

        $actionMock = $this->getMock(
            'Magento\Indexer\Model\ActionInterface',
            array('executeFull', 'executeList', 'executeRow'),
            array(),
            '',
            false
        );
        $actionMock->expects($this->once())->method('executeFull')->will(
            $this->returnCallback(
                function () {
                    throw new \Exception('Test exception');
                }
            )
        );
        $this->actionFactoryMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            'Some\Class\Name'
        )->will(
            $this->returnValue($actionMock)
        );

        $this->model->reindexAll();
    }

    protected function getIndexerData()
    {
        return array(
            'indexer_id' => 'indexer_internal_name',
            'view_id' => 'view_test',
            'action_class' => 'Some\Class\Name',
            'title' => 'Indexer public name',
            'description' => 'Indexer public description'
        );
    }

    /**
     * @param $indexId
     */
    protected function loadIndexer($indexId)
    {
        $this->configMock->expects(
            $this->once()
        )->method(
            'getIndexer'
        )->with(
            $indexId
        )->will(
            $this->returnValue($this->getIndexerData())
        );
        $this->model->load($indexId);
    }
}
