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

namespace Magento\Framework\Search\Adapter\Mysql;

use Magento\Framework\App\Resource;
use Magento\Framework\App\Resource\Config;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder\Container as AggregationContainer;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderContainer;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderInterface;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\TestFramework\Helper\ObjectManager;

class AdapterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ResponseFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseFactory;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionAdapter;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Mapper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mapper;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Adapter
     */
    private $adapter;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Search\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    private $select;

    /**
     * @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    /**
     * @var BucketInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $bucket;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $aggregatioBuilder;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->request = $this->getMockBuilder('Magento\Framework\Search\RequestInterface')
            ->setMethods(['getAggregation'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->resource = $this->getMockBuilder('Magento\Framework\App\Resource')
            ->setMethods(['getConnection'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->select = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionAdapter = $this->getMockBuilder('Magento\Framework\DB\Adapter\AdapterInterface')
            ->setMethods(['fetchAssoc'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resource->expects($this->any())
            ->method('getConnection')
            ->with(Resource::DEFAULT_READ_RESOURCE)
            ->will($this->returnValue($this->connectionAdapter));

        $this->mapper = $this->getMockBuilder('\Magento\Framework\Search\Adapter\Mysql\Mapper')
            ->setMethods(['buildQuery'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->responseFactory = $this->getMockBuilder('\Magento\Framework\Search\Adapter\Mysql\ResponseFactory')
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $this->aggregatioBuilder = $this->getMockBuilder('Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder')
            ->setMethods(['build'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->bucket = $this->getMockBuilder('Magento\Framework\Search\Request\BucketInterface')
            ->setMethods(['getType', 'getName'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->adapter = $this->objectManager->getObject(
            '\Magento\Framework\Search\Adapter\Mysql\Adapter',
            [
                'mapper' => $this->mapper,
                'responseFactory' => $this->responseFactory,
                'resource' => $this->resource,
                'aggregationBuilder' => $this->aggregatioBuilder
            ]
        );
    }

    public function testQuery()
    {
        $selectResult = [
            'documents' => [
                [
                    'product_id' => 1,
                    'sku' => 'Product'
                ]
            ],
            'aggregations' => [
                'aggregation_name' => [
                    'aggregation1' => [1, 3],
                    'aggregation2' => [2, 4]
                ]
            ]
        ];

        $this->connectionAdapter->expects($this->at(0))
            ->method('fetchAssoc')
            ->will($this->returnValue($selectResult['documents']));
        $this->mapper->expects($this->once())
            ->method('buildQuery')
            ->with($this->request)
            ->will($this->returnValue($this->select));
        $this->responseFactory->expects($this->once())
            ->method('create')
            ->with($selectResult)
            ->will($this->returnArgument(0));
        $this->aggregatioBuilder->expects($this->once())->method('build')->willReturn($selectResult['aggregations']);
        $response = $this->adapter->query($this->request);
        $this->assertEquals($selectResult, $response);
    }
}
