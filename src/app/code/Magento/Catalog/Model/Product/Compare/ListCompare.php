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
namespace Magento\Catalog\Model\Product\Compare;

use Magento\Catalog\Model\Resource\Product\Compare\Item\Collection;

/**
 * Product Compare List Model
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class ListCompare extends \Magento\Framework\Object
{
    /**
     * Customer visitor
     *
     * @var \Magento\Customer\Model\Visitor
     */
    protected $_customerVisitor;

    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * Catalog product compare item
     *
     * @var \Magento\Catalog\Model\Resource\Product\Compare\Item
     */
    protected $_catalogProductCompareItem;

    /**
     * Item collection factory
     *
     * @var \Magento\Catalog\Model\Resource\Product\Compare\Item\CollectionFactory
     */
    protected $_itemCollectionFactory;

    /**
     * Compare item factory
     *
     * @var \Magento\Catalog\Model\Product\Compare\ItemFactory
     */
    protected $_compareItemFactory;

    /**
     * Constructor
     *
     * @param \Magento\Catalog\Model\Product\Compare\ItemFactory $compareItemFactory
     * @param \Magento\Catalog\Model\Resource\Product\Compare\Item\CollectionFactory $itemCollectionFactory
     * @param \Magento\Catalog\Model\Resource\Product\Compare\Item $catalogProductCompareItem
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Visitor $customerVisitor
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Compare\ItemFactory $compareItemFactory,
        \Magento\Catalog\Model\Resource\Product\Compare\Item\CollectionFactory $itemCollectionFactory,
        \Magento\Catalog\Model\Resource\Product\Compare\Item $catalogProductCompareItem,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Visitor $customerVisitor,
        array $data = array()
    ) {
        $this->_compareItemFactory = $compareItemFactory;
        $this->_itemCollectionFactory = $itemCollectionFactory;
        $this->_catalogProductCompareItem = $catalogProductCompareItem;
        $this->_customerSession = $customerSession;
        $this->_customerVisitor = $customerVisitor;
        parent::__construct($data);
    }

    /**
     * Add product to Compare List
     *
     * @param int|\Magento\Catalog\Model\Product $product
     * @return $this
     */
    public function addProduct($product)
    {
        /* @var $item \Magento\Catalog\Model\Product\Compare\Item */
        $item = $this->_compareItemFactory->create();
        $this->_addVisitorToItem($item);
        $item->loadByProduct($product);

        if (!$item->getId()) {
            $item->addProductData($product);
            $item->save();
        }

        return $this;
    }

    /**
     * Add products to compare list
     *
     * @param string[] $productIds
     * @return $this
     */
    public function addProducts($productIds)
    {
        if (is_array($productIds)) {
            foreach ($productIds as $productId) {
                $this->addProduct($productId);
            }
        }
        return $this;
    }

    /**
     * Retrieve Compare Items Collection
     *
     * @return Collection
     */
    public function getItemCollection()
    {
        return $this->_itemCollectionFactory->create();
    }

    /**
     * Remove product from compare list
     *
     * @param int|\Magento\Catalog\Model\Product $product
     * @return $this
     */
    public function removeProduct($product)
    {
        /* @var $item \Magento\Catalog\Model\Product\Compare\Item */
        $item = $this->_compareItemFactory->create();
        $this->_addVisitorToItem($item);
        $item->loadByProduct($product);

        if ($item->getId()) {
            $item->delete();
        }

        return $this;
    }

    /**
     * Add visitor and customer data to compare item
     *
     * @param \Magento\Catalog\Model\Product\Compare\Item $item
     * @return $this
     */
    protected function _addVisitorToItem($item)
    {
        $item->addVisitorId($this->_customerVisitor->getId());
        if ($this->_customerSession->isLoggedIn()) {
            $item->setCustomerId($this->_customerSession->getCustomerId());
        }

        return $this;
    }

    /**
     * Check has compare items by visitor/customer
     *
     * @param int $customerId
     * @param int $visitorId
     * @return bool
     */
    public function hasItems($customerId, $visitorId)
    {
        return (bool)$this->_catalogProductCompareItem->getCount($customerId, $visitorId);
    }
}
