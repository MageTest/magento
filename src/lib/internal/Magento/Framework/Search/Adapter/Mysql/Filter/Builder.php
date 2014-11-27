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
namespace Magento\Framework\Search\Adapter\Mysql\Filter;

use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Framework\Search\Adapter\Mysql\Filter\Builder\FilterInterface;
use Magento\Framework\Search\Adapter\Mysql\Filter\Builder\Range;
use Magento\Framework\Search\Adapter\Mysql\Filter\Builder\Term;
use Magento\Framework\Search\Adapter\Mysql\Filter\Builder\Wildcard;
use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;
use Magento\Framework\Search\Request\Query\Bool;

class Builder implements BuilderInterface
{
    /**
     * @var ConditionManager
     */
    private $conditionManager;

    /**
     * @var FilterInterface[]
     */
    private $filters;

    /**
     * @var PreprocessorInterface
     */
    private $preprocessor;

    /**
     * @param Range $range
     * @param Term $term
     * @param Wildcard $wildcard
     * @param ConditionManager $conditionManager
     * @param PreprocessorInterface $preprocessor
     */
    public function __construct(
        Range $range,
        Term $term,
        Wildcard $wildcard,
        ConditionManager $conditionManager,
        PreprocessorInterface $preprocessor
    ) {
        $this->filters = [
            RequestFilterInterface::TYPE_RANGE => $range,
            RequestFilterInterface::TYPE_TERM => $term,
            RequestFilterInterface::TYPE_WILDCARD => $wildcard
        ];
        $this->conditionManager = $conditionManager;
        $this->preprocessor = $preprocessor;
    }

    /**
     * {@inheritdoc}
     */
    public function build(RequestFilterInterface $filter, $conditionType)
    {
        return $this->processFilter($filter, $this->isNegation($conditionType));
    }

    /**
     * @param RequestFilterInterface $filter
     * @param bool $isNegation
     * @return string
     */
    private function processFilter(RequestFilterInterface $filter, $isNegation)
    {
        if ($filter->getType() == RequestFilterInterface::TYPE_BOOL) {
            $query = $this->processBoolFilter($filter, $isNegation);
            $query = $this->conditionManager->wrapBrackets($query);
        } else {
            if (!isset($this->filters[$filter->getType()])) {
                throw new \InvalidArgumentException('Unknown filter type ' . $filter->getType());
            }
            $query = $this->filters[$filter->getType()]->buildFilter($filter, $isNegation);
            $query = $this->preprocessor->process($filter, $isNegation, $query);
        }

        return $query;
    }

    /**
     * @param RequestFilterInterface|\Magento\Framework\Search\Request\Filter\Bool $filter
     * @param bool $isNegation
     * @return string
     */
    private function processBoolFilter(RequestFilterInterface $filter, $isNegation)
    {
        $must = $this->buildFilters($filter->getMust(), Select::SQL_AND, $isNegation);
        $should = $this->buildFilters($filter->getShould(), Select::SQL_OR, $isNegation);
        $mustNot = $this->buildFilters(
            $filter->getMustNot(),
            Select::SQL_AND,
            !$isNegation
        );

        $queries = [
            $must,
            $this->conditionManager->wrapBrackets($should),
            $this->conditionManager->wrapBrackets($mustNot),
        ];

        return $this->conditionManager->combineQueries($queries, Select::SQL_AND);
    }

    /**
     * @param \Magento\Framework\Search\Request\FilterInterface[] $filters
     * @param string $unionOperator
     * @param bool $isNegation
     * @return string
     */
    private function buildFilters(array $filters, $unionOperator, $isNegation)
    {
        $queries = [];
        foreach ($filters as $filter) {
            $queries[] = $this->processFilter($filter, $isNegation);
        }
        return $this->conditionManager->combineQueries($queries, $unionOperator);
    }

    /**
     * @param string $conditionType
     * @return bool
     */
    private function isNegation($conditionType)
    {
        return Bool::QUERY_CONDITION_NOT === $conditionType;
    }
}
