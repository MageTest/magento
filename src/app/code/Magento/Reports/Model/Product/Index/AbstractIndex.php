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
namespace Magento\Reports\Model\Product\Index;

/**
 * Reports Product Index Abstract Model
 */
abstract class AbstractIndex extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Cache key name for Count of product index
     *
     * @var string
     */
    protected $_countCacheKey;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Customer\Model\Visitor
     */
    protected $_customerVisitor;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $_reportSession;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_productVisibility;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Visitor $customerVisitor
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Session\Generic $reportSession
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Visitor $customerVisitor,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Session\Generic $reportSession,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_storeManager = $storeManager;
        $this->dateTime = $dateTime;
        $this->_customerVisitor = $customerVisitor;
        $this->_customerSession = $customerSession;
        $this->_reportSession = $reportSession;
        $this->_productVisibility = $productVisibility;
    }

    /**
     * Prepare customer/visitor, store data before save
     *
     * @return $this
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();

        if (!$this->hasVisitorId()) {
            $this->setVisitorId($this->getVisitorId());
        }
        if (!$this->hasCustomerId()) {
            $this->setCustomerId($this->getCustomerId());
        }
        if (!$this->hasStoreId()) {
            $this->setStoreId($this->getStoreId());
        }
        if (!$this->hasAddedAt()) {
            $this->setAddedAt($this->dateTime->now());
        }

        return $this;
    }

    /**
     * Retrieve visitor id
     *
     * if don't exists return current visitor id
     *
     * @return int
     */
    public function getVisitorId()
    {
        if ($this->hasData('visitor_id')) {
            return $this->getData('visitor_id');
        }
        return $this->_customerVisitor->getId();
    }

    /**
     * Retrieve customer id
     *
     * if customer don't logged in return null
     *
     * @return int
     */
    public function getCustomerId()
    {
        if ($this->hasData('customer_id')) {
            return $this->getData('customer_id');
        }
        return $this->_customerSession->getCustomerId();
    }

    /**
     * Retrieve store id
     *
     * default return current store id
     *
     * @return int
     */
    public function getStoreId()
    {
        if ($this->hasData('store_id')) {
            return $this->getData('store_id');
        }
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * Retrieve resource instance wrapper
     *
     * @return \Magento\Reports\Model\Resource\Product\Index\AbstractIndex
     */
    protected function _getResource()
    {
        return parent::_getResource();
    }

    /**
     * On customer loggin merge visitor/customer index
     *
     * @return $this
     */
    public function updateCustomerFromVisitor()
    {
        $this->_getResource()->updateCustomerFromVisitor($this);
        return $this;
    }

    /**
     * Purge visitor data by customer (logout)
     *
     * @return $this
     */
    public function purgeVisitorByCustomer()
    {
        $this->_getResource()->purgeVisitorByCustomer($this);
        return $this;
    }

    /**
     * Retrieve Reports Session instance
     *
     * @return \Magento\Framework\Session\Generic
     */
    protected function _getSession()
    {
        return $this->_reportSession;
    }

    /**
     * Calculate count of product index items cache
     *
     * @return $this
     */
    public function calculate()
    {
        $collection = $this->getCollection()->setCustomerId(
            $this->getCustomerId()
        )->addIndexFilter()->setVisibility(
            $this->_productVisibility->getVisibleInSiteIds()
        );

        $count = $collection->getSize();
        $this->_getSession()->setData($this->_countCacheKey, $count);
        return $this;
    }

    /**
     * Retrieve Exclude Product Ids List for Collection
     *
     * @return array
     */
    public function getExcludeProductIds()
    {
        return array();
    }

    /**
     * Retrieve count of product index items
     *
     * @return int
     */
    public function getCount()
    {
        if (!$this->_countCacheKey) {
            return 0;
        }

        if (!$this->_getSession()->hasData($this->_countCacheKey)) {
            $this->calculate();
        }

        return $this->_getSession()->getData($this->_countCacheKey);
    }

    /**
     * Clean index (visitors)
     *
     * @return $this
     */
    public function clean()
    {
        $this->_getResource()->clean($this);
        return $this;
    }

    /**
     * Add product ids to current visitor/customer log
     * @param string[] $productIds
     * @return $this
     */
    public function registerIds($productIds)
    {
        $this->_getResource()->registerIds($this, $productIds);
        $this->_getSession()->unsData($this->_countCacheKey);
        return $this;
    }
}
