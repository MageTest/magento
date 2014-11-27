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


/**
 * Reports Product Index Abstract Product Resource Collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\Resource\Product\Index\Collection;

abstract class AbstractCollection extends \Magento\Catalog\Model\Resource\Product\Collection
{
    /**
     * Customer id
     *
     * @var null|int
     */
    protected $_customerId = null;

    /**
     * @var \Magento\Customer\Model\Visitor
     */
    protected $_customerVisitor;

    /**
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Eav\Model\EntityFactory $eavEntityFactory
     * @param \Magento\Catalog\Model\Resource\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory
     * @param \Magento\Catalog\Model\Resource\Url $catalogUrl
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Customer\Model\Visitor $customerVisitor
     * @param mixed $connection
     * 
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Framework\Logger $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\Resource $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Catalog\Model\Resource\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Magento\Catalog\Model\Resource\Url $catalogUrl,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Customer\Model\Visitor $customerVisitor,
        $connection = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $storeManager,
            $moduleManager,
            $catalogProductFlatState,
            $scopeConfig,
            $productOptionFactory,
            $catalogUrl,
            $localeDate,
            $customerSession,
            $dateTime,
            $connection
        );
        $this->_customerVisitor = $customerVisitor;
    }

    /**
     * Retrieve Product Index table name
     *
     * @return string
     */
    abstract protected function _getTableName();

    /**
     * Join index table
     *
     * @return $this
     */
    protected function _joinIdxTable()
    {
        if (!$this->getFlag('is_idx_table_joined')) {
            $this->joinTable(
                array('idx_table' => $this->_getTableName()),
                'product_id=entity_id',
                array('product_id' => 'product_id', 'item_store_id' => 'store_id', 'added_at' => 'added_at'),
                $this->_getWhereCondition()
            );
            $this->setFlag('is_idx_table_joined', true);
        }
        return $this;
    }

    /**
     * Add Viewed Products Index to Collection
     *
     * @return $this
     */
    public function addIndexFilter()
    {
        $this->_joinIdxTable();
        $this->_productLimitationFilters['store_table'] = 'idx_table';
        $this->setFlag('url_data_object', true);
        $this->setFlag('do_not_use_category_id', true);
        return $this;
    }

    /**
     * Add filter by product ids
     *
     * @param array $ids
     * @return $this
     */
    public function addFilterByIds($ids)
    {
        if (empty($ids)) {
            $this->getSelect()->where('1=0');
        } else {
            $this->getSelect()->where('e.entity_id IN(?)', $ids);
        }
        return $this;
    }

    /**
     * Retrieve Where Condition to Index table
     *
     * @return array
     */
    protected function _getWhereCondition()
    {
        $condition = array();

        if ($this->_customerSession->isLoggedIn()) {
            $condition['customer_id'] = $this->_customerSession->getCustomerId();
        } elseif ($this->_customerId) {
            $condition['customer_id'] = $this->_customerId;
        } else {
            $condition['visitor_id'] = $this->_customerVisitor->getId();
        }

        return $condition;
    }

    /**
     * Set customer id, that will be used in 'whereCondition'
     *
     * @param int $id
     * @return $this
     */
    public function setCustomerId($id)
    {
        $this->_customerId = (int)$id;
        return $this;
    }

    /**
     * Add order by "added at"
     *
     * @param string $dir
     * @return $this
     */
    public function setAddedAtOrder($dir = self::SORT_ORDER_DESC)
    {
        if ($this->getFlag('is_idx_table_joined')) {
            $this->getSelect()->order('added_at ' . $dir);
        }
        return $this;
    }

    /**
     * Add exclude Product Ids
     *
     * @param int|array $productIds
     * @return $this
     */
    public function excludeProductIds($productIds)
    {
        if (empty($productIds)) {
            return $this;
        }
        $this->_joinIdxTable();
        $this->getSelect()->where('idx_table.product_id NOT IN(?)', $productIds);
        return $this;
    }
}
