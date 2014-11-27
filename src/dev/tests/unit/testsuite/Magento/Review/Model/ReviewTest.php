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

namespace Magento\Review\Model;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class ReviewTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Review\Model\Review */
    protected $review;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\Model\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $contextMock;

    /** @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject */
    protected $registryMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $productFactoryMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $statusFactoryMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $reviewSummaryMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $summaryModMock;

    /** @var \Magento\Review\Model\Review\Summary|\PHPUnit_Framework_MockObject_MockObject */
    protected $summaryMock;

    /** @var \Magento\Framework\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManagerMock;

    /** @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlInterfaceMock;

    /** @var \Magento\Review\Model\Resource\Review|\PHPUnit_Framework_MockObject_MockObject */
    protected $resource;

    /** @var int  */
    protected $reviewId = 8;

    protected function setUp()
    {
        $this->contextMock = $this->getMock('Magento\Framework\Model\Context', [], [], '', false);
        $this->registryMock = $this->getMock('Magento\Framework\Registry');
        $this->productFactoryMock = $this->getMock(
            'Magento\Review\Model\Resource\Review\Product\CollectionFactory',
            ['create']
        );
        $this->statusFactoryMock = $this->getMock(
            'Magento\Review\Model\Resource\Review\Status\CollectionFactory',
            ['create']
        );
        $this->reviewSummaryMock = $this->getMock('Magento\Review\Model\Resource\Review\Summary\CollectionFactory');
        $this->summaryModMock = $this->getMock('Magento\Review\Model\Review\SummaryFactory', ['create']);
        $this->summaryMock = $this->getMock('Magento\Review\Model\Review\Summary', [], [], '', false);
        $this->storeManagerMock = $this->getMock('Magento\Framework\StoreManagerInterface');
        $this->urlInterfaceMock = $this->getMock('Magento\Framework\UrlInterface');
        $this->resource = $this->getMock('Magento\Review\Model\Resource\Review', [], [], '', false);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->review = $this->objectManagerHelper->getObject(
            'Magento\Review\Model\Review',
            [
                'context' => $this->contextMock,
                'registry' => $this->registryMock,
                'productFactory' => $this->productFactoryMock,
                'statusFactory' => $this->statusFactoryMock,
                'summaryFactory' => $this->reviewSummaryMock,
                'summaryModFactory' => $this->summaryModMock,
                'reviewSummary' => $this->summaryMock,
                'storeManager' => $this->storeManagerMock,
                'urlModel' => $this->urlInterfaceMock,
                'resource' => $this->resource,
                'data' => array('review_id' => $this->reviewId, 'status_id' => 1, 'stores' => [2, 3, 4])
            ]
        );
    }

    public function testGetProductCollection()
    {
        $collection = $this->getMock('Magento\Review\Model\Resource\Review\Product\Collection', [], [], '', false);
        $this->productFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($collection));
        $this->assertSame($collection, $this->review->getProductCollection());
    }

    public function testGetStatusCollection()
    {
        $collection = $this->getMock('Magento\Review\Model\Resource\Review\Status\Collection', [], [], '', false);
        $this->statusFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($collection));
        $this->assertSame($collection, $this->review->getStatusCollection());
    }

    public function testGetTotalReviews()
    {
        $primaryKey = 'review_id';
        $approvedOnly = false;
        $storeId = 0;
        $result = 5;
        $this->resource->expects($this->once())->method('getTotalReviews')
            ->with($this->equalTo($primaryKey), $this->equalTo($approvedOnly), $this->equalTo($storeId))
            ->will($this->returnValue($result));
        $this->assertSame($result, $this->review->getTotalReviews($primaryKey, $approvedOnly, $storeId));
    }

    public function testAggregate()
    {
        $this->resource->expects($this->once())->method('aggregate')
            ->with($this->equalTo($this->review))
            ->will($this->returnValue($this->review));
        $this->assertSame($this->review, $this->review->aggregate());
    }

    public function testGetEntitySummary()
    {
        $productId = 6;
        $storeId = 4;
        $testSummaryData = ['test' => 'value'];
        $summary = new \Magento\Framework\Object();
        $summary->setData($testSummaryData);

        $product = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['getId', 'setRatingSummary', '__wakeup'],
            [],
            '',
            false
        );
        $product->expects($this->once())->method('getId')->will($this->returnValue($productId));
        $product->expects($this->once())->method('setRatingSummary')->with($summary)->will($this->returnSelf());

        $summaryData = $this->getMock(
            'Magento\Review\Model\Review\Summary',
            ['load', 'getData', 'setStoreId', '__wakeup'],
            [],
            '',
            false
        );
        $summaryData->expects($this->once())->method('setStoreId')
            ->with($this->equalTo($storeId))
            ->will($this->returnSelf());
        $summaryData->expects($this->once())->method('load')
            ->with($this->equalTo($productId))
            ->will($this->returnSelf());
        $summaryData->expects($this->once())->method('getData')->will($this->returnValue($testSummaryData));
        $this->summaryModMock->expects($this->once())->method('create')->will($this->returnValue($summaryData));
        $this->assertNull($this->review->getEntitySummary($product, $storeId));
    }

    public function testGetPendingStatus()
    {
        $this->assertSame(Review::STATUS_PENDING, $this->review->getPendingStatus());
    }

    public function testGetReviewUrl()
    {
        $result = 'http://some.url';
        $this->urlInterfaceMock->expects($this->once())->method('getUrl')
            ->with($this->equalTo('review/product/view'), $this->equalTo(array('id' => $this->reviewId)))
            ->will($this->returnValue($result));
        $this->assertSame($result, $this->review->getReviewUrl());
    }

    /**
     * @param int $productId
     * @param int $storeId
     * @param string $result
     * @dataProvider getProductUrlDataProvider
     */
    public function testGetProductUrl($productId, $storeId, $result)
    {
        if ($storeId) {
            $this->urlInterfaceMock->expects($this->once())->method('setScope')
                ->with($this->equalTo($storeId))
                ->will($this->returnSelf());
        }

        $this->urlInterfaceMock->expects($this->once())->method('getUrl')
            ->with($this->equalTo('catalog/product/view'), $this->equalTo(array('id' => $productId)))
            ->will($this->returnValue($result));
        $this->assertSame($result, $this->review->getProductUrl($productId, $storeId));
    }

    /**
     * @return array
     */
    public function getProductUrlDataProvider()
    {
        return [
            'store id specified' => [3, 5, 'http://some.url'],
            'store id is not specified' => [3, null, 'http://some.url/2/'],
        ];
    }

    public function testIsApproved()
    {
        $this->assertTrue($this->review->isApproved());
    }

    /**
     * @param int|null $storeId
     * @param bool $result
     * @dataProvider isAvailableOnStoreDataProvider
     */
    public function testIsAvailableOnStore($storeId, $result)
    {
        $store = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        if ($storeId) {
            $store->expects($this->once())->method('getId')->will($this->returnValue($storeId));
            $this->storeManagerMock->expects($this->once())
                ->method('getStore')
                ->with($this->equalTo($store))
                ->will($this->returnValue($store));
        }
        $this->assertSame($result, $this->review->isAvailableOnStore($store));
    }

    /**
     * @return array
     */
    public function isAvailableOnStoreDataProvider()
    {
        return [
            'store id is set and not in list' => [1, false],
            'store id is set' => [3, true],
            'store id is not set' => [null, false],
        ];
    }

    public function testGetEntityIdByCode()
    {
        $entityCode = 'test';
        $result = 22;
        $this->resource->expects($this->once())->method('getEntityIdByCode')
            ->with($this->equalTo($entityCode))
            ->will($this->returnValue($result));
        $this->assertSame($result, $this->review->getEntityIdByCode($entityCode));
    }
}
