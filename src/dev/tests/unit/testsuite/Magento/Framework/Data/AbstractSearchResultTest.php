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
namespace Magento\Framework\Data;

/**
 * Class AbstractSearchResultTest
 */
class AbstractSearchResultTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractSearchResult
     */
    protected $searchResult;

    /**
     * @var \Magento\Framework\DB\QueryInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $query;

    /**
     * @var \Magento\Framework\Data\Collection\EntityFactoryInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityFactory;

    /**
     * @var \Magento\Framework\Api\CriteriaInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $criteria;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultIteratorMock;

    public function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->criteria = $this->getMockForAbstractClass('Magento\Framework\Api\CriteriaInterface');
        $this->query = $this->getMockForAbstractClass('Magento\Framework\DB\QueryInterface');
        $this->query->expects($this->any())
            ->method('getCriteria')
            ->willReturn($this->criteria);
        $this->entityFactory = $this->getMockForAbstractClass(
            'Magento\Framework\Data\Collection\EntityFactoryInterface'
        );
        $this->eventManagerMock = $this->getMockBuilder('Magento\Framework\Event\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchResultIteratorMock = $this->getMockBuilder('Magento\Framework\Data\SearchResultIteratorFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchResult = $objectManager->getObject(
            'Magento\Framework\Data\Stub\SearchResult',
            [
                'query' => $this->query,
                'entityFactory' => $this->entityFactory,
                'eventManager' => $this->eventManagerMock,
                'resultIteratorFactory' => $this->searchResultIteratorMock
            ]
        );
    }

    public function testGetItems()
    {
        $itemData = ['id' => 1];

        $testItem = new \Magento\Framework\Object($itemData);

        $this->query->expects($this->once())
            ->method('fetchAll')
            ->willReturn([$itemData]);
        $this->entityFactory->expects($this->once())
            ->method('create')
            ->with('Magento\Framework\Object', ['data' => $itemData])
            ->willReturn($testItem);

        $items = $this->searchResult->getItems();

        $this->assertCount(1, $items);
        $this->assertEquals($testItem, end($items));
    }

    public function testGetTotalCount()
    {
        $totalCount = 42;

        $this->query->expects($this->once())
            ->method('getSize')
            ->willReturn($totalCount);

        $this->assertEquals($totalCount, $this->searchResult->getTotalCount());
    }

    public function testGetSearchCriteria()
    {
        $this->assertEquals($this->criteria, $this->searchResult->getSearchCriteria());
    }

    public function testGetSize()
    {
        $size = 42;
        $this->query->expects($this->once())
            ->method('getSize')
            ->willReturn($size);
        $this->assertEquals($size, $this->searchResult->getSize());
    }
}
 