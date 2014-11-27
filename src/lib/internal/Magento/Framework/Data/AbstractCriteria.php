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

use Magento\Framework\Object;

/**
 * Class AbstractCriteria
 */
abstract class AbstractCriteria implements \Magento\Framework\Api\CriteriaInterface
{
    /**
     * @var array
     */
    protected $data = [
        self::PART_FIELDS => ['list' => []],
        self::PART_FILTERS => ['list' => []],
        self::PART_ORDERS => ['list' => []],
        self::PART_CRITERIA_LIST => ['list' => []],
        self::PART_LIMIT => [1, 0]
    ];

    /**
     * @var string
     */
    protected $mapperInterfaceName;

    /**
     * Get associated Mapper Interface name
     *
     * @throws \Exception
     * @return string
     */
    public function getMapperInterfaceName()
    {
        if (!$this->mapperInterfaceName) {
            throw new \Exception(__("Missed Mapper Interface for Criteria Interface: ") . get_class($this));
        }
        return $this->mapperInterfaceName;
    }

    /**
     * Add field to select
     *
     * @param string|array $field
     * @param string|null $alias
     * @return void
     */
    public function addField($field, $alias = null)
    {
        if ($field === '*') {
            $this->data[self::PART_FIELDS]['list'] = [$field];
        } else {
            if (is_array($field)) {
                foreach ($field as $key => $value) {
                    $this->addField($value, is_string($key) ? $key : null);
                }
            } else {
                if ($alias === null) {
                    $this->data[self::PART_FIELDS]['list'][$field] = $field;
                } else {
                    $this->data[self::PART_FIELDS]['list'][$alias] = $field;
                }
            }
        }
    }

    /**
     * Add field filter to collection
     *
     * If $condition integer or string - exact value will be filtered ('eq' condition)
     *
     * If $condition is array - one of the following structures is expected:
     * <pre>
     * - ["from" => $fromValue, "to" => $toValue]
     * - ["eq" => $equalValue]
     * - ["neq" => $notEqualValue]
     * - ["like" => $likeValue]
     * - ["in" => [$inValues]]
     * - ["nin" => [$notInValues]]
     * - ["notnull" => $valueIsNotNull]
     * - ["null" => $valueIsNull]
     * - ["moreq" => $moreOrEqualValue]
     * - ["gt" => $greaterValue]
     * - ["lt" => $lessValue]
     * - ["gteq" => $greaterOrEqualValue]
     * - ["lteq" => $lessOrEqualValue]
     * - ["finset" => $valueInSet]
     * </pre>
     *
     * If non matched - sequential parallel arrays are expected and OR conditions
     * will be built using above mentioned structure.
     *
     * Example:
     * <pre>
     * $field = ['age', 'name'];
     * $condition = [42, ['like' => 'Mage']];
     * $type = 'or';
     * </pre>
     * The above would find where age equal to 42 OR name like %Mage%.
     *
     * @param string $name
     * @param string|array $field
     * @param string|int|array $condition
     * @param string $type
     * @throws \Exception
     * @return void
     */
    public function addFilter($name, $field, $condition = null, $type = 'and')
    {
        if (isset($this->data[self::PART_FILTERS]['list'][$name])) {
            throw new \Exception(__("Filter already exists in Criteria object: ") . $name);
        }
        $filter = new Object();
        // implements ArrayAccess
        $filter['name'] = $name;
        $filter['field'] = $field;
        $filter['condition'] = $condition;
        $filter['type'] = strtolower($type);
        $this->data[self::PART_FILTERS]['list'][$name] = $filter;
    }

    /**
     * self::setOrder() alias
     *
     * @param string $field
     * @param string $direction
     * @param bool $unShift
     * @return void
     */
    public function addOrder($field, $direction = self::SORT_ORDER_DESC, $unShift = false)
    {
        $direction = strtoupper($direction) == self::SORT_ORDER_ASC ? self::SORT_ORDER_ASC : self::SORT_ORDER_DESC;
        unset($this->data[self::PART_ORDERS]['list'][$field]);
        // avoid ordering by the same field twice
        if ($unShift) {
            $orders = [$field => $direction];
            foreach ($this->data[self::PART_ORDERS]['list'] as $key => $dir) {
                $orders[$key] = $dir;
            }
            $this->data[self::PART_ORDERS]['list'] = $orders;
        } else {
            $this->data[self::PART_ORDERS]['list'][$field] = $direction;
        }
    }

    /**
     * Set Query limit
     *
     * @param int $offset
     * @param int $size
     * @return void
     */
    public function setLimit($offset, $size)
    {
        $this->data[self::PART_LIMIT] = [$offset, $size];
    }

    /**
     * Removes field from select
     *
     * @param string|null $field
     * @param bool $isAlias Alias identifier
     * @return void
     */
    public function removeField($field, $isAlias = false)
    {
        if ($isAlias) {
            if (isset($this->data[self::PART_FIELDS]['list'][$field])) {
                unset($this->data[self::PART_FIELDS]['list'][$field]);
            }
        } else {
            foreach ($this->data[self::PART_FIELDS]['list'] as $key => $value) {
                if ($value === $field) {
                    unset($this->data[self::PART_FIELDS]['list'][$key]);
                    break;
                }
            }
        }
    }

    /**
     * Removes all fields from select
     *
     * @return void
     */
    public function removeAllFields()
    {
        $this->data[self::PART_FIELDS]['list'] = [];
    }

    /**
     * Removes filter by name
     *
     * @param string $name
     * @return void
     */
    public function removeFilter($name)
    {
        if (isset($this->data[self::PART_FILTERS]['list'][$name])) {
            unset($this->data[self::PART_FILTERS]['list'][$name]);
        }
    }

    /**
     * Removes all filters
     *
     * @return void
     */
    public function removeAllFilters()
    {
        $this->data[self::PART_FILTERS]['list'] = [];
    }

    /**
     * Get Criteria objects added to current Composite Criteria
     *
     * @return array
     */
    public function getCriteriaList()
    {
        return $this->data[self::PART_CRITERIA_LIST]['list'];
    }

    /**
     * Get list of filters
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->data[self::PART_FILTERS]['list'];
    }

    /**
     * Get ordering criteria
     *
     * @return array
     */
    public function getOrders()
    {
        return $this->data[self::PART_ORDERS]['list'];
    }

    /**
     * Get limit
     * (['offset', 'page'])
     *
     * @return array
     */
    public function getLimit()
    {
        return $this->data[self::PART_LIMIT];
    }

    /**
     * Retrieve criteria part
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getPart($name, $default = null)
    {
        return isset($this->data[$name]) ? $this->data[$name] : $default;
    }

    /**
     * Return all criteria parts as array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * Reset criteria
     *
     * @return void
     */
    public function reset()
    {
        $this->data = [
            self::PART_FIELDS => ['list' => []],
            self::PART_FILTERS => ['list' => []],
            self::PART_ORDERS => ['list' => []],
            self::PART_CRITERIA_LIST => ['list' => []],
            self::PART_LIMIT => [1, 0]
        ];
    }
}
