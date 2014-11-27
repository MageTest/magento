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
namespace Magento\Eav\Model;

use Magento\Eav\Model\Entity\Type;

class Config
{
    /**#@+
     * EAV cache ids
     */
    const ENTITIES_CACHE_ID = 'EAV_ENTITY_TYPES';
    const ATTRIBUTES_CACHE_ID = 'EAV_ENTITY_ATTRIBUTES';
    const ATTRIBUTES_CODES_CACHE_ID = 'EAV_ENTITY_ATTRIBUTES_CODES';
    /**#@-*/

    /**
     * Entity types data
     *
     * @var array
     */
    protected $_entityTypeData;

    /**
     * Attributes data
     *
     * @var array
     */
    protected $_attributeData;

    /**
     * Attribute codes cache array
     *
     * @var array
     */
    protected $_attributeCodes;

    /**
     * Initialized objects
     *
     * array ($objectId => $object)
     *
     * @var array
     */
    protected $_objects;

    /**
     * References between codes and identifiers
     *
     * array (
     *      'attributes'=> array ($attributeId => $attributeCode),
     *      'entities'  => array ($entityId => $entityCode)
     * )
     *
     * @var array
     */
    protected $_references;

    /**
     * Cache flag
     *
     * @var bool|null
     */
    protected $_isCacheEnabled = null;

    /**
     * Array of attributes objects used in collections
     *
     * @var array
     */
    protected $_collectionAttributes = array();

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $_cache;

    /** @var \Magento\Framework\App\Cache\StateInterface */
    protected $_cacheState;

    /**
     * @var \Magento\Eav\Model\Entity\TypeFactory
     */
    protected $_entityTypeFactory;

    /**
     * @var \Magento\Eav\Model\Resource\Entity\Type\CollectionFactory
     */
    protected $entityTypeCollectionFactory;

    /**
     * @var \Magento\Framework\Validator\UniversalFactory
     */
    protected $_universalFactory;

    /**
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Eav\Model\Entity\TypeFactory $entityTypeFactory
     * @param \Magento\Eav\Model\Resource\Entity\Type\CollectionFactory $entityTypeCollectionFactory
     * @param \Magento\Framework\App\Cache\StateInterface $cacheState
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     */
    public function __construct(
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Eav\Model\Entity\TypeFactory $entityTypeFactory,
        \Magento\Eav\Model\Resource\Entity\Type\CollectionFactory $entityTypeCollectionFactory,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        \Magento\Framework\Validator\UniversalFactory $universalFactory
    ) {
        $this->_cache = $cache;
        $this->_entityTypeFactory = $entityTypeFactory;
        $this->entityTypeCollectionFactory = $entityTypeCollectionFactory;
        $this->_cacheState = $cacheState;
        $this->_universalFactory = $universalFactory;
    }

    /**
     * Reset object state
     *
     * @return $this
     */
    public function clear()
    {
        $this->_entityTypeData = null;
        $this->_attributeData = null;
        $this->_objects = null;
        $this->_references = null;
        $this->_attributeCodes = null;
        $this->_cache->clean(
            [
                \Magento\Eav\Model\Cache\Type::CACHE_TAG,
                \Magento\Eav\Model\Entity\Attribute::CACHE_TAG
            ]
        );
        return $this;
    }

    /**
     * Get object by identifier
     *
     * @param   mixed $id
     * @return  mixed
     */
    protected function _load($id)
    {
        return isset($this->_objects[$id]) ? $this->_objects[$id] : null;
    }

    /**
     * Associate object with identifier
     *
     * @param   mixed $obj
     * @param   mixed $id
     * @return $this
     */
    protected function _save($obj, $id)
    {
        $this->_objects[$id] = $obj;
        return $this;
    }

    /**
     * Specify reference for entity type id
     *
     * @param   int $id
     * @param   string $code
     * @return $this
     */
    protected function _addEntityTypeReference($id, $code)
    {
        $this->_references['entity'][$id] = $code;
        return $this;
    }

