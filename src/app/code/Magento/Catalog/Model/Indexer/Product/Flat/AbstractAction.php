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
namespace Magento\Catalog\Model\Indexer\Product\Flat;

/**
 * Abstract action reindex class
 *
 */
abstract class AbstractAction
{
    /**
     * Suffix for value field on composite attributes
     *
     * @var string
     */
    protected $_valueFieldSuffix = '_value';

    /**
     * Suffix for drop table (uses on flat table rename)
     *
     * @var string
     */
    protected $_tableDropSuffix = '_drop_indexer';

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Helper\Product\Flat\Indexer
     */
    protected $_productIndexerHelper;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_productType;

    /**
     * Existing flat tables flags pool
     *
     * @var array
     */
    protected $_flatTablesExist = array();

    /**
     * List of product types available in installation
     *
     * @var array
     */
    protected $_productTypes = array();

    /**
     * @var TableBuilder
     */
    protected $_tableBuilder;

    /**
     * @var FlatTableBuilder
     */
    protected $_flatTableBuilder;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Helper\Product\Flat\Indexer $productHelper
     * @param \Magento\Catalog\Model\Product\Type $productType
     * @param TableBuilder $tableBuilder
     * @param FlatTableBuilder $flatTableBuilder
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Product\Flat\Indexer $productHelper,
        \Magento\Catalog\Model\Product\Type $productType,
        TableBuilder $tableBuilder,
        FlatTableBuilder $flatTableBuilder
    ) {
        $this->_storeManager = $storeManager;
        $this->_productIndexerHelper = $productHelper;
        $this->_productType = $productType;
        $this->_connection = $resource->getConnection('default');
        $this->_tableBuilder = $tableBuilder;
        $this->_flatTableBuilder = $flatTableBuilder;
    }

    /**
     * Execute action for given ids
     *
     * @param array|int $ids
     * @return \Magento\Catalog\Model\Indexer\Product\Flat\AbstractAction
     */
    abstract public function execute($ids);

    /**
     * Return temporary table name by regular table name
     *
     * @param string $tableName
     * @return string
     */
    protected function _getTemporaryTableName($tableName)
    {
        return sprintf('%s_tmp_indexer', $tableName);
    }

    /**
     * Drop temporary tables created by reindex process
     *
     * @param array $tablesList
     * @param int|string $storeId
     * @return void
     */
    protected function _cleanOnFailure(array $tablesList, $storeId)
    {
        foreach ($tablesList as $table => $columns) {
            $this->_connection->dropTemporaryTable($table);
        }
        $tableName = $this->_getTemporaryTableName($this->_productIndexerHelper->getFlatTableName($storeId));
        $this->_connection->dropTable($tableName);
    }

    /**
     * Rebuild catalog flat index from scratch
     *
     * @param int $storeId
     * @param array $changedIds
     * @return void
     * @throws \Exception
     */
    protected function _reindex($storeId, array $changedIds = array())
    {
        try {
            $this->_tableBuilder->build($storeId, $changedIds, $this->_valueFieldSuffix);
            $this->_flatTableBuilder->build(
                $storeId,
                $changedIds,
                $this->_valueFieldSuffix,
                $this->_tableDropSuffix,
                true
            );

            $this->_updateRelationProducts($storeId, $changedIds);
            $this->_cleanRelationProducts($storeId);
        } catch (\Exception $e) {
            $attributes = $this->_productIndexerHelper->getAttributes();
            $eavAttributes = $this->_productIndexerHelper->getTablesStructure($attributes);
            $this->_cleanOnFailure($eavAttributes, $storeId);
            throw $e;
        }
    }

    /**
     * Retrieve Product Type Instances
     * as key - type code, value - instance model
     *
     * @return array
     */
    protected function _getProductTypeInstances()
    {
        if ($this->_productTypes === null) {
            $this->_productTypes = array();
            $productEmulator = new \Magento\Framework\Object();
            foreach (array_keys($this->_productType->getTypes()) as $typeId) {
                $productEmulator->setTypeId($typeId);
                $this->_productTypes[$typeId] = $this->_productType->factory($productEmulator);
            }
        }
        return $this->_productTypes;
    }

    /**
     * Update relation products
     *
     * @param int $storeId
     * @param int|array $productIds Update child product(s) only
     * @return \Magento\Catalog\Model\Indexer\Product\Flat\AbstractAction
     */
    protected function _updateRelationProducts($storeId, $productIds = null)
    {
        if (!$this->_productIndexerHelper->isAddChildData() || !$this->_isFlatTableExists($storeId)) {
            return $this;
        }

        foreach ($this->_getProductTypeInstances() as $typeInstance) {
            /** @var $typeInstance \Magento\Catalog\Model\Product\Type\AbstractType */
            if (!$typeInstance->isComposite(null)) {
                continue;
            }
            $relation = $typeInstance->getRelationInfo();
            if ($relation && $relation->getTable() && $relation->getParentFieldName() && $relation->getChildFieldName()
            ) {
                $columns = $this->_productIndexerHelper->getFlatColumns();
                $fieldList = array_keys($columns);
                unset($columns['entity_id']);
                unset($columns['child_id']);
                unset($columns['is_child']);
                /** @var $select \Magento\Framework\DB\Select */
                $select = $this->_connection->select()->from(
                    array('t' => $this->_productIndexerHelper->getTable($relation->getTable())),
                    array($relation->getParentFieldName(), $relation->getChildFieldName(), new \Zend_Db_Expr('1'))
                )->join(
                    array('e' => $this->_productIndexerHelper->getFlatTableName($storeId)),
                    "e.entity_id = t.{$relation->getChildFieldName()}",
                    array_keys($columns)
                );
                if ($relation->getWhere() !== null) {
                    $select->where($relation->getWhere());
                }
                if ($productIds !== null) {
                    $cond = array(
                        $this->_connection->quoteInto("{$relation->getChildFieldName()} IN(?)", $productIds),
                        $this->_connection->quoteInto("{$relation->getParentFieldName()} IN(?)", $productIds)
                    );

                    $select->where(implode(' OR ', $cond));
                }
                $sql = $select->insertFromSelect($this->_productIndexerHelper->getFlatTableName($storeId), $fieldList);
                $this->_connection->query($sql);
            }
        }

        return $this;
    }

    /**
     * Clean unused relation products
     *
     * @param int $storeId
     * @return \Magento\Catalog\Model\Indexer\Product\Flat\AbstractAction
     */
    protected function _cleanRelationProducts($storeId)
    {
        if (!$this->_productIndexerHelper->isAddChildData()) {
            return $this;
        }

        foreach ($this->_getProductTypeInstances() as $typeInstance) {
            /** @var $typeInstance \Magento\Catalog\Model\Product\Type\AbstractType */
            if (!$typeInstance->isComposite(null)) {
                continue;
            }
            $relation = $typeInstance->getRelationInfo();
            if ($relation && $relation->getTable() && $relation->getParentFieldName() && $relation->getChildFieldName()
            ) {
                $select = $this->_connection->select()->distinct(
                    true
                )->from(
                    $this->_productIndexerHelper->getTable($relation->getTable()),
                    "{$relation->getParentFieldName()}"
                );
                $joinLeftCond = array(
                    "e.entity_id = t.{$relation->getParentFieldName()}",
                    "e.child_id = t.{$relation->getChildFieldName()}"
                );
                if ($relation->getWhere() !== null) {
                    $select->where($relation->getWhere());
                    $joinLeftCond[] = $relation->getWhere();
                }

                $entitySelect = new \Zend_Db_Expr($select->__toString());
                /** @var $select \Magento\Framework\DB\Select */
                $select = $this->_connection->select()->from(
                    array('e' => $this->_productIndexerHelper->getFlatTableName($storeId)),
                    null
                )->joinLeft(
                    array('t' => $this->_productIndexerHelper->getTable($relation->getTable())),
                    implode(' AND ', $joinLeftCond),
                    array()
                )->where(
                    'e.is_child = ?',
                    1
                )->where(
                    'e.entity_id IN(?)',
                    $entitySelect
                )->where(
                    "t.{$relation->getChildFieldName()} IS NULL"
                );

                $sql = $select->deleteFromSelect('e');
                $this->_connection->query($sql);
            }
        }

        return $this;
    }

    /**
     * Check is flat table for store exists
     *
     * @param int $storeId
     * @return bool
     */
    protected function _isFlatTableExists($storeId)
    {
        if (!isset($this->_flatTablesExist[$storeId])) {
            $tableName = $this->_productIndexerHelper->getFlatTableName($storeId);
            $isTableExists = $this->_connection->isTableExists($tableName);

            $this->_flatTablesExist[$storeId] = $isTableExists ? true : false;
        }

        return $this->_flatTablesExist[$storeId];
    }
}
