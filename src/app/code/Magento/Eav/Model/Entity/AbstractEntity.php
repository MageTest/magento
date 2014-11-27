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
namespace Magento\Eav\Model\Entity;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Model\Exception;
use Magento\Framework\App\Config\Element;
use Magento\Framework\Model\AbstractModel;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Entity/Attribute/Model - entity abstract
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class AbstractEntity extends \Magento\Framework\Model\Resource\AbstractResource implements EntityInterface
{
    /**
     * Read connection
     *
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql
     */
    protected $_read;

    /**
     * Write connection
     *
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql
     */
    protected $_write;

    /**
     * Entity type configuration
     *
     * @var Type
     */
    protected $_type;

    /**
     * Attributes array by attribute name
     *
     * @var array
     */
    protected $_attributesByCode = array();

    /**
     * Two-dimensional array by table name and attribute name
     *
     * @var array
     */
    protected $_attributesByTable = array();

    /**
     * Attributes that are static fields in entity table
     *
     * @var array
     */
    protected $_staticAttributes = array();

    /**
     * Default Attributes that are static
     *
     * @var array
     */
    protected static $_defaultAttributes = array();

    /**
     * Entity table
     *
     * @var string
     */
    protected $_entityTable;

    /**
     * Describe data for tables
     *
     * @var array
     */
    protected $_describeTable = array();

    /**
     * Entity table identification field name
     *
     * @var string
     */
    protected $_entityIdField;

    /**
     * Entity values table identification field name
     *
     * @var string
     */
    protected $_valueEntityIdField;

    /**
     * Entity value table prefix
     *
     * @var string
     */
    protected $_valueTablePrefix;

    /**
     * Entity table string
     *
     * @var string
     */
    protected $_entityTablePrefix;

    /**
     * Partial load flag
     *
     * @var bool
     */
    protected $_isPartialLoad = false;

    /**
     * Partial save flag
     *
     * @var bool
     */
    protected $_isPartialSave = false;

    /**
     * Attribute set id which used for get sorted attributes
     *
     * @var int
     */
    protected $_sortingSetId = null;

    /**
     * Entity attribute values per backend table to delete
     *
     * @var array
     */
    protected $_attributeValuesToDelete = array();

    /**
     * Entity attribute values per backend table to save
     *
     * @var array
     */
    protected $_attributeValuesToSave = array();

    /**
     * Array of describe attribute backend tables
     * The table name as key
     *
     * @var array
     */
    protected static $_attributeBackendTables = array();

    /**
     * @var \Magento\Framework\App\Resource
     */
    protected $_resource;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\Set
     */
    protected $_attrSetEntity;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $_localeFormat;

    /**
     * @var \Magento\Eav\Model\Resource\Helper
     */
    protected $_resourceHelper;

    /**
     * @var \Magento\Framework\Validator\UniversalFactory
     */
    protected $_universalFactory;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Eav\Model\Entity\Attribute\Set $attrSetEntity
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Eav\Model\Resource\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\Entity\Attribute\Set $attrSetEntity,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Eav\Model\Resource\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        $data = array()
    ) {
        $this->_eavConfig = $eavConfig;
        $this->_resource = $resource;
        $this->_attrSetEntity = $attrSetEntity;
        $this->_localeFormat = $localeFormat;
        $this->_resourceHelper = $resourceHelper;
        $this->_universalFactory = $universalFactory;
        parent::__construct();
        $properties = get_object_vars($this);
        foreach ($data as $key => $value) {
            if (array_key_exists('_' . $key, $properties)) {
                $this->{'_' . $key} = $value;
            }
        }
    }

    /**
     * Set connections for entity operations
     *
     * @param \Zend_Db_Adapter_Abstract|string $read
     * @param \Zend_Db_Adapter_Abstract|string|null $write
     * @return $this
     */
    public function setConnection($read, $write = null)
    {
        $this->_read = $read;
        $this->_write = $write ? $write : $read;

        return $this;
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
    }

    /**
     * Retrieve connection for read data
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function _getReadAdapter()
    {
        if (is_string($this->_read)) {
            $this->_read = $this->_resource->getConnection($this->_read);
        }
        return $this->_read;
    }

    /**
     * Retrieve connection for write data
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function _getWriteAdapter()
    {
        if (is_string($this->_write)) {
            $this->_write = $this->_resource->getConnection($this->_write);
        }
        return $this->_write;
    }

    /**
     * Retrieve read DB connection
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function getReadConnection()
    {
        return $this->_getReadAdapter();
    }

    /**
     * Retrieve write DB connection
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function getWriteConnection()
    {
        return $this->_getWriteAdapter();
    }

    /**
     * For compatibility with AbstractModel
     *
     * @return string
     */
    public function getIdFieldName()
    {
        return $this->getEntityIdField();
    }

    /**
     * Retrieve table name
     *
     * @param string $alias
     * @return string
     */
    public function getTable($alias)
    {
        return $this->_resource->getTableName($alias);
    }

    /**
     * Set configuration for the entity
     *
     * Accepts config node or name of entity type
     *
     * @param string|Type $type
     * @return $this
     */
    public function setType($type)
    {
        $this->_type = $this->_eavConfig->getEntityType($type);
        return $this;
    }

    /**
     * Retrieve current entity config
     *
     * @return Type
     * @throws \Magento\Framework\Model\Exception
     */
    public function getEntityType()
    {
        if (empty($this->_type)) {
            throw new \Magento\Eav\Exception(__('Entity is not initialized'));
        }
        return $this->_type;
    }

    /**
     * Get entity type name
     *
     * @return string
     */
    public function getType()
    {
        return $this->getEntityType()->getEntityTypeCode();
    }

    /**
     * Get entity type id
     *
     * @return int
     */
    public function getTypeId()
    {
        return (int) $this->getEntityType()->getEntityTypeId();
    }

    /**
     * Unset attributes
     *
     * If NULL or not supplied removes configuration of all attributes
     * If string - removes only one, if array - all specified
     *
     * @param array|string|null $attributes
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    public function unsetAttributes($attributes = null)
    {
        if ($attributes === null) {
            $this->_attributesByCode = array();
            $this->_attributesByTable = array();
            return $this;
        }

        if (is_string($attributes)) {
            $attributes = array($attributes);
        }

        if (!is_array($attributes)) {
            throw new \Magento\Eav\Exception(__('Unknown parameter'));
        }

        foreach ($attributes as $attrCode) {
            if (!isset($this->_attributesByCode[$attrCode])) {
                continue;
            }

            $attr = $this->getAttribute($attrCode);
            unset($this->_attributesByTable[$attr->getBackend()->getTable()][$attrCode]);
            unset($this->_attributesByCode[$attrCode]);
        }

        return $this;
    }

    /**
     * Get EAV config model
     *
     * @return \Magento\Eav\Model\Config
     */
    protected function _getConfig()
    {
        return $this->_eavConfig;
    }

    /**
     * Retrieve attribute instance by name, id or config node
     *
     * This will add the attribute configuration to entity's attributes cache
     *
     * If attribute is not found false is returned
     *
     * @param string|int|Element $attribute
     * @return AbstractAttribute|false
     */
    public function getAttribute($attribute)
    {
        /** @var $config \Magento\Eav\Model\Config */
        $config = $this->_getConfig();
        if (is_numeric($attribute)) {
            $attributeId = $attribute;
            $attributeInstance = $config->getAttribute($this->getEntityType(), $attributeId);
            if ($attributeInstance) {
                $attributeCode = $attributeInstance->getAttributeCode();
            }
        } elseif (is_string($attribute)) {
            $attributeCode = $attribute;
            $attributeInstance = $config->getAttribute($this->getEntityType(), $attributeCode);
            if (!$attributeInstance->getAttributeCode() && in_array($attribute, $this->getDefaultAttributes())) {
                $attributeInstance->setAttributeCode(
                    $attribute
                )->setBackendType(
                    AbstractAttribute::TYPE_STATIC
                )->setIsGlobal(
                    1
                )->setEntity(
                    $this
                )->setEntityType(
                    $this->getEntityType()
                )->setEntityTypeId(
                    $this->getEntityType()->getId()
                );
            }
        } elseif ($attribute instanceof AbstractAttribute) {
            $attributeInstance = $attribute;
            $attributeCode = $attributeInstance->getAttributeCode();
        }

        if (empty($attributeInstance)
            || !$attributeInstance instanceof AbstractAttribute
            || !$attributeInstance->getId()
            && !in_array($attributeInstance->getAttributeCode(), $this->getDefaultAttributes())
        ) {
            return false;
        }

        $attribute = $attributeInstance;

        if (!$attribute->getAttributeCode()) {
            $attribute->setAttributeCode($attributeCode);
        }
        if (!$attribute->getAttributeModel()) {
            $attribute->setAttributeModel($this->_getDefaultAttributeModel());
        }

        $this->addAttribute($attribute);

        return $attribute;
    }

    /**
     * Return default static virtual attribute that doesn't exists in EAV attributes
     *
     * @param string $attributeCode
     * @return Attribute
     */
    protected function _getDefaultAttribute($attributeCode)
    {
        $entityTypeId = $this->getEntityType()->getId();
        if (!isset(self::$_defaultAttributes[$entityTypeId][$attributeCode])) {
            $attribute = $this->_universalFactory->create(
                $this->getEntityType()->getAttributeModel()
            )->setAttributeCode(
                $attributeCode
            )->setBackendType(
                AbstractAttribute::TYPE_STATIC
            )->setIsGlobal(
                1
            )->setEntityType(
                $this->getEntityType()
            )->setEntityTypeId(
                $this->getEntityType()->getId()
            );
            self::$_defaultAttributes[$entityTypeId][$attributeCode] = $attribute;
        }

        return self::$_defaultAttributes[$entityTypeId][$attributeCode];
    }

    /**
     * Adding attribute to entity
     *
     * @param AbstractAttribute $attribute
     * @return $this
     */
    public function addAttribute(AbstractAttribute $attribute)
    {
        $attribute->setEntity($this);
        $attributeCode = $attribute->getAttributeCode();

        $this->_attributesByCode[$attributeCode] = $attribute;

        if ($attribute->isStatic()) {
            $this->_staticAttributes[$attributeCode] = $attribute;
        } else {
            $this->_attributesByTable[$attribute->getBackendTable()][$attributeCode] = $attribute;
        }

        return $this;
    }

    /**
     * Retrieve partial load flag
     *
     * @param bool $flag
     * @return bool
     */
    public function isPartialLoad($flag = null)
    {
        $result = $this->_isPartialLoad;
        if ($flag !== null) {
            $this->_isPartialLoad = (bool)$flag;
        }
        return $result;
    }

    /**
     * Retrieve partial save flag
     *
     * @param bool $flag
     * @return bool
     */
    public function isPartialSave($flag = null)
    {
        $result = $this->_isPartialSave;
        if ($flag !== null) {
            $this->_isPartialSave = (bool) $flag;
        }
        return $result;
    }

    /**
     * Retrieve configuration for all attributes
     *
     * @param null|\Magento\Framework\Object $object
     * @return $this
     */
    public function loadAllAttributes($object = null)
    {
        $attributeCodes = $this->_eavConfig->getEntityAttributeCodes($this->getEntityType(), $object);

        /**
         * Check and init default attributes
         */
        $defaultAttributes = $this->getDefaultAttributes();
        foreach ($defaultAttributes as $attributeCode) {
            $attributeIndex = array_search($attributeCode, $attributeCodes);
            if ($attributeIndex !== false) {
                $this->getAttribute($attributeCodes[$attributeIndex]);
                unset($attributeCodes[$attributeIndex]);
            } else {
                $this->addAttribute($this->_getDefaultAttribute($attributeCode));
            }
        }

        foreach ($attributeCodes as $code) {
            $this->getAttribute($code);
        }

        return $this;
    }

    /**
     * Retrieve sorted attributes
     *
     * @param int $setId
     * @return array
     */
    public function getSortedAttributes($setId = null)
    {
        $attributes = $this->getAttributesByCode();
        if ($setId === null) {
            $setId = $this->getEntityType()->getDefaultAttributeSetId();
        }

        // initialize set info
        $this->_attrSetEntity->addSetInfo($this->getEntityType(), $attributes, $setId);

        foreach ($attributes as $code => $attribute) {
            /* @var $attribute AbstractAttribute */
            if (!$attribute->isInSet($setId)) {
                unset($attributes[$code]);
            }
        }

        $this->_sortingSetId = $setId;
        uasort($attributes, array($this, 'attributesCompare'));
        return $attributes;
    }

    /**
     * Compare attributes
     *
     * @param Attribute $firstAttribute
     * @param Attribute $secondAttribute
     * @return int
     */
    public function attributesCompare($firstAttribute, $secondAttribute)
    {
        $firstSort = $firstAttribute->getSortWeight((int) $this->_sortingSetId);
        $secondSort = $secondAttribute->getSortWeight((int) $this->_sortingSetId);

        if ($firstSort > $secondSort) {
            return 1;
        } elseif ($firstSort < $secondSort) {
            return -1;
        }

        return 0;
    }

    /**
     * Check whether the attribute is Applicable to the object
     *
     * @param   \Magento\Framework\Object $object
     * @param   AbstractAttribute $attribute
     * @return  bool
     */
    protected function _isApplicableAttribute($object, $attribute)
    {
        return true;
    }

    /**
     * Walk through the attributes and run method with optional arguments
     *
     * Returns array with results for each attribute
     *
     * if $partMethod is in format "part/method" will run method on specified part
     * for example: $this->walkAttributes('backend/validate');
     *
     * @param string $partMethod
     * @param array $args
     * @param null|bool $collectExceptionMessages
     *
     * @throws \Exception|\Magento\Eav\Model\Entity\Attribute\Exception
     * @return array
     */
    public function walkAttributes($partMethod, array $args = array(), $collectExceptionMessages = null)
    {
        $methodArr = explode('/', $partMethod);
        switch (sizeof($methodArr)) {
            case 1:
                $part = 'attribute';
                $method = $methodArr[0];
                break;

            case 2:
                $part = $methodArr[0];
                $method = $methodArr[1];
                break;

            default:
                break;
        }
        $results = array();
        foreach ($this->getAttributesByCode() as $attrCode => $attribute) {
            if (isset($args[0]) && is_object($args[0]) && !$this->_isApplicableAttribute($args[0], $attribute)) {
                continue;
            }

            switch ($part) {
                case 'attribute':
                    $instance = $attribute;
                    break;

                case 'backend':
                    $instance = $attribute->getBackend();
                    break;

                case 'frontend':
                    $instance = $attribute->getFrontend();
                    break;

                case 'source':
                    $instance = $attribute->getSource();
                    break;

                default:
                    break;
            }

            if (!$this->_isCallableAttributeInstance($instance, $method, $args)) {
                continue;
            }

            try {
                $results[$attrCode] = call_user_func_array(array($instance, $method), $args);
            } catch (\Magento\Eav\Model\Entity\Attribute\Exception $e) {
                if ($collectExceptionMessages) {
                    $results[$attrCode] = $e->getMessage();
                } else {
                    throw $e;
                }
            } catch (\Exception $e) {
                if ($collectExceptionMessages) {
                    $results[$attrCode] = $e->getMessage();
                } else {
                    /** @var \Magento\Eav\Model\Entity\Attribute\Exception $e */
                    $e = $this->_universalFactory->create(
                        'Magento\Eav\Model\Entity\Attribute\Exception',
                        array('message' => $e->getMessage())
                    );
                    $e->setAttributeCode($attrCode)->setPart($part);
                    throw $e;
                }
            }
        }

        return $results;
    }

    /**
     * Check whether attribute instance (attribute, backend, frontend or source) has method and applicable
     *
     * @param AbstractAttribute|AbstractBackend|AbstractFrontend|AbstractSource $instance
     * @param string $method
     * @param array $args array of arguments
     * @return bool
     */
    protected function _isCallableAttributeInstance($instance, $method, $args)
    {
        if (!is_object($instance) || !method_exists($instance, $method) || !is_callable([$instance, $method])) {
            return false;
        }

        return true;
    }

    /**
     * Get attributes by name array
     *
     * @return array
     */
    public function getAttributesByCode()
    {
        return $this->_attributesByCode;
    }

    /**
     * Get attributes by table and name array
     *
     * @return array
     */
    public function getAttributesByTable()
    {
        return $this->_attributesByTable;
    }

    /**
     * Get entity table name
     *
     * @return string
     */
    public function getEntityTable()
    {
        if (!$this->_entityTable) {
            $table = $this->getEntityType()->getEntityTable();
            if (!$table) {
                $table = \Magento\Eav\Model\Entity::DEFAULT_ENTITY_TABLE;
            }
            $this->_entityTable = $this->_resource->getTableName($table);
        }

        return $this->_entityTable;
    }

    /**
     * Get entity id field name in entity table
     *
     * @return string
     */
    public function getEntityIdField()
    {
        if (!$this->_entityIdField) {
            $this->_entityIdField = $this->getEntityType()->getEntityIdField();
            if (!$this->_entityIdField) {
                $this->_entityIdField = \Magento\Eav\Model\Entity::DEFAULT_ENTITY_ID_FIELD;
            }
        }

        return $this->_entityIdField;
    }

    /**
     * Get default entity id field name in attribute values tables
     *
     * @return string
     */
    public function getValueEntityIdField()
    {
        return $this->getEntityIdField();
    }

    /**
     * Get prefix for value tables
     *
     * @return string
     */
    public function getValueTablePrefix()
    {
        if (!$this->_valueTablePrefix) {
            $prefix = (string) $this->getEntityType()->getValueTablePrefix();
            if (!empty($prefix)) {
                $this->_valueTablePrefix = $prefix;
                /**
                 * entity type prefix include DB table name prefix
                 */
                //$this->_resource->getTableName($prefix);
            } else {
                $this->_valueTablePrefix = $this->getEntityTable();
            }
        }

        return $this->_valueTablePrefix;
    }

    /**
     * Get entity table prefix for value
     *
     * @return string
     */
    public function getEntityTablePrefix()
    {
        if (empty($this->_entityTablePrefix)) {
            $prefix = $this->getEntityType()->getEntityTablePrefix();
            if (empty($prefix)) {
                $prefix = $this->getEntityType()->getEntityTable();
                if (empty($prefix)) {
                    $prefix = \Magento\Eav\Model\Entity::DEFAULT_ENTITY_TABLE;
                }
            }
            $this->_entityTablePrefix = $prefix;
        }

        return $this->_entityTablePrefix;
    }

    /**
     * Check whether the attribute is a real field in entity table
     *
     * @see \Magento\Eav\Model\Entity\AbstractEntity::getAttribute for $attribute format
     * @param int|string|AbstractAttribute $attribute
     * @return bool
     */
    public function isAttributeStatic($attribute)
    {
        $attrInstance = $this->getAttribute($attribute);
        return $attrInstance && $attrInstance->getBackend()->isStatic();
    }

    /**
     * Validate all object's attributes against configuration
     *
     * @param \Magento\Framework\Object $object
     * @throws \Magento\Eav\Model\Entity\Attribute\Exception
     * @return bool|array
     */
    public function validate($object)
    {
        $this->loadAllAttributes($object);
        $result = $this->walkAttributes('backend/validate', array($object), $object->getCollectExceptionMessages());
        $errors = array();
        foreach ($result as $attributeCode => $error) {
            if ($error === false) {
                $errors[$attributeCode] = true;
            } elseif (is_string($error)) {
                $errors[$attributeCode] = $error;
            }
        }
        if (!$errors) {
            return true;
        }

        return $errors;
    }

    /**
     * Set new increment id to object
     *
     * @param \Magento\Framework\Object $object
     * @return $this
     */
    public function setNewIncrementId(\Magento\Framework\Object $object)
    {
        if ($object->getIncrementId()) {
            return $this;
        }

        $incrementId = $this->getEntityType()->fetchNewIncrementId($object->getStoreId());

        if ($incrementId !== false) {
            $object->setIncrementId($incrementId);
        }

        return $this;
    }

    /**
     * Check attribute unique value
     *
     * @param AbstractAttribute $attribute
     * @param \Magento\Framework\Object $object
     * @return bool
     */
    public function checkAttributeUniqueValue(AbstractAttribute $attribute, $object)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select();
        if ($attribute->getBackend()->getType() === 'static') {
            $value = $object->getData($attribute->getAttributeCode());
            $bind = array('entity_type_id' => $this->getTypeId(), 'attribute_code' => trim($value));

            $select->from(
                $this->getEntityTable(),
                $this->getEntityIdField()
            )->where(
                'entity_type_id = :entity_type_id'
            )->where(
                $attribute->getAttributeCode() . ' = :attribute_code'
            );
        } else {
            $value = $object->getData($attribute->getAttributeCode());
            if ($attribute->getBackend()->getType() == 'datetime') {
                $date = new \Magento\Framework\Stdlib\DateTime\Date($value, \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT);
                $value = $date->toString(\Magento\Framework\Stdlib\DateTime::DATETIME_INTERNAL_FORMAT);
            }
            $bind = array(
                'entity_type_id' => $this->getTypeId(),
                'attribute_id' => $attribute->getId(),
                'value' => trim($value)
            );
            $select->from(
                $attribute->getBackend()->getTable(),
                $attribute->getBackend()->getEntityIdField()
            )->where(
                'entity_type_id = :entity_type_id'
            )->where(
                'attribute_id = :attribute_id'
            )->where(
                'value = :value'
            );
        }
        $data = $adapter->fetchCol($select, $bind);

        if ($object->getId()) {
            if (isset($data[0])) {
                return $data[0] == $object->getId();
            }
            return true;
        }

        return !count($data);
    }

    /**
     * Retrieve default source model
     *
     * @return string
     */
    public function getDefaultAttributeSourceModel()
    {
        return \Magento\Eav\Model\Entity::DEFAULT_SOURCE_MODEL;
    }

    /**
     * Load entity's attributes into the object
     *
     * @param   AbstractModel $object
     * @param   int $entityId
     * @param   array|null $attributes
     * @return $this
     */
    public function load($object, $entityId, $attributes = array())
    {
        \Magento\Framework\Profiler::start('EAV:load_entity');
        /**
         * Load object base row data
         */
        $select = $this->_getLoadRowSelect($object, $entityId);
        $row = $this->_getReadAdapter()->fetchRow($select);

        if (is_array($row)) {
            $object->addData($row);
        } else {
            $object->isObjectNew(true);
        }

        if (empty($attributes)) {
            $this->loadAllAttributes($object);
        } else {
            foreach ($attributes as $attrCode) {
                $this->getAttribute($attrCode);
            }
        }

        $this->_loadModelAttributes($object);

        $object->setOrigData();

        $this->_afterLoad($object);

        \Magento\Framework\Profiler::stop('EAV:load_entity');
        return $this;
    }

    /**
     * Load model attributes data
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _loadModelAttributes($object)
    {
        if (!$object->getId()) {
            return $this;
        }

        \Magento\Framework\Profiler::start('load_model_attributes');

        $selects = array();
        foreach (array_keys($this->getAttributesByTable()) as $table) {
            $attribute = current($this->_attributesByTable[$table]);
            $eavType = $attribute->getBackendType();
            $select = $this->_getLoadAttributesSelect($object, $table);
            $selects[$eavType][] = $select->columns('*');
        }
        $selectGroups = $this->_resourceHelper->getLoadAttributesSelectGroups($selects);
        foreach ($selectGroups as $selects) {
            if (!empty($selects)) {
                $select = $this->_prepareLoadSelect($selects);
                $values = $this->_getReadAdapter()->fetchAll($select);
                foreach ($values as $valueRow) {
                    $this->_setAttributeValue($object, $valueRow);
                }
            }
        }

        \Magento\Framework\Profiler::stop('load_model_attributes');

        return $this;
    }

    /**
     * Prepare select object for loading entity attributes values
     *
     * @param  array $selects
     * @return \Zend_Db_Select
     */
    protected function _prepareLoadSelect(array $selects)
    {
        return $this->_getReadAdapter()->select()->union($selects, \Zend_Db_Select::SQL_UNION_ALL);
    }

    /**
     * Retrieve select object for loading base entity row
     *
     * @param   \Magento\Framework\Object $object
     * @param   string|int $rowId
     * @return  \Zend_Db_Select
     */
    protected function _getLoadRowSelect($object, $rowId)
    {
        $select = $this->_getReadAdapter()->select()->from(
            $this->getEntityTable()
        )->where(
            $this->getEntityIdField() . ' =?',
            $rowId
        );

        return $select;
    }

    /**
     * Retrieve select object for loading entity attributes values
     *
     * @param   \Magento\Framework\Object $object
     * @param   string $table
     * @return  \Zend_Db_Select
     */
    protected function _getLoadAttributesSelect($object, $table)
    {
        $select = $this->_getReadAdapter()->select()->from(
            $table,
            array()
        )->where(
            $this->getEntityIdField() . ' =?',
            $object->getId()
        );

        return $select;
    }

    /**
     * Initialize attribute value for object
     *
     * @param   \Magento\Framework\Object $object
     * @param   array $valueRow
     * @return $this
     */
    protected function _setAttributeValue($object, $valueRow)
    {
        $attribute = $this->getAttribute($valueRow['attribute_id']);
        if ($attribute) {
            $attributeCode = $attribute->getAttributeCode();
            $object->setData($attributeCode, $valueRow['value']);
            $attribute->getBackend()->setEntityValueId($object, $valueRow['value_id']);
        }

        return $this;
    }

    /**
     * Save entity's attributes into the object's resource
     *
     * @param   \Magento\Framework\Object $object
     * @return $this
     */
    public function save(\Magento\Framework\Object $object)
    {
        if ($object->isDeleted()) {
            return $this->delete($object);
        }

        if (!$this->isPartialSave()) {
            $this->loadAllAttributes($object);
        }

        if (!$object->getEntityTypeId()) {
            $object->setEntityTypeId($this->getTypeId());
        }

        $object->setParentId((int)$object->getParentId());

        $this->_beforeSave($object);
        $this->_processSaveData($this->_collectSaveData($object));
        $this->_afterSave($object);

        return $this;
    }

    /**
     * Retrieve Object instance with original data
     *
     * @param \Magento\Framework\Object $object
     * @return \Magento\Framework\Object
     */
    protected function _getOrigObject($object)
    {
        $className = get_class($object);
        $origObject = $this->_universalFactory->create($className);
        $origObject->setData(array());
        $this->load($origObject, $object->getData($this->getEntityIdField()));

        return $origObject;
    }

    /**
     * Aggregate Data for attributes that will be deleted
     *
     * @param array &$delete
     * @param AbstractAttribute $attribute
     * @param \Magento\Eav\Model\Entity\AbstractEntity $object
     * @return void
     */
    private function _aggregateDeleteData(&$delete, $attribute, $object)
    {
        foreach ($attribute->getBackend()->getAffectedFields($object) as $tableName => $valuesData) {
            if (!isset($delete[$tableName])) {
                $delete[$tableName] = array();
            }
            $delete[$tableName] = array_merge((array)$delete[$tableName], $valuesData);
        }
    }

    /**
     * Prepare entity object data for save
     *
     * Result array structure:
     * array (
     *  'newObject', 'entityRow', 'insert', 'update', 'delete'
     * )
     *
     * @param   \Magento\Framework\Object $newObject
     * @return  array
     */
    protected function _collectSaveData($newObject)
    {
        $newData = $newObject->getData();
        $entityId = $newObject->getData($this->getEntityIdField());

        // define result data
        $entityRow = array();
        $insert = array();
        $update = array();
        $delete = array();

        if (!empty($entityId)) {
            $origData = $newObject->getOrigData();
            /**
             * get current data in db for this entity if original data is empty
             */
            if (empty($origData)) {
                $origData = $this->_getOrigObject($newObject)->getOrigData();
            }

            if (is_null($origData)) {
                $origData = array();
            }

            /**
             * drop attributes that are unknown in new data
             * not needed after introduction of partial entity loading
             */
            foreach ($origData as $k => $v) {
                if (!array_key_exists($k, $newData)) {
                    unset($origData[$k]);
                }
            }
        } else {
            $origData = array();
        }

        $staticFields = $this->_getWriteAdapter()->describeTable($this->getEntityTable());
        $staticFields = array_keys($staticFields);
        $attributeCodes = array_keys($this->_attributesByCode);

        foreach ($newData as $k => $v) {
            /**
             * Check if data key is presented in static fields or attribute codes
             */
            if (!in_array($k, $staticFields) && !in_array($k, $attributeCodes)) {
                continue;
            }

            $attribute = $this->getAttribute($k);
            if (empty($attribute)) {
                continue;
            }

            if (!$attribute->isInSet($newObject->getAttributeSetId()) && !in_array($k, $staticFields)) {
                $this->_aggregateDeleteData($delete, $attribute, $newObject);
            }

            $attrId = $attribute->getAttributeId();

            /**
             * Only scalar values can be stored in generic tables
             */
            if (!$attribute->getBackend()->isScalar()) {
                continue;
            }


            /**
             * if attribute is static add to entity row and continue
             */
            if ($this->isAttributeStatic($k)) {
                $entityRow[$k] = $this->_prepareStaticValue($k, $v);
                continue;
            }

            /**
             * Check comparability for attribute value
             */
            if ($this->_canUpdateAttribute($attribute, $v, $origData)) {
                if ($this->_isAttributeValueEmpty($attribute, $v)) {
                    $this->_aggregateDeleteData($delete, $attribute, $newObject);
                } elseif (!is_numeric($v) && $v !== $origData[$k] || is_numeric($v) && $v != $origData[$k]) {
                    $update[$attrId] = array(
                        'value_id' => $attribute->getBackend()->getEntityValueId($newObject),
                        'value' => $v
                    );
                }
            } elseif (!$this->_isAttributeValueEmpty($attribute, $v)) {
                $insert[$attrId] = $v;
            }
        }

        $result = compact('newObject', 'entityRow', 'insert', 'update', 'delete');
        return $result;
    }

    /**
     * Return if attribute exists in original data array.
     *
     * @param AbstractAttribute $attribute
     * @param mixed $v New value of the attribute. Can be used in subclasses.
     * @param array $origData
     * @return bool
     */
    protected function _canUpdateAttribute(AbstractAttribute $attribute, $v, array &$origData)
    {
        return array_key_exists($attribute->getAttributeCode(), $origData);
    }

    /**
     * Retrieve static field properties
     *
     * @param string $field
     * @return array
     */
    protected function _getStaticFieldProperties($field)
    {
        if (empty($this->_describeTable[$this->getEntityTable()])) {
            $this->_describeTable[$this->getEntityTable()] = $this->_getWriteAdapter()->describeTable(
                $this->getEntityTable()
            );
        }

        if (isset($this->_describeTable[$this->getEntityTable()][$field])) {
            return $this->_describeTable[$this->getEntityTable()][$field];
        }

        return false;
    }

    /**
     * Prepare static value for save
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function _prepareStaticValue($key, $value)
    {
        $fieldProp = $this->_getStaticFieldProperties($key);

        if (!$fieldProp) {
            return $value;
        }

        if ($fieldProp['DATA_TYPE'] == 'decimal') {
            $value = $this->_localeFormat->getNumber($value);
        }

        return $value;
    }

    /**
     * Save object collected data
     *
     * @param   array $saveData array('newObject', 'entityRow', 'insert', 'update', 'delete')
     * @return $this
     */
    protected function _processSaveData($saveData)
    {
        extract($saveData);
        /**
         * Import variables into the current symbol table from save data array
         *
         * @see \Magento\Eav\Model\Entity\AbstractEntity::_collectSaveData()
         *
         * @var array $entityRow
         * @var \Magento\Framework\Model\AbstractModel $newObject
         * @var array $insert
         * @var array $update
         * @var array $delete
         */
        $adapter = $this->_getWriteAdapter();
        $insertEntity = true;
        $entityTable = $this->getEntityTable();
        $entityIdField = $this->getEntityIdField();
        $entityId = $newObject->getId();

        unset($entityRow[$entityIdField]);
        if (!empty($entityId) && is_numeric($entityId)) {
            $bind = array('entity_id' => $entityId);
            $select = $adapter->select()->from($entityTable, $entityIdField)->where("{$entityIdField} = :entity_id");
            $result = $adapter->fetchOne($select, $bind);
            if ($result) {
                $insertEntity = false;
            }
        } else {
            $entityId = null;
        }

        /**
         * Process base row
         */
        $entityObject = new \Magento\Framework\Object($entityRow);
        $entityRow = $this->_prepareDataForTable($entityObject, $entityTable);
        if ($insertEntity) {
            if (!empty($entityId)) {
                $entityRow[$entityIdField] = $entityId;
                $adapter->insertForce($entityTable, $entityRow);
            } else {
                $adapter->insert($entityTable, $entityRow);
                $entityId = $adapter->lastInsertId($entityTable);
            }
            $newObject->setId($entityId);
        } else {
            $where = sprintf('%s=%d', $adapter->quoteIdentifier($entityIdField), $entityId);
            $adapter->update($entityTable, $entityRow, $where);
        }

        /**
         * insert attribute values
         */
        if (!empty($insert)) {
            foreach ($insert as $attributeId => $value) {
                $attribute = $this->getAttribute($attributeId);
                $this->_insertAttribute($newObject, $attribute, $value);
            }
        }

        /**
         * update attribute values
         */
        if (!empty($update)) {
            foreach ($update as $attributeId => $v) {
                $attribute = $this->getAttribute($attributeId);
                $this->_updateAttribute($newObject, $attribute, $v['value_id'], $v['value']);
            }
        }

        /**
         * delete empty attribute values
         */
        if (!empty($delete)) {
            foreach ($delete as $table => $values) {
                $this->_deleteAttributes($newObject, $table, $values);
            }
        }

        $this->_processAttributeValues();

        $newObject->isObjectNew(false);

        return $this;
    }

    /**
     * Insert entity attribute value
     *
     * @param   \Magento\Framework\Object $object
     * @param   AbstractAttribute $attribute
     * @param   mixed $value
     * @return $this
     */
    protected function _insertAttribute($object, $attribute, $value)
    {
        return $this->_saveAttribute($object, $attribute, $value);
    }

    /**
     * Update entity attribute value
     *
     * @param   \Magento\Framework\Object $object
     * @param   AbstractAttribute $attribute
     * @param   mixed $valueId
     * @param   mixed $value
     * @return $this
     */
    protected function _updateAttribute($object, $attribute, $valueId, $value)
    {
        return $this->_saveAttribute($object, $attribute, $value);
    }

    /**
     * Save entity attribute value
     *
     * Collect for mass save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param AbstractAttribute $attribute
     * @param mixed $value
     * @return $this
     */
    protected function _saveAttribute($object, $attribute, $value)
    {
        $table = $attribute->getBackend()->getTable();
        if (!isset($this->_attributeValuesToSave[$table])) {
            $this->_attributeValuesToSave[$table] = array();
        }

        $entityIdField = $attribute->getBackend()->getEntityIdField();

        $data = array(
            'entity_type_id' => $object->getEntityTypeId(),
            $entityIdField => $object->getId(),
            'attribute_id' => $attribute->getId(),
            'value' => $this->_prepareValueForSave($value, $attribute)
        );

        $this->_attributeValuesToSave[$table][] = $data;

        return $this;
    }

    /**
     * Save and delete collected attribute values
     *
     * @return $this
     */
    protected function _processAttributeValues()
    {
        $adapter = $this->_getWriteAdapter();
        foreach ($this->_attributeValuesToSave as $table => $data) {
            $adapter->insertOnDuplicate($table, $data, array('value'));
        }

        foreach ($this->_attributeValuesToDelete as $table => $valueIds) {
            $adapter->delete($table, array('value_id IN (?)' => $valueIds));
        }

        // reset data arrays
        $this->_attributeValuesToSave = array();
        $this->_attributeValuesToDelete = array();

        return $this;
    }

    /**
     * Prepare value for save
     *
     * @param mixed $value
     * @param AbstractAttribute $attribute
     * @return mixed
     */
    protected function _prepareValueForSave($value, AbstractAttribute $attribute)
    {
        $type = $attribute->getBackendType();
        if (($type == 'int' || $type == 'decimal' || $type == 'datetime') && $value === '') {
            $value = null;
        } elseif ($type == 'decimal') {
            $value = $this->_localeFormat->getNumber($value);
        }
        $backendTable = $attribute->getBackendTable();
        if (!isset(self::$_attributeBackendTables[$backendTable])) {
            self::$_attributeBackendTables[$backendTable] = $this->_getReadAdapter()->describeTable($backendTable);
        }
        $describe = self::$_attributeBackendTables[$backendTable];
        return $this->_getReadAdapter()->prepareColumnValue($describe['value'], $value);
    }

    /**
     * Delete entity attribute values
     *
     * @param   \Magento\Framework\Object $object
     * @param   string $table
     * @param   array $info
     * @return  \Magento\Framework\Object
     */
    protected function _deleteAttributes($object, $table, $info)
    {
        $valueIds = array();
        foreach ($info as $itemData) {
            $valueIds[] = $itemData['value_id'];
        }

        if (empty($valueIds)) {
            return $this;
        }

        if (isset($this->_attributeValuesToDelete[$table])) {
            $this->_attributeValuesToDelete[$table] = array_merge($this->_attributeValuesToDelete[$table], $valueIds);
        } else {
            $this->_attributeValuesToDelete[$table] = $valueIds;
        }

        return $this;
    }

    /**
     * Save attribute
     *
     * @param \Magento\Framework\Object $object
     * @param string $attributeCode
     * @return $this
     * @throws \Exception
     */
    public function saveAttribute(\Magento\Framework\Object $object, $attributeCode)
    {
        $attribute = $this->getAttribute($attributeCode);
        $backend = $attribute->getBackend();
        $table = $backend->getTable();
        $entity = $attribute->getEntity();
        $entityIdField = $entity->getEntityIdField();
        $adapter = $this->_getWriteAdapter();

        $row = array(
            'entity_type_id' => $entity->getTypeId(),
            'attribute_id' => $attribute->getId(),
            $entityIdField => $object->getData($entityIdField)
        );

        $newValue = $object->getData($attributeCode);
        if ($attribute->isValueEmpty($newValue)) {
            $newValue = null;
        }

        $whereArr = array();
        foreach ($row as $field => $value) {
            $whereArr[] = $adapter->quoteInto($field . '=?', $value);
        }
        $where = implode(' AND ', $whereArr);

        $adapter->beginTransaction();

        try {
            $select = $adapter->select()->from($table, 'value_id')->where($where);
            $origValueId = $adapter->fetchOne($select);

            if ($origValueId === false && $newValue !== null) {
                $this->_insertAttribute($object, $attribute, $newValue);
            } elseif ($origValueId !== false && $newValue !== null) {
                $this->_updateAttribute($object, $attribute, $origValueId, $newValue);
            } elseif ($origValueId !== false && $newValue === null) {
                $adapter->delete($table, $where);
            }
            $this->_processAttributeValues();
            $adapter->commit();
        } catch (\Exception $e) {
            $adapter->rollback();
            throw $e;
        }

        return $this;
    }

    /**
     * Delete entity using current object's data
     *
     * @param \Magento\Framework\Object|int|string $object
     * @return $this
     * @throws \Exception
     */
    public function delete($object)
    {
        if (is_numeric($object)) {
            $id = (int) $object;
        } elseif ($object instanceof \Magento\Framework\Object) {
            $id = (int) $object->getId();
        }

        $this->_beforeDelete($object);

        try {
            $where = array($this->getEntityIdField() . '=?' => $id);
            $this->_getWriteAdapter()->delete($this->getEntityTable(), $where);
            $this->loadAllAttributes($object);
            foreach ($this->getAttributesByTable() as $table => $attributes) {
                $this->_getWriteAdapter()->delete($table, $where);
            }
        } catch (\Exception $e) {
            throw $e;
        }

        $this->_afterDelete($object);
        return $this;
    }

    /**
     * After Load Entity process
     *
     * @param \Magento\Framework\Object $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Object $object)
    {
        \Magento\Framework\Profiler::start('after_load');
        $this->walkAttributes('backend/afterLoad', array($object));
        \Magento\Framework\Profiler::stop('after_load');
        return $this;
    }

    /**
     * Before delete Entity process
     *
     * @param \Magento\Framework\Object $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Object $object)
    {
        $this->walkAttributes('backend/beforeSave', array($object));
        return $this;
    }

    /**
     * After Save Entity process
     *
     * @param \Magento\Framework\Object $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Object $object)
    {
        $this->walkAttributes('backend/afterSave', array($object));
        return $this;
    }

    /**
     * Before Delete Entity process
     *
     * @param \Magento\Framework\Object $object
     * @return $this
     */
    protected function _beforeDelete(\Magento\Framework\Object $object)
    {
        $this->walkAttributes('backend/beforeDelete', array($object));
        return $this;
    }

    /**
     * After delete entity process
     *
     * @param \Magento\Framework\Object $object
     * @return $this
     */
    protected function _afterDelete(\Magento\Framework\Object $object)
    {
        $this->walkAttributes('backend/afterDelete', array($object));
        return $this;
    }

    /**
     * Retrieve Default attribute model
     *
     * @return string
     */
    protected function _getDefaultAttributeModel()
    {
        return \Magento\Eav\Model\Entity::DEFAULT_ATTRIBUTE_MODEL;
    }

    /**
     * Retrieve default entity attributes
     *
     * @return string[]
     */
    protected function _getDefaultAttributes()
    {
        return array('entity_type_id', 'attribute_set_id', 'created_at', 'updated_at', 'parent_id', 'increment_id');
    }

    /**
     * Retrieve default entity static attributes
     *
     * @return string[]
     */
    public function getDefaultAttributes()
    {
        return array_unique(array_merge($this->_getDefaultAttributes(), array($this->getEntityIdField())));
    }

    /**
     * Check is attribute value empty
     *
     * @param AbstractAttribute $attribute
     * @param mixed $value
     * @return bool
     */
    protected function _isAttributeValueEmpty(AbstractAttribute $attribute, $value)
    {
        return $attribute->isValueEmpty($value);
    }
}