    /**
     * Get entity type code by id
     *
     * @param   int $id
     * @return  string
     */
    protected function _getEntityTypeReference($id)
    {
        return isset($this->_references['entity'][$id]) ? $this->_references['entity'][$id] : null;
    }

    /**
     * Specify reference between entity attribute id and attribute code
     *
     * @param   int $id
     * @param   string $code
     * @param   string $entityTypeCode
     * @return $this
     */
    protected function _addAttributeReference($id, $code, $entityTypeCode)
    {
        $this->_references['attribute'][$entityTypeCode][$id] = $code;
        return $this;
    }

    /**
     * Get attribute code by attribute id
     *
     * @param   int $id
     * @param   string $entityTypeCode
     * @return  string|null
     */
    protected function _getAttributeReference($id, $entityTypeCode)
    {
        if (isset($this->_references['attribute'][$entityTypeCode][$id])) {
            return $this->_references['attribute'][$entityTypeCode][$id];
        }
        return null;
    }

    /**
     * Get internal cache key for entity type code
     *
     * @param   string $code
     * @return  string
     */
    protected function _getEntityKey($code)
    {
        return 'ENTITY/' . $code;
    }

    /**
     * Get internal cache key for attribute object cache
     *
     * @param   string $entityTypeCode
     * @param   string $attributeCode
     * @return  string
     */
    protected function _getAttributeKey($entityTypeCode, $attributeCode)
    {
        return 'ATTRIBUTE/' . $entityTypeCode . '/' . $attributeCode;
    }

    /**
     * Check EAV cache availability
     *
     * @return bool
     */
    protected function _isCacheEnabled()
    {
        if ($this->_isCacheEnabled === null) {
            $this->_isCacheEnabled = $this->_cacheState->isEnabled(\Magento\Eav\Model\Cache\Type::TYPE_IDENTIFIER);
        }
        return $this->_isCacheEnabled;
    }

    /**
     * Initialize all entity types data
     *
     * @return $this
     */
    protected function _initEntityTypes()
    {
        if (is_array($this->_entityTypeData)) {
            return $this;
        }
        \Magento\Framework\Profiler::start('EAV: ' . __METHOD__, array('group' => 'EAV', 'method' => __METHOD__));

        if ($this->_isCacheEnabled() && ($cache = $this->_cache->load(self::ENTITIES_CACHE_ID))) {
            $this->_entityTypeData = unserialize($cache);
            foreach ($this->_entityTypeData as $typeCode => $data) {
                $typeId = $data['entity_type_id'];
                $this->_addEntityTypeReference($typeId, $typeCode);
            }
            \Magento\Framework\Profiler::stop('EAV: ' . __METHOD__);
            return $this;
        }

        $entityTypesData = $this->entityTypeCollectionFactory->create()->getData();
        foreach ($entityTypesData as $typeData) {
            if (!isset($typeData['attribute_model'])) {
                $typeData['attribute_model'] = 'Magento\Eav\Model\Entity\Attribute';
            }

            $typeCode = $typeData['entity_type_code'];
            $typeId = $typeData['entity_type_id'];

            $this->_addEntityTypeReference($typeId, $typeCode);
            $this->_entityTypeData[$typeCode] = $typeData;
        }

        if ($this->_isCacheEnabled()) {
            $this->_cache->save(
                serialize($this->_entityTypeData),
                self::ENTITIES_CACHE_ID,
                array(
                    \Magento\Eav\Model\Cache\Type::CACHE_TAG,
                    \Magento\Eav\Model\Entity\Attribute::CACHE_TAG
                )
            );
        }
        \Magento\Framework\Profiler::stop('EAV: ' . __METHOD__);
        return $this;
    }

