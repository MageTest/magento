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
namespace Magento\Store\Model\Resource;

/**
 * Store Resource Model
 */
class Store extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Define main table and primary key
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('store', 'store_id');
    }

    /**
     * Count number of all entities in the system
     *
     * By default won't count admin store
     *
     * @param bool $countAdmin
     * @return int
     */
    public function countAll($countAdmin = false)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()->from($this->getMainTable(), 'COUNT(*)');
        if (!$countAdmin) {
            $select->where(sprintf('%s <> %s', $adapter->quoteIdentifier('code'), $adapter->quote('admin')));
        }
        return (int)$adapter->fetchOne($select);
    }

    /**
     * Initialize unique fields
     *
     * @return $this
     */
    protected function _initUniqueFields()
    {
        $this->_uniqueFields = array(array('field' => 'code', 'title' => __('Store with the same code')));
        return $this;
    }

    /**
     * Update Store Group data after save store
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_afterSave($object);
        $this->_updateGroupDefaultStore($object->getGroupId(), $object->getId());
        $this->_changeGroup($object);

        return $this;
    }

    /**
     * Remove configuration data after delete store
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return $this
     */
    protected function _afterDelete(\Magento\Framework\Model\AbstractModel $model)
    {
        $where = array(
            'scope = ?' => \Magento\Store\Model\ScopeInterface::SCOPE_STORES,
            'scope_id = ?' => $model->getStoreId()
        );

        $this->_getWriteAdapter()->delete($this->getTable('core_config_data'), $where);
        return $this;
    }

    /**
     * Update Default store for Store Group
     *
     * @param int $groupId
     * @param int $storeId
     * @return $this
     */
    protected function _updateGroupDefaultStore($groupId, $storeId)
    {
        $adapter = $this->_getWriteAdapter();

        $bindValues = array('group_id' => (int)$groupId);
        $select = $adapter->select()->from(
            $this->getMainTable(),
            array('count' => 'COUNT(*)')
        )->where(
            'group_id = :group_id'
        );
        $count = $adapter->fetchOne($select, $bindValues);

        if ($count == 1) {
            $bind = array('default_store_id' => (int)$storeId);
            $where = array('group_id = ?' => (int)$groupId);
            $adapter->update($this->getTable('store_group'), $bind, $where);
        }

        return $this;
    }

    /**
     * Change store group for store
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return $this
     */
    protected function _changeGroup(\Magento\Framework\Model\AbstractModel $model)
    {
        if ($model->getOriginalGroupId() && $model->getGroupId() != $model->getOriginalGroupId()) {
            $adapter = $this->_getReadAdapter();
            $select = $adapter->select()->from(
                $this->getTable('store_group'),
                'default_store_id'
            )->where(
                $adapter->quoteInto('group_id=?', $model->getOriginalGroupId())
            );
            $storeId = $adapter->fetchOne($select, 'default_store_id');

            if ($storeId == $model->getId()) {
                $bind = array('default_store_id' => \Magento\Store\Model\Store::DEFAULT_STORE_ID);
                $where = array('group_id = ?' => $model->getOriginalGroupId());
                $this->_getWriteAdapter()->update($this->getTable('store_group'), $bind, $where);
            }
        }
        return $this;
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\DB\Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $select->order('sort_order');
        return $select;
    }
}
