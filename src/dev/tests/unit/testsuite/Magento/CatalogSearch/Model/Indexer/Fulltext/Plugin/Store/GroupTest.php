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
namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\Store;

class GroupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Indexer\Model\IndexerInterface
     */
    protected $indexerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Model\Resource\Group
     */
    protected $subjectMock;

    /**
     * @var \Magento\Indexer\Model\IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerRegistryMock;

    /**
     * @var Group
     */
    protected $model;

    protected function setUp()
    {
        $this->subjectMock = $this->getMock('Magento\Store\Model\Resource\Group', [], [], '', false);
        $this->indexerMock = $this->getMockForAbstractClass(
            'Magento\Indexer\Model\IndexerInterface',
            [],
            '',
            false,
            false,
            true,
            ['getId', 'getState', '__wakeup']
        );
        $this->indexerRegistryMock = $this->getMock('Magento\Indexer\Model\IndexerRegistry', ['get'], [], '', false);
        $this->model = new Group($this->indexerRegistryMock);
    }

    /**
     * @param bool $isObjectNew
     * @param bool $websiteChanged
     * @param int $invalidateCounter
     * @return void
     * @dataProvider aroundSaveDataProvider
     */
    public function testAroundSave($isObjectNew, $websiteChanged, $invalidateCounter)
    {
        $groupMock = $this->getMock(
            'Magento\Store\Model\Group',
            ['dataHasChangedFor', 'isObjectNew', '__wakeup'],
            [],
            '',
            false
        );
        $groupMock->expects($this->any())
            ->method('dataHasChangedFor')
            ->with('website_id')
            ->will($this->returnValue($websiteChanged));
        $groupMock->expects($this->once())->method('isObjectNew')->will($this->returnValue($isObjectNew));

        $closureMock = function (\Magento\Store\Model\Group $object) use ($groupMock) {
            $this->assertEquals($object, $groupMock);
            return $this->subjectMock;
        };

        $this->indexerMock->expects($this->exactly($invalidateCounter))->method('invalidate');
        $this->prepareIndexer($invalidateCounter);

        $this->assertEquals(
            $this->subjectMock,
            $this->model->aroundSave($this->subjectMock, $closureMock, $groupMock)
        );
    }

    /**
     * @return array
     */
    public function aroundSaveDataProvider()
    {
        return [
            [false, false, 0],
            [false, true, 1],
            [true, false, 0],
            [true, true, 0],
        ];
    }

    /**
     * @return void
     */
    public function testAfterDelete()
    {
        $this->indexerMock->expects($this->once())->method('invalidate');
        $this->prepareIndexer(1);

        $this->assertEquals(
            $this->subjectMock,
            $this->model->afterDelete($this->subjectMock, $this->subjectMock)
        );
    }

    /**
     * @param int $invalidateCounter
     */
    protected function prepareIndexer($invalidateCounter)
    {
        $this->indexerRegistryMock->expects($this->exactly($invalidateCounter))
            ->method('get')
            ->with(\Magento\CatalogSearch\Model\Indexer\Fulltext::INDEXER_ID)
            ->will($this->returnValue($this->indexerMock));
    }
}
