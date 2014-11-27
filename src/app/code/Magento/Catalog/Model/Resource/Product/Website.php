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
namespace Magento\Catalog\Model\Resource\Product;

/**
 * Catalog Product Website Resource Model
 */
class Website extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Store manager
     *
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Framework\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($resource);
    }

    /**
     * Initialize connection and define resource table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_product_website', 'product_id');
    }

    /**
     * Removes products from websites
     *
     * @param array $websiteIds
     * @param array $productIds
     * @return $this
     * @throws \Exception
     */
    public function removeProducts($websiteIds, $productIds)
    {
        if (!is_array($websiteIds) || !is_array($productIds) || count($websiteIds) == 0 || count($productIds) == 0) {
            return $this;
        }

        $adapter = $this->_getWriteAdapter();
        $whereCond = array(
            $adapter->quoteInto('website_id IN(?)', $websiteIds),
            $adapter->quoteInto('product_id IN(?)', $productIds)
        );
        $whereCond = join(' AND ', $whereCond);

        $adapter->beginTransaction();
        try {
            $adapter->delete($this->getMainTable(), $whereCond);
            $adapter->commit();
        } catch (\Exception $e) {
            $adapter->rollBack();
            throw $e;
        }

        return $this;
    }

    /**
     * Add products to websites
     *
     * @param array $websiteIds
     * @param array $productIds
     * @return $this
     * @throws \Exception
     */
    public function addProducts($websiteIds, $productIds)
    {
        if (!is_array($websiteIds) || !is_array($productIds) || count($websiteIds) == 0 || count($productIds) == 0) {
            return $this;
        }

        $this->_getWriteAdapter()->beginTransaction();

        // Before adding of products we should remove it old rows with same ids
        $this->removeProducts($websiteIds, $productIds);
        try {
            foreach ($websiteIds as $websiteId) {
                foreach ($productIds as $productId) {
                    if (!$productId) {
                        continue;
                    }
                    $this->_getWriteAdapter()->insert(
                        $this->getMainTable(),
                        array('product_id' => (int)$productId, 'website_id' => (int)$websiteId)
                    );
                }
            }
            $this->_getWriteAdapter()->commit();
        } catch (\Exception $e) {
            $this->_getWriteAdapter()->rollBack();
            throw $e;
        }
        return $this;
    }

    /**
     * Retrieve product(s) website ids.
     *
     * @param array $productIds
     * @return array
     */
    public function getWebsites($productIds)
    {
        $select = $this->_getReadAdapter()->select()->from(
            $this->getMainTable(),
            array('product_id', 'website_id')
        )->where(
            'product_id IN (?)',
            $productIds
        );
        $rowset = $this->_getReadAdapter()->fetchAll($select);

        $result = array();
        foreach ($rowset as $row) {
            $result[$row['product_id']][] = $row['website_id'];
        }

        return $result;
    }
}