    /**
     * Get entity type object by entity type code/identifier
     *
     * @param int|string $code
     * @return Type
     * @throws \Magento\Framework\Model\Exception
     */
    public function getEntityType($code)
    {
        if ($code instanceof Type) {
            return $code;
        }
        $this->_initEntityTypes();
        \Magento\Framework\Profiler::start('EAV: ' . __METHOD__, array('group' => 'EAV', 'method' => __METHOD__));

        if (is_numeric($code)) {
            $entityCode = $this->_getEntityTypeReference($code);
            if ($entityCode !== null) {
                $code = $entityCode;
            }
        }

        $entityKey = $this->_getEntityKey($code);
        $entityType = $this->_load($entityKey);
        if ($entityType) {
            \Magento\Framework\Profiler::stop('EAV: ' . __METHOD__);
            return $entityType;
        }

        $entityType = $this->_entityTypeFactory->create(
            ['data' => isset($this->_entityTypeData[$code]) ? $this->_entityTypeData[$code] : []]
        );
        if (!$entityType->getId()) {
            throw new \Magento\Framework\Model\Exception(__('Invalid entity_type specified: %1', $code));
        }
        $this->_addEntityTypeReference($entityType->getId(), $entityType->getEntityTypeCode());
        $this->_save($entityType, $entityKey);

        \Magento\Framework\Profiler::stop('EAV: ' . __METHOD__);
        return $entityType;
    }

    /**
     * Initialize all attributes for entity type
     *
     * @param   string $entityType
     * @return $this
     */
    protected function _initAttributes($entityType)
    {
        $entityType = $this->getEntityType($entityType);
        $entityTypeCode = $entityType->getEntityTypeCode();

        if (isset($this->_attributeData[$entityTypeCode])) {
            return $this;
        }
        $cacheKey = self::ATTRIBUTES_CACHE_ID . $entityTypeCode;
        if ($this->_isCacheEnabled() && ($attributes = $this->_cache->load($cacheKey))) {
            $attributes = unserialize($attributes);
            if ($attributes) {
                foreach ($attributes as $attribute) {
                    $this->_createAttribute($entityType, $attribute);
                    $this->_attributeData[$entityTypeCode][$attribute['attribute_code']] = $attribute;
                }
                return $this;
            }
        }

        \Magento\Framework\Profiler::start('EAV: ' . __METHOD__, array('group' => 'EAV', 'method' => __METHOD__));

        $attributes = $this->_universalFactory->create(
            $entityType->getEntityAttributeCollection()
        )->setEntityTypeFilter(
            $entityType
        )->getData();

        foreach ($attributes as $attribute) {
            if (empty($attribute['attribute_model'])) {
                $attribute['attribute_model'] = $entityType->getAttributeModel();
            }
            $this->_createAttribute($entityType, $attribute);
            $this->_attributeData[$entityTypeCode][$attribute['attribute_code']] = $attribute;
        }
        if ($this->_isCacheEnabled()) {
            $this->_cache->save(
                serialize($this->_attributeData[$entityTypeCode]),
                $cacheKey,
                array(
                    \Magento\Eav\Model\Cache\Type::CACHE_TAG,
                    \Magento\Eav\Model\Entity\Attribute::CACHE_TAG
                )
            );
        }

        \Magento\Framework\Profiler::stop('EAV: ' . __METHOD__);
        return $this;
    }

