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
use Magento\Framework\DB\Select;
use Magento\Framework\Search\AdapterInterface;
use Magento\Framework\Search\RequestInterface;
use \Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder as AggregationBuilder;

/**
 * MySQL Search Adapter
 */
class Adapter implements AdapterInterface
{
    /**
     * Mapper instance
     *
     * @var Mapper
     */
    protected $mapper;

    /**
     * Response Factory
     *
     * @var ResponseFactory
     */
    protected $responseFactory;

    /**
     * @var \Magento\Framework\App\Resource
     */
    private $resource;

    /**
     * @var AggregationBuilder
     */
    private $aggregationBuilder;

    /**
     * @param Mapper $mapper
     * @param ResponseFactory $responseFactory
     * @param Resource $resource
     * @param AggregationBuilder $aggregationBuilder
     */
    public function __construct(
        Mapper $mapper,
        ResponseFactory $responseFactory,
        Resource $resource,
        AggregationBuilder $aggregationBuilder
    ) {
        $this->mapper = $mapper;
        $this->responseFactory = $responseFactory;
        $this->resource = $resource;
        $this->aggregationBuilder = $aggregationBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function query(RequestInterface $request)
    {
        /** @var Select $query */
        $query = $this->mapper->buildQuery($request);
        $documents = $this->executeQuery($query);

        $aggregations = $this->aggregationBuilder->build($request, $documents);
        $response = [
            'documents' => $documents,
            'aggregations' => $aggregations,
        ];
        return $this->responseFactory->create($response);
    }

    /**
     * Executes query and return raw response
     *
     * @param Select $select
     * @return array
     */
    private function executeQuery(Select $select)
    {
        return $this->resource->getConnection(Resource::DEFAULT_READ_RESOURCE)->fetchAssoc($select);
    }
}
