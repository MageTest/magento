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
namespace Magento\Framework\DB;

/**
 * Interface MapperInterface
 */
interface MapperInterface
{
    const SORT_ORDER_ASC = 'ASC';

    const SORT_ORDER_DESC = 'DESC';

    /**
     * Map criteria to Select Query Object
     *
     * @param \Magento\Framework\Api\CriteriaInterface $criteria
     * @return Select
     */
    public function map(\Magento\Framework\Api\CriteriaInterface $criteria);

    /**
     * Get resource instance
     *
     * @return \Magento\Framework\Model\Resource\Db\AbstractDb
     */
    public function getResource();

    /**
     * Add attribute expression (SUM, COUNT, etc)
     * Example: ('sub_total', 'SUM({{attribute}})', 'revenue')
     * Example: ('sub_total', 'SUM({{revenue}})', 'revenue')
     * For some functions like SUM use groupByAttribute.
     *
     * @param string $alias
     * @param string $expression
     * @param array|string $fields
     * @return $this
     */
    public function addExpressionFieldToSelect($alias, $expression, $fields);

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
     * </pre>
     * The above would find where age equal to 42 OR name like %Mage%.
     *
     * @param string|array $field
     * @param string|int|array $condition
     * @throws \Magento\Framework\Exception if some error in the input could be detected.
     * @return void
     */
    public function addFieldToFilter($field, $condition = null);

    /**
     * Reset Select object state
     *
     * @return void
     */
    public function reset();
}