    /**
     * Get attribute by code for entity type
     *
     * @param   mixed $entityType
     * @param   mixed $code
     * @return  \Magento\Eav\Model\Entity\Attribute\AbstractAttribute|false
     * @throws \Magento\Framework\Model\Exception
     */
    public function getAttribute($entityType, $code)
    {
        if ($code instanceof \Magento\Eav\Model\Entity\Attribute\AttributeInterface) {
            return $code;
        }
        $this->_initAttributes($entityType);

        $entityTypeCode = $this->getEntityType($entityType)->getEntityTypeCode();
        if (is_numeric($code)) {
            $attributeCode = $this->_getAttributeReference($code, $entityTypeCode);
            if ($attributeCode) {
                $code = $attributeCode;
            }
        }
        $attributeKey = $this->_getAttributeKey($entityTypeCode, $code);

        $attribute = $this->_load($attributeKey);
        if (!$attribute) {
            // TODO: refactor wrong method usage in: addAttributeToSelect, joinAttribute
            $entityType = $this->getEntityType($entityType);
            $attribute = $this->_universalFactory->create($entityType->getAttributeModel());
            $attribute->setAttributeCode($code);
            $entity = $entityType->getEntity();
            if ($entity && in_array($attribute->getAttributeCode(), $entity->getDefaultAttributes())) {
                $attribute->setBackendType(
                    \Magento\Eav\Model\Entity\Attribute\AbstractAttribute::TYPE_STATIC
                )->setIsGlobal(
                    1
                );
            }
            $this->_addAttributeReference($code, $code, $entityTypeCode);
            $this->_save($attribute, $attributeKey);
        }
        \Magento\Framework\Profiler::stop('EAV: ' . __METHOD__);
        return $attribute;
    }

    /**
     * Get codes of all entity type attributes
     *
     * @param  mixed $entityType
     * @param  \Magento\Framework\Object $object
     * @return array
     */
    public function getEntityAttributeCodes($entityType, $object = null)
    {
        $entityType = $this->getEntityType($entityType);
        $attributeSetId = 0;
        $storeId = 0;
        if ($object instanceof \Magento\Framework\Object) {
            $attributeSetId = $object->getAttributeSetId() ?: $attributeSetId;
            $storeId = $object->getStoreId() ?: $storeId;
        }
        $cacheKey = self::ATTRIBUTES_CODES_CACHE_ID . $entityType->getId() . '-' . $storeId . '-' . $attributeSetId;
        if (isset($this->_attributeCodes[$cacheKey])) {
            return $this->_attributeCodes[$cacheKey];
        }

        if ($this->_isCacheEnabled() && ($attributes = $this->_cache->load($cacheKey))) {
            $this->_attributeCodes[$cacheKey] = unserialize($attributes);
            return $this->_attributeCodes[$cacheKey];
        }

        if ($attributeSetId) {
            $attributesInfo = $this->_universalFactory->create(
                $entityType->getEntityAttributeCollection()
            )->setEntityTypeFilter(
                $entityType
            )->setAttributeSetFilter(
                $attributeSetId
            )->addStoreLabel(
                $storeId
            )->getData();
            $attributes = array();
            foreach ($attributesInfo as $attributeData) {
                $attributes[] = $attributeData['attribute_code'];
                $this->_createAttribute($entityType, $attributeData);
            }
        } else {
            $this->_initAttributes($entityType);
            $attributes = array_keys($this->_attributeData[$entityType->getEntityTypeCode()]);
        }

        $this->_attributeCodes[$cacheKey] = $attributes;
        if ($this->_isCacheEnabled()) {
            $this->_cache->save(
                serialize($attributes),
                $cacheKey,
                array(
                    \Magento\Eav\Model\Cache\Type::CACHE_TAG,
                    \Magento\Eav\Model\Entity\Attribute::CACHE_TAG
                )
            );
        }

        return $attributes;
    }

    /**
     * Get attribute object for colection usage
     *
     * @param   mixed $entityType
     * @param   string $attribute
     * @return  \Magento\Eav\Model\Entity\Attribute\AbstractAttribute|null
     */
    public function getCollectionAttribute($entityType, $attribute)
    {
        $entityType = $this->getEntityType($entityType);
        $entityTypeCode = $entityType->getEntityTypeCode();

        if (is_numeric($attribute)) {
            $attribute = $this->_getAttributeReference($attribute, $entityTypeCode);
            if (!$attribute) {
                return null;
            }
        }

        $attributeKey = $this->_getAttributeKey($entityTypeCode, $attribute);
        $attributeObject = $this->_load($attributeKey);
        if ($attributeObject) {
            return $attributeObject;
        }

        return $this->getAttribute($entityType, $attribute);
    }

