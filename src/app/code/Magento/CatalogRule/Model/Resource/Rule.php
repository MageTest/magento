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
 * Catalog rules resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CatalogRule\Model\Resource;

use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Model\Rule as ModelRule;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Rule extends \Magento\Rule\Model\Resource\AbstractResource
{
    /**
     * Store number of seconds in a day
     */
    const SECONDS_IN_DAY = 86400;

    /**
     * @var \Magento\Framework\Logger
     */
    protected $_logger;

    /**
     * Store associated with rule entities information map
     *
     * @var array
     */
    protected $_associatedEntitiesMap = array(
        'website' => array(
            'associations_table' => 'catalogrule_website',
            'rule_id_field' => 'rule_id',
            'entity_id_field' => 'website_id'
        ),
        'customer_group' => array(
            'associations_table' => 'catalogrule_customer_group',
            'rule_id_field' => 'rule_id',
            'entity_id_field' => 'customer_group_id'
        )
    );

    /**
     * Catalog rule data
     *
     * @var \Magento\CatalogRule\Helper\Data
     */
    protected $_catalogRuleData = null;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_coreDate;

    /**
     * @var \Magento\Catalog\Model\Product\ConditionFactory
     */
    protected $_conditionFactory;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param Product\ConditionFactory $conditionFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $coreDate
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\CatalogRule\Helper\Data $catalogRuleData
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\ConditionFactory $conditionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\CatalogRule\Helper\Data $catalogRuleData,
        \Magento\Framework\Logger $logger,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->_storeManager = $storeManager;
        $this->_conditionFactory = $conditionFactory;
        $this->_coreDate = $coreDate;
        $this->_eavConfig = $eavConfig;
        $this->_eventManager = $eventManager;
        $this->_catalogRuleData = $catalogRuleData;
        $this->_logger = $logger;
        $this->dateTime = $dateTime;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($resource);
    }

    /**
     * Initialize main table and table id field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalogrule', 'rule_id');
    }

    /**
     * Add customer group ids and website ids to rule data after load
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\Model\Resource\Db\AbstractDb
     */
    protected function _afterLoad(AbstractModel $object)
    {
        $object->setData('customer_group_ids', (array)$this->getCustomerGroupIds($object->getId()));
        $object->setData('website_ids', (array)$this->getWebsiteIds($object->getId()));

        return parent::_afterLoad($object);
    }

    /**
     * Bind catalog rule to customer group(s) and website(s).
     * Update products which are matched for rule.
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        if ($object->hasWebsiteIds()) {
            $websiteIds = $object->getWebsiteIds();
            if (!is_array($websiteIds)) {
                $websiteIds = explode(',', (string)$websiteIds);
            }
            $this->bindRuleToEntity($object->getId(), $websiteIds, 'website');
        }

        if ($object->hasCustomerGroupIds()) {
            $customerGroupIds = $object->getCustomerGroupIds();
            if (!is_array($customerGroupIds)) {
                $customerGroupIds = explode(',', (string)$customerGroupIds);
            }
            $this->bindRuleToEntity($object->getId(), $customerGroupIds, 'customer_group');
        }

        parent::_afterSave($object);
        return $this;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $rule
     * @return $this
     */
    protected function _afterDelete(\Magento\Framework\Model\AbstractModel $rule)
    {
        $write = $this->_getWriteAdapter();
        $write->delete(
            $this->getTable('catalogrule_product'),
            array('rule_id=?' => $rule->getId())
        );
        $write->delete(
            $this->getTable('catalogrule_customer_group'),
            array('rule_id=?' => $rule->getId())
        );
        $write->delete(
            $this->getTable('catalogrule_group_website'),
            array('rule_id=?' => $rule->getId())
        );
        return parent::_afterDelete($rule);
    }

    /**
     * Get catalog rules product price for specific date, website and
     * customer group
     *
     * @param int|string $date
     * @param int $wId
     * @param int $gId
     * @param int $pId
     * @return float|false
     */
    public function getRulePrice($date, $wId, $gId, $pId)
    {
        $data = $this->getRulePrices($date, $wId, $gId, array($pId));
        if (isset($data[$pId])) {
            return $data[$pId];
        }

        return false;
    }

    /**
     * Retrieve product prices by catalog rule for specific date, website and customer group
     * Collect data with  product Id => price pairs
     *
     * @param int|string $date
     * @param int $websiteId
     * @param int $customerGroupId
     * @param array $productIds
     * @return array
     */
    public function getRulePrices($date, $websiteId, $customerGroupId, $productIds)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()->from(
            $this->getTable('catalogrule_product_price'),
            array('product_id', 'rule_price')
        )->where(
            'rule_date = ?',
            $this->dateTime->formatDate($date, false)
        )->where(
            'website_id = ?',
            $websiteId
        )->where(
            'customer_group_id = ?',
            $customerGroupId
        )->where(
            'product_id IN(?)',
            $productIds
        );
        return $adapter->fetchPairs($select);
    }

    /**
     * Get active rule data based on few filters
     *
     * @param int|string $date
     * @param int $websiteId
     * @param int $customerGroupId
     * @param int $productId
     * @return array
     */
    public function getRulesFromProduct($date, $websiteId, $customerGroupId, $productId)
    {
        $adapter = $this->_getReadAdapter();
        if (is_string($date)) {
            $date = strtotime($date);
        }
        $select = $adapter->select()->from(
            $this->getTable('catalogrule_product')
        )->where(
            'website_id = ?',
            $websiteId
        )->where(
            'customer_group_id = ?',
            $customerGroupId
        )->where(
            'product_id = ?',
            $productId
        )->where(
            'from_time = 0 or from_time < ?',
            $date
        )->where(
            'to_time = 0 or to_time > ?',
            $date
        );

        return $adapter->fetchAll($select);
    }
}
