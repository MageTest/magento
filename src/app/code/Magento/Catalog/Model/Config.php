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
namespace Magento\Catalog\Model;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Config extends \Magento\Eav\Model\Config
{
    const XML_PATH_LIST_DEFAULT_SORT_BY = 'catalog/frontend/default_sort_by';

    /**
     * @var mixed
     */
    protected $_attributeSetsById;

    /**
     * @var mixed
     */
    protected $_attributeSetsByName;

    /**
     * @var mixed
     */
    protected $_attributeGroupsById;

    /**
     * @var mixed
     */
    protected $_attributeGroupsByName;

    /**
     * @var mixed
     */
    protected $_productTypesById;

    /**
     * Array of attributes codes needed for product load
     *
     * @var array
     */
    protected $_productAttributes;

    /**
     * Product Attributes used in product listing
     *
     * @var array
     */
    protected $_usedInProductListing;

    /**
     * Product Attributes For Sort By
     *
     * @var array
     */
    protected $_usedForSortBy;

    /**
     * @var int|float|string|null
     */
    protected $_storeId = null;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Eav config
     *
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * Store manager
     *
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Set collection factory
     *
     * @var \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory
     */
    protected $_setCollectionFactory;

    /**
     * Group collection factory
     *
     * @var \Magento\Eav\Model\Resource\Entity\Attribute\Group\CollectionFactory
     */
    protected $_groupCollectionFactory;

    /**
     * Product type factory
     *
     * @var \Magento\Catalog\Model\Product\TypeFactory
     */
    protected $_productTypeFactory;

    /**
     * Config factory
     *
     * @var \Magento\Catalog\Model\Resource\ConfigFactory
     */
    protected $_configFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Eav\Model\Entity\TypeFactory $entityTypeFactory
     * @param \Magento\Eav\Model\Resource\Entity\Type\CollectionFactory $entityTypeCollectionFactory,
     * @param \Magento\Framework\App\Cache\StateInterface $cacheState
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\Resource\ConfigFactory $configFactory
     * @param \Magento\Catalog\Model\Product\TypeFactory $productTypeFactory
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Group\CollectionFactory $groupCollectionFactory
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory $setCollectionFactory
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Eav\Model\Config $eavConfig
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Eav\Model\Entity\TypeFactory $entityTypeFactory,
        \Magento\Eav\Model\Resource\Entity\Type\CollectionFactory $entityTypeCollectionFactory,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Resource\ConfigFactory $configFactory,
        \Magento\Catalog\Model\Product\TypeFactory $productTypeFactory,
        \Magento\Eav\Model\Resource\Entity\Attribute\Group\CollectionFactory $groupCollectionFactory,
        \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory $setCollectionFactory,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_configFactory = $configFactory;
        $this->_productTypeFactory = $productTypeFactory;
        $this->_groupCollectionFactory = $groupCollectionFactory;
        $this->_setCollectionFactory = $setCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->_eavConfig = $eavConfig;

