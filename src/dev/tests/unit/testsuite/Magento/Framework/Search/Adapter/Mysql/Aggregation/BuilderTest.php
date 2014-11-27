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
namespace Magento\Framework\Search\Adapter\Mysql\Aggregation;

use Magento\TestFramework\Helper\ObjectManager;

class BuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Search\EntityMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityMetadata;

    /**
     * @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $adapter;

    /**
     * @var \Magento\Framework\Search\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\Search\Request\BucketInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $bucket;

    /**
     * @var DataProviderContainer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataProviderContainer;

    /**
     * @var DataProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataProvider;

    /**
     * @var Builder\Container|\PHPUnit_Framework_MockObject_MockObject
     */
    private $aggregationContainer;

    /**
     * @var Builder\BucketInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $bucketBuilder;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    private $select;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder
     */
    private $builder;

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->entityMetadata = $this->getMockBuilder('Magento\Framework\Search\EntityMetadata')
            ->setMethods(['getEntityId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->getMockBuilder('Magento\Framework\Search\RequestInterface')
            ->setMethods(['getAggregation'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->bucket = $this->getMockBuilder('Magento\Framework\Search\Request\BucketInterface')
            ->setMethods(['getName'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->select = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->disableOriginalConstructor()
            ->getMock();

        $this->bucketBuilder = $this->getMockBuilder(
            'Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder\BucketInterface'
        )
            ->setMethods(['build'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->aggregationContainer = $this->getMockBuilder(
            'Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder\Container'
        )
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->aggregationContainer->expects($this->any())->method('get')->willReturn($this->bucketBuilder);

        $this->adapter = $this->getMockBuilder('Magento\Framework\DB\Adapter\AdapterInterface')
            ->setMethods(['fetchAssoc'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->dataProvider = $this->getMockBuilder(
            'Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderInterface'
        )
            ->setMethods(['getDataSet'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->dataProviderContainer = $this->getMockBuilder(
            'Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderContainer'
        )
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataProviderContainer->expects($this->any())->method('get')->willReturn($this->dataProvider);

        $this->resource = $this->getMockBuilder('Magento\Framework\App\Resource')
            ->setMethods(['getConnection'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource->expects($this->once())->method('getConnection')->willReturn($this->adapter);

        $this->builder = $helper->getObject(
            'Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder',
            [
                'entityMetadata' => $this->entityMetadata,
                'dataProviderContainer' => $this->dataProviderContainer,
                'resource' => $this->resource,
                'aggregationContainer' => $this->aggregationContainer
            ]
        );
    }

    public function testBuild()
    {
        $fetchResult = ['name' => ['some', 'result']];
        $documents = [
            [
                'product_id' => 1,
                'sku' => 'Product'
            ]
        ];

        $this->bucket->expects($this->once())->method('getName')->willReturn('name');
        $this->dataProvider->expects($this->once())->method('getDataSet')->willReturn($this->select);
        $this->entityMetadata->expects($this->once())->method('getEntityId')->willReturn('product_id');
        $this->adapter->expects($this->once())->method('fetchAssoc')->willReturn($fetchResult['name']);
        $this->request->expects($this->once())->method('getAggregation')->willReturn([$this->bucket]);
        $this->bucketBuilder->expects($this->once())->method('build')->willReturn($this->select);

        $result = $this->builder->build($this->request, $documents);

        $this->assertEquals($result, $fetchResult);
    }
}
