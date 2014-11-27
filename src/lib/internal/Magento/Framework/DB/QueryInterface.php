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
 * Class QueryInterface
 */
interface QueryInterface
{
    /**
     * Retrieve source Criteria object
     *
     * @return \Magento\Framework\Api\CriteriaInterface
     */
    public function getCriteria();

    /**
     * Retrieve all ids for query
     *
     * @return array
     */
    public function getAllIds();

    /**
     * Add variable to bind list
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function addBindParam($name, $value);

    /**
     * Get collection size
     *
     * @return int
     */
    public function getSize();

    /**
     * Get sql select string or object
     *
     * @param bool $stringMode
     * @return string || Select
     */
    public function getSelectSql($stringMode = false);

    /**
     * Reset Statement object
     *
     * @return void
     */
    public function reset();

    /**
     * Fetch all statement
     *
     * @return array
     */
    public function fetchAll();

    /**
     * Fetch statement
     *
     * @return mixed
     */
    public function fetchItem();

    /**
     * Get Identity Field Name
     *
     * @return string
     */
    public function getIdFieldName();

    /**
     * Retrieve connection object
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function getConnection();

    /**
     * Get resource instance
     *
     * @return \Magento\Framework\Model\Resource\Db\AbstractDb
     */
    public function getResource();

    /**
     * Add Select Part to skip from count query
     *
     * @param string $name
     * @param bool $toSkip
     * @return void
     */
    public function addCountSqlSkipPart($name, $toSkip = true);
}
