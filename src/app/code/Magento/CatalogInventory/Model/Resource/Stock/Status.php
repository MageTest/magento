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
namespace Magento\CatalogInventory\Model\Resource\Stock;

use Magento\CatalogInventory\Model\Stock;

/**
 * CatalogInventory Stock Status per website Resource Model
 */
class Status extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Store model manager
     *
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Website model factory
     *
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $_websiteFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        parent::__construct($resource);

        $this->_storeManager = $storeManager;
        $this->_websiteFactory = $websiteFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * Resource model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('cataloginventory_stock_status', 'product_id');
    }

    /**
     * Save Product Status per website
     *
     * @param int $productId
     * @param int $status
     * @param float|int $qty
     * @param int|null $websiteId
     * @return $this
     */
    public function saveProductStatus(
        $productId,
        $status,
        $qty,
        $websiteId
    ) {
        $adapter = $this->_getWriteAdapter();
        $select = $adapter->select()->from($this->getMainTable())
            ->where('product_id = :product_id')
            ->where('website_id = :website_id');
        $bind = array(':product_id' => $productId, ':website_id' => $websiteId);
        $row = $adapter->fetchRow($select, $bind);
        if ($row) {
            $bind = array('qty' => $qty, 'stock_status' => $status);
            $where = array(
                $adapter->quoteInto('product_id=?', (int)$row['product_id']),
                $adapter->quoteInto('website_id=?', (int)$row['website_id'])
            );
            $adapter->update($this->getMainTable(), $bind, $where);
        } else {
            $bind = array(
                'product_id' => $productId,
                'website_id' => $websiteId,
                'qty' => $qty,
                'stock_status' => $status
            );
            $adapter->insert($this->getMainTable(), $bind);
        }

        return $this;
    }

    /**
     * Retrieve product status
     * Return array as key product id, value - stock status
     *
     * @param int[] $productIds
     * @param int $websiteId
     * @return array
     */
    public function getProductsStockStatuses($productIds, $websiteId)
    {
        if (!is_array($productIds)) {
            $productIds = array($productIds);
        }

        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), array('product_id', 'stock_status'))
            ->where('product_id IN(?)', $productIds)
            ->where('website_id=?', (int) $websiteId);
        return $this->_getReadAdapter()->fetchPairs($select);
    }

    /**
     * Retrieve websites and default stores
     * Return array as key website_id, value store_id
     *
     * @return array
     */
    public function getWebsiteStores()
    {
        /** @var \Magento\Store\Model\Website $website */
        $website = $this->_websiteFactory->create();
        return $this->_getReadAdapter()->fetchPairs($website->getDefaultStoresSelect(false));
    }

    /**
     * Retrieve Product Type
     *
     * @param array|int $productIds
     * @return array
     */
    public function getProductsType($productIds)
    {
        if (!is_array($productIds)) {
            $productIds = array($productIds);
        }

        $select = $this->_getReadAdapter()->select()->from(
            array('e' => $this->getTable('catalog_product_entity')),
            array('entity_id', 'type_id')
        )->where(
            'entity_id IN(?)',
            $productIds
        );
        return $this->_getReadAdapter()->fetchPairs($select);
    }

    /**
     * Retrieve Product part Collection array
     * Return array as key product id, value product type
     *
     * @param int $lastEntityId
     * @param int $limit
     * @return array
     */
    public function getProductCollection($lastEntityId = 0, $limit = 1000)
    {
        $select = $this->_getReadAdapter()->select()->from(
            array('e' => $this->getTable('catalog_product_entity')),
            array('entity_id', 'type_id')
        )
            ->order('entity_id ASC')
            ->where('entity_id > :entity_id')
            ->limit($limit);
        return $this->_getReadAdapter()->fetchPairs($select, array(':entity_id' => $lastEntityId));
    }

    /**
     * Add stock status to prepare index select
     *
     * @param \Magento\Framework\DB\Select $select
     * @param \Magento\Store\Model\Website $website
     * @return Status
     */
    public function addStockStatusToSelect(\Magento\Framework\DB\Select $select, \Magento\Store\Model\Website $website)
    {
        $websiteId = $website->getId();
        $select->joinLeft(
            array('stock_status' => $this->getMainTable()),
            'e.entity_id = stock_status.product_id AND stock_status.website_id=' . $websiteId,
            array('salable' => 'stock_status.stock_status')
        );

        return $this;
    }

    /**
     * Add only is in stock products filter to product collection
     *
     * @param \Magento\Catalog\Model\Resource\Product\Collection $collection
     * @return $this
     */
    public function addIsInStockFilterToCollection($collection)
    {
        $websiteId = $this->_storeManager->getStore($collection->getStoreId())->getWebsiteId();
        $joinCondition = $this->_getReadAdapter()->quoteInto(
            'e.entity_id = stock_status_index.product_id' . ' AND stock_status_index.website_id = ?',
            $websiteId
        );

        $joinCondition .= $this->_getReadAdapter()->quoteInto(
            ' AND stock_status_index.website_id = ?',
            $websiteId
        );

        $collection->getSelect()->join(
            array('stock_status_index' => $this->getMainTable()),
            $joinCondition,
            array()
        )->where(
            'stock_status_index.stock_status=?',
            Stock\Status::STATUS_IN_STOCK
        );
        return $this;
    }

    /**
     * Retrieve Product(s) status for store
     * Return array where key is a product_id, value - status
     *
     * @param int[] $productIds
     * @param int $storeId
     * @return array
     */
    public function getProductStatus($productIds, $storeId = null)
    {
        if (!is_array($productIds)) {
            $productIds = array($productIds);
        }

        $attribute = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'status');
        $attributeTable = $attribute->getBackend()->getTable();

        $adapter = $this->_getReadAdapter();

        if ($storeId === null || $storeId == \Magento\Store\Model\Store::DEFAULT_STORE_ID) {
            $select = $adapter->select()->from($attributeTable, array('entity_id', 'value'))
                ->where('entity_id IN (?)', $productIds)
                ->where('attribute_id = ?', $attribute->getAttributeId())
                ->where('store_id = ?', \Magento\Store\Model\Store::DEFAULT_STORE_ID);

            $rows = $adapter->fetchPairs($select);
        } else {
            $select = $adapter->select()->from(
                array('t1' => $attributeTable),
                array('entity_id' => 't1.entity_id', 'value' => $adapter->getIfNullSql('t2.value', 't1.value'))
            )->joinLeft(
                array('t2' => $attributeTable),
                't1.entity_id = t2.entity_id AND t1.attribute_id = t2.attribute_id AND t2.store_id = ' . (int)$storeId
            )->where(
                't1.store_id = ?',
                \Magento\Store\Model\Store::DEFAULT_STORE_ID
            )->where(
                't1.attribute_id = ?',
                $attribute->getAttributeId()
            )->where(
                't1.entity_id IN(?)',
                $productIds
            );

            $rows = $adapter->fetchPairs($select);
        }

        $statuses = array();
        foreach ($productIds as $productId) {
            if (isset($rows[$productId])) {
                $statuses[$productId] = $rows[$productId];
            } else {
                $statuses[$productId] = -1;
            }
        }
        return $statuses;
    }
}