        parent::__construct($cache, $entityTypeFactory, $entityTypeCollectionFactory, $cacheState, $universalFactory);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Catalog\Model\Resource\Config');
    }

    /**
     * Set store id
     *
     * @param integer $storeId
     * @return \Magento\Catalog\Model\Config
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        return $this;
    }

    /**
     * Return store id, if is not set return current app store
     *
     * @return integer
     */
    public function getStoreId()
    {
        if ($this->_storeId === null) {
            return $this->_storeManager->getStore()->getId();
        }
        return $this->_storeId;
    }

    /**
     * @return $this
     */
    public function loadAttributeSets()
    {
        if ($this->_attributeSetsById) {
            return $this;
        }

        $attributeSetCollection = $this->_setCollectionFactory->create()->load();

        $this->_attributeSetsById = array();
        $this->_attributeSetsByName = array();
        foreach ($attributeSetCollection as $id => $attributeSet) {
            $entityTypeId = $attributeSet->getEntityTypeId();
            $name = $attributeSet->getAttributeSetName();
            $this->_attributeSetsById[$entityTypeId][$id] = $name;
            $this->_attributeSetsByName[$entityTypeId][strtolower($name)] = $id;
        }
        return $this;
    }

    /**
     * @param string|int|float $entityTypeId
     * @param float|int $id
     * @return false|string
     */
    public function getAttributeSetName($entityTypeId, $id)
    {
        if (!is_numeric($id)) {
            return $id;
        }
        $this->loadAttributeSets();

        if (!is_numeric($entityTypeId)) {
            $entityTypeId = $this->getEntityType($entityTypeId)->getId();
        }
        return isset(
            $this->_attributeSetsById[$entityTypeId][$id]
        ) ? $this->_attributeSetsById[$entityTypeId][$id] : false;
    }

    /**
     * @param string|int|float $entityTypeId
     * @param string|null $name
     * @return false|string|int
     */
    public function getAttributeSetId($entityTypeId, $name = null)
    {
        if (is_numeric($name)) {
            return $name;
        }
        $this->loadAttributeSets();

        if (!is_numeric($entityTypeId)) {
            $entityTypeId = $this->getEntityType($entityTypeId)->getId();
        }
        $name = strtolower($name);
        return isset(
            $this->_attributeSetsByName[$entityTypeId][$name]
        ) ? $this->_attributeSetsByName[$entityTypeId][$name] : false;
    }

    /**
     * @return $this
     */
    public function loadAttributeGroups()
    {
        if ($this->_attributeGroupsById) {
            return $this;
        }

        $attributeSetCollection = $this->_groupCollectionFactory->create()->load();

        $this->_attributeGroupsById = array();
        $this->_attributeGroupsByName = array();
        foreach ($attributeSetCollection as $id => $attributeGroup) {
            $attributeSetId = $attributeGroup->getAttributeSetId();
            $name = $attributeGroup->getAttributeGroupName();
            $this->_attributeGroupsById[$attributeSetId][$id] = $name;
            $this->_attributeGroupsByName[$attributeSetId][strtolower($name)] = $id;
        }
        return $this;
    }

    /**
     * @param float|int|string $attributeSetId
     * @param float|int|string $id
     * @return bool|string
     */
    public function getAttributeGroupName($attributeSetId, $id)
    {
        if (!is_numeric($id)) {
            return $id;
        }

        $this->loadAttributeGroups();

        if (!is_numeric($attributeSetId)) {
            $attributeSetId = $this->getAttributeSetId($attributeSetId);
        }
        return isset(
            $this->_attributeGroupsById[$attributeSetId][$id]
        ) ? $this->_attributeGroupsById[$attributeSetId][$id] : false;
    }

    /**
     * @param float|int|string $attributeSetId
     * @param string $name
     * @return bool|string|int|float
     */
    public function getAttributeGroupId($attributeSetId, $name)
    {
        if (is_numeric($name)) {
            return $name;
        }

        $this->loadAttributeGroups();

        if (!is_numeric($attributeSetId)) {
            $attributeSetId = $this->getAttributeSetId($attributeSetId);
        }
        $name = strtolower($name);
        return isset(
            $this->_attributeGroupsByName[$attributeSetId][$name]
        ) ? $this->_attributeGroupsByName[$attributeSetId][$name] : false;
    }

    /**
     * @return $this
     */
    public function loadProductTypes()
    {
        if ($this->_productTypesById) {
            return $this;
        }

        $productTypeCollection = $this->_productTypeFactory->create()->getOptionArray();

        $this->_productTypesById = array();
        $this->_productTypesByName = array();
        foreach ($productTypeCollection as $id => $type) {
            $name = $type;
            $this->_productTypesById[$id] = $name;
            $this->_productTypesByName[strtolower($name)] = $id;
        }
        return $this;
    }

    /**
     * @param string $name
     * @return false|string
     */
    public function getProductTypeId($name)
    {
        if (is_numeric($name)) {
            return $name;
        }

        $this->loadProductTypes();

        $name = strtolower($name);
        return isset($this->_productTypesByName[$name]) ? $this->_productTypesByName[$name] : false;
    }

    /**
     * @param float|int|string $id
     * @return false|string
     */
    public function getProductTypeName($id)
    {
        if (!is_numeric($id)) {
            return $id;
        }

        $this->loadProductTypes();

        return isset($this->_productTypesById[$id]) ? $this->_productTypesById[$id] : false;
    }

    /**
     * @param \Magento\Framework\Object $source
     * @param string $value
     * @return null|mixed
     */
    public function getSourceOptionId($source, $value)
    {
        foreach ($source->getAllOptions() as $option) {
            if (strcasecmp($option['label'], $value) == 0 || $option['value'] == $value) {
                return $option['value'];
            }
        }
        return null;
    }

    /**
     * Load Product attributes
     *
     * @return array
     */
    public function getProductAttributes()
    {
        if (is_null($this->_productAttributes)) {
            $this->_productAttributes = array_keys($this->getAttributesUsedInProductListing());
        }
        return $this->_productAttributes;
    }

    /**
     * Retrieve resource model
     *
     * @return \Magento\Catalog\Model\Resource\Config
     */
    protected function _getResource()
    {
        return $this->_configFactory->create();
    }

    /**
     * Retrieve Attributes used in product listing
     *
     * @return array
     */
    public function getAttributesUsedInProductListing()
    {
        if (is_null($this->_usedInProductListing)) {
            $this->_usedInProductListing = array();
            $entityType = \Magento\Catalog\Model\Product::ENTITY;
            $attributesData = $this->_getResource()->setStoreId($this->getStoreId())->getAttributesUsedInListing();
            $this->_eavConfig->importAttributesData($entityType, $attributesData);
            foreach ($attributesData as $attributeData) {
                $attributeCode = $attributeData['attribute_code'];
                $this->_usedInProductListing[$attributeCode] = $this->_eavConfig->getAttribute(
                    $entityType,
                    $attributeCode
                );
            }
        }
        return $this->_usedInProductListing;
    }

    /**
     * Retrieve Attributes array used for sort by
     *
     * @return array
     */
    public function getAttributesUsedForSortBy()
    {
        if (is_null($this->_usedForSortBy)) {
            $this->_usedForSortBy = array();
            $entityType = \Magento\Catalog\Model\Product::ENTITY;
            $attributesData = $this->_getResource()->getAttributesUsedForSortBy();
            $this->_eavConfig->importAttributesData($entityType, $attributesData);
            foreach ($attributesData as $attributeData) {
                $attributeCode = $attributeData['attribute_code'];
                $this->_usedForSortBy[$attributeCode] = $this->_eavConfig->getAttribute($entityType, $attributeCode);
            }
        }
        return $this->_usedForSortBy;
    }

    /**
     * Retrieve Attributes Used for Sort by as array
     * key = code, value = name
     *
     * @return array
     */
    public function getAttributeUsedForSortByArray()
    {
        $options = array('position' => __('Position'));
        foreach ($this->getAttributesUsedForSortBy() as $attribute) {
            /* @var $attribute \Magento\Eav\Model\Entity\Attribute\AbstractAttribute */
            $options[$attribute->getAttributeCode()] = $attribute->getStoreLabel();
        }

        return $options;
    }

    /**
     * Retrieve Product List Default Sort By
     *
     * @param mixed $store
     * @return string
     */
    public function getProductListDefaultSortBy($store = null)
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_LIST_DEFAULT_SORT_BY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
    }
}
