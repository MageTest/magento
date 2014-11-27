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
namespace Magento\Sales\Model\Resource\Collection;

/**
 * Flat sales abstract collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class AbstractCollection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * @var \Zend_Db_Select
     */
    protected $_countSelect;

    /**
     * Set select count sql
     *
     * @param \Zend_Db_Select $countSelect
     * @return $this
     */
    public function setSelectCountSql(\Zend_Db_Select $countSelect)
    {
        $this->_countSelect = $countSelect;
        return $this;
    }

    /**
     * get select count sql
     *
     * @return \Zend_Db_Select
     */
    public function getSelectCountSql()
    {
        if (!$this->_countSelect instanceof \Zend_Db_Select) {
            $this->setSelectCountSql(parent::getSelectCountSql());
        }
        return $this->_countSelect;
    }

    /**
     * Check if $attribute is \Magento\Eav\Model\Entity\Attribute and convert to string field name
     *
     * @param string|\Magento\Eav\Model\Entity\Attribute $attribute
     * @return string
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _attributeToField($attribute)
    {
        $field = false;
        if (is_string($attribute)) {
            $field = $attribute;
        } elseif ($attribute instanceof \Magento\Eav\Model\Entity\Attribute) {
            $field = $attribute->getAttributeCode();
        }
        if (!$field) {
            throw new \Magento\Framework\Model\Exception(__('We cannot determine the field name.'));
        }
        return $field;
    }

    /**
     * Add attribute to select result set.
     * Backward compatibility with EAV collection
     *
     * @param string $attribute
     * @return $this
     */
    public function addAttributeToSelect($attribute)
    {
        $this->addFieldToSelect($this->_attributeToField($attribute));
        return $this;
    }

    /**
     * Specify collection select filter by attribute value
     * Backward compatibility with EAV collection
     *
     * @param string|\Magento\Eav\Model\Entity\Attribute $attribute
     * @param array|int|string|null $condition
     * @return $this
     */
    public function addAttributeToFilter($attribute, $condition = null)
    {
        $this->addFieldToFilter($this->_attributeToField($attribute), $condition);
        return $this;
    }

    /**
     * Specify collection select order by attribute value
     * Backward compatibility with EAV collection
     *
     * @param string $attribute
     * @param string $dir
     * @return $this
     */
    public function addAttributeToSort($attribute, $dir = 'asc')
    {
        $this->addOrder($this->_attributeToField($attribute), $dir);
        return $this;
    }

    /**
     * Set collection page start and records to show
     * Backward compatibility with EAV collection
     *
     * @param int $pageNum
     * @param int $pageSize
     * @return $this
     */
    public function setPage($pageNum, $pageSize)
    {
        $this->setCurPage($pageNum)->setPageSize($pageSize);
        return $this;
    }

    /**
     * Create all ids retrieving select with limitation
     * Backward compatibility with EAV collection
     *
     * @param int $limit
     * @param int $offset
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    protected function _getAllIdsSelect($limit = null, $offset = null)
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(\Zend_Db_Select::ORDER);
        $idsSelect->reset(\Zend_Db_Select::LIMIT_COUNT);
        $idsSelect->reset(\Zend_Db_Select::LIMIT_OFFSET);
        $idsSelect->reset(\Zend_Db_Select::COLUMNS);
        $idsSelect->columns($this->getResource()->getIdFieldName(), 'main_table');
        $idsSelect->limit($limit, $offset);
        return $idsSelect;
    }

    /**
     * Retrieve all ids for collection
     * Backward compatibility with EAV collection
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAllIds($limit = null, $offset = null)
    {
        return $this->getConnection()->fetchCol($this->_getAllIdsSelect($limit, $offset), $this->_bindParams);
    }

    /**
     * Backward compatibility with EAV collection
     *
     * @param string $alias
     * @param string $attribute
     * @param string $bind
     * @param string $filter
     * @param string $joinType
     * @param int $storeId
     * @return $this
     *
     * @todo implement join functionality if necessary
     */
    public function joinAttribute($alias, $attribute, $bind, $filter = null, $joinType = 'inner', $storeId = null)
    {
        return $this;
    }
}