    /**
     * Prepare attributes for usage in EAV collection
     *
     * @param   mixed $entityType
     * @param   array $attributes
     * @return $this
     */
    public function loadCollectionAttributes($entityType, $attributes)
    {
        $entityType = $this->getEntityType($entityType);
        $entityTypeCode = $entityType->getEntityTypeCode();

        if (!isset($this->_collectionAttributes[$entityTypeCode])) {
            $this->_collectionAttributes[$entityTypeCode] = array();
        }
        $loadedAttributes = array_keys($this->_collectionAttributes[$entityTypeCode]);
        $attributes = array_diff($attributes, $loadedAttributes);

        foreach ($attributes as $k => $attribute) {
            if (is_numeric($attribute)) {
                $attribute = $this->_getAttributeReference($attribute, $entityTypeCode);
            }
            $attributeKey = $this->_getAttributeKey($entityTypeCode, $attribute);
            if ($this->_load($attributeKey)) {
                unset($attributes[$k]);
            }
        }

        if (empty($attributes)) {
            return $this;
        }
        $attributeCollection = $entityType->getEntityAttributeCollection();
        $attributesInfo = $this->_universalFactory->create(
            $attributeCollection
        )->useLoadDataFields()->setEntityTypeFilter(
            $entityType
        )->setCodeFilter(
            $attributes
        )->getData();

        foreach ($attributesInfo as $attributeData) {
            $attribute = $this->_createAttribute($entityType, $attributeData);
            $this->_collectionAttributes[$entityTypeCode][$attribute->getAttributeCode()] = $attribute;
        }

        return $this;
    }

    /**
     * Create attribute from attribute data array
     *
     * @param string $entityType
     * @param array $attributeData
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    protected function _createAttribute($entityType, $attributeData)
    {
        $entityType = $this->getEntityType($entityType);
        $entityTypeCode = $entityType->getEntityTypeCode();

        $attributeKey = $this->_getAttributeKey($entityTypeCode, $attributeData['attribute_code']);
        $attribute = $this->_load($attributeKey);
        if ($attribute) {
            $existsFullAttribute = $attribute->hasIsRequired();
            $fullAttributeData = array_key_exists('is_required', $attributeData);

            if ($existsFullAttribute || !$existsFullAttribute && !$fullAttributeData) {
                return $attribute;
            }
        }

        if (!empty($attributeData['attribute_model'])) {
            $model = $attributeData['attribute_model'];
        } else {
            $model = $entityType->getAttributeModel();
        }
        $attribute = $this->_universalFactory->create($model)->setData($attributeData);
        $this->_addAttributeReference(
            $attributeData['attribute_id'],
            $attributeData['attribute_code'],
            $entityTypeCode
        );
        $attributeKey = $this->_getAttributeKey($entityTypeCode, $attributeData['attribute_code']);
        $this->_save($attribute, $attributeKey);

        return $attribute;
    }

    /**
     * Validate attribute data from import
     *
     * @param array $attributeData
     * @return bool
     */
    protected function _validateAttributeData($attributeData = null)
    {
        if (!is_array($attributeData)) {
            return false;
        }
        $requiredKeys = array('attribute_id', 'attribute_code', 'entity_type_id', 'attribute_model');
        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $attributeData)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Import attributes data from external source
     *
     * @param string|Type $entityType
     * @param array $attributes
     * @return $this
     */
    public function importAttributesData($entityType, array $attributes)
    {
        $entityType = $this->getEntityType($entityType);
        foreach ($attributes as $attributeData) {
            if (!$this->_validateAttributeData($attributeData)) {
                continue;
            }
            $this->_createAttribute($entityType, $attributeData);
        }

        return $this;
    }
}
