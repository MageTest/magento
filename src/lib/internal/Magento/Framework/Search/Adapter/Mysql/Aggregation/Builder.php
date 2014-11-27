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

use Magento\Framework\App\Resource;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder\Container as AggregationContainer;
use Magento\Framework\Search\EntityMetadata;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\DB\Select;

class Builder
{
    /**
     * @var DataProviderContainer
     */
    private $dataProviderContainer;

    /**
     * @var Builder\Container
     */
    private $aggregationContainer;

    /**
     * @var EntityMetadata
     */
    private $entityMetadata;

    /**
     * @var Resource
     */
    private $resource;

    /**
     * @param Resource $resource
     * @param DataProviderContainer $dataProviderContainer
     * @param Builder\Container $aggregationContainer
     * @param EntityMetadata $entityMetadata
     */
    public function __construct(
        Resource $resource,
        DataProviderContainer $dataProviderContainer,
        AggregationContainer $aggregationContainer,
        EntityMetadata $entityMetadata
    ) {
        $this->dataProviderContainer = $dataProviderContainer;
        $this->aggregationContainer = $aggregationContainer;
        $this->entityMetadata = $entityMetadata;
        $this->resource = $resource;
    }

    /**
     * @param RequestInterface $request
     * @param int[] $documents
     * @return array
     */
    public function build(RequestInterface $request, array $documents)
    {
        $entityIds = $this->getEntityIds($documents);

        return $this->processAggregations($request, $entityIds);
    }

    /**
     * @param array $documents
     * @return int[]
     */
    private function getEntityIds($documents)
    {
        $fieldName = $this->entityMetadata->getEntityId();
        $entityIds = [];
        foreach ($documents as $document) {
            $entityIds[] = $document[$fieldName];
        }
        return $entityIds;
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

    /**
     * @param RequestInterface $request
     * @param int[] $entityIds
     * @return array
     */
    private function processAggregations(RequestInterface $request, array $entityIds)
    {
        $aggregations = [];
        $buckets = $request->getAggregation();
        $dataProvider = $this->dataProviderContainer->get($request->getIndex());
        foreach ($buckets as $bucket) {
            $aggregationBuilder = $this->aggregationContainer->get($bucket->getType());

            $select = $dataProvider->getDataSet($bucket, $request);
            $select = $aggregationBuilder->build($select, $bucket, $entityIds);
            $aggregations[$bucket->getName()] = $this->executeQuery($select);
        }
        return $aggregations;
    }
}
