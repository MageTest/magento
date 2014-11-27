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
namespace Magento\Eav\Model\Entity\Attribute\Backend;

/**
 * Entity/Attribute/Model - attribute backend abstract
 */
abstract class AbstractBackend implements \Magento\Eav\Model\Entity\Attribute\Backend\BackendInterface
{
    /**
     * Reference to the attribute instance
     *
     * @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    protected $_attribute;

    /**
     * PK value_id for loaded entity (for faster updates)
     *
     * @var integer
     */
    protected $_valueId;

    /**
     * PK value_ids for each loaded entity
     *
     * @var array
     */
    protected $_valueIds = array();

    /**
     * Table name for this attribute
     *
     * @var string
     */
    protected $_table;

    /**
     * Name of the entity_id field for the value table of this attribute
     *
     * @var string
     */
    protected $_entityIdField;

    /**
     * Default value for the attribute
     *
     * @var mixed
     */
    protected $_defaultValue = null;

    /**
     * Set attribute instance
     *
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @return $this
     */
    public function setAttribute($attribute)
    {
        $this->_attribute = $attribute;
        return $this;
    }

    /**
     * Get attribute instance
     *
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    public function getAttribute()
    {
        return $this->_attribute;
    }

    /**
     * Get backend type of the attribute
     *
     * @return string
     */
    public function getType()
    {
        return $this->getAttribute()->getBackendType();
    }

    /**
     * Check whether the attribute is a real field in the entity table
     *
     * @return bool
     */
    public function isStatic()
    {
        return $this->getAttribute()->isStatic();
    }

    /**
     * Get table name for the values of the attribute
     *
     * @return string
     */
    public function getTable()
    {
        if (empty($this->_table)) {
            if ($this->isStatic()) {
                $this->_table = $this->getAttribute()->getEntityType()->getValueTablePrefix();
            } elseif ($this->getAttribute()->getBackendTable()) {
                $this->_table = $this->getAttribute()->getBackendTable();
            } else {
                $entity = $this->getAttribute()->getEntity();
                $tableName = sprintf('%s_%s', $entity->getValueTablePrefix(), $this->getType());
                $this->_table = $tableName;
            }
        }

        return $this->_table;
    }

    /**
     * Get entity_id field in the attribute values tables
     *
     * @return string
     */
    public function getEntityIdField()
    {
        if (empty($this->_entityIdField)) {
            if ($this->getAttribute()->getEntityIdField()) {
                $this->_entityIdField = $this->getAttribute()->getEntityIdField();
            } else {
                $this->_entityIdField = $this->getAttribute()->getEntityType()->getValueEntityIdField();
            }
        }

        return $this->_entityIdField;
    }

    /**
     * Set value id
     *
     * @param int $valueId
     * @return $this
     */
    public function setValueId($valueId)
    {
        $this->_valueId = $valueId;
        return $this;
    }

    /**
     * Set entity value id
     *
     * @param \Magento\Framework\Object $entity
     * @param int $valueId
     * @return $this
     */
    public function setEntityValueId($entity, $valueId)
    {
        if (!$entity || !$entity->getId()) {
            return $this->setValueId($valueId);
        }

        $this->_valueIds[$entity->getId()] = $valueId;
        return $this;
    }

    /**
     * Retrieve value id
     *
     * @return int
     */
    public function getValueId()
    {
        return $this->_valueId;
    }

    /**
     * Get entity value id
     *
     * @param \Magento\Framework\Object $entity
     * @return int
     */
    public function getEntityValueId($entity)
    {
        if (!$entity || !$entity->getId() || !array_key_exists($entity->getId(), $this->_valueIds)) {
            return $this->getValueId();
        }

        return $this->_valueIds[$entity->getId()];
    }

    /**
     * Retrieve default value
     *
     * @return mixed
     */
    public function getDefaultValue()
    {
        if ($this->_defaultValue === null) {
            if ($this->getAttribute()->getDefaultValue()) {
                $this->_defaultValue = $this->getAttribute()->getDefaultValue();
            } else {
                $this->_defaultValue = "";
            }
        }

        return $this->_defaultValue;
    }

    /**
     * Validate object
     *
     * @param \Magento\Framework\Object $object
     * @throws \Magento\Eav\Exception
     * @return bool
     */
    public function validate($object)
    {
        $attribute = $this->getAttribute();
        $attrCode = $attribute->getAttributeCode();
        $value = $object->getData($attrCode);
        if ($attribute->getIsVisible() && $attribute->getIsRequired() && $attribute->isValueEmpty($value)) {
            throw new \Magento\Eav\Exception(__('The value of attribute "%1" must be set', $attrCode));
        }

        if ($attribute->getIsUnique()
            && !$attribute->getIsRequired()
            && ($value == '' || $attribute->isValueEmpty($value))
        ) {
            return true;
        }

        if ($attribute->getIsUnique()) {
            if (!$attribute->getEntity()->checkAttributeUniqueValue($attribute, $object)) {
                $label = $attribute->getFrontend()->getLabel();
                throw new \Magento\Eav\Exception(__('The value of attribute "%1" must be unique', $label));
            }
        }

        return true;
    }

    /**
     * After load method
     *
     * @param \Magento\Framework\Object $object
     * @return $this
     */
    public function afterLoad($object)
    {
        return $this;
    }

    /**
     * Before save method
     *
     * @param \Magento\Framework\Object $object
     * @return $this
     */
    public function beforeSave($object)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        if (!$object->hasData($attrCode) && $this->getDefaultValue()) {
            $object->setData($attrCode, $this->getDefaultValue());
        }

        return $this;
    }

    /**
     * After save method
     *
     * @param \Magento\Framework\Object $object
     * @return $this
     */
    public function afterSave($object)
    {
        return $this;
    }

    /**
     * Before delete method
     *
     * @param \Magento\Framework\Object $object
     * @return $this
     */
    public function beforeDelete($object)
    {
        return $this;
    }

    /**
     * After delete method
     *
     * @param \Magento\Framework\Object $object
     * @return $this
     */
    public function afterDelete($object)
    {
        return $this;
    }

    /**
     * Retrieve data for update attribute
     *
     * @param \Magento\Framework\Object $object
     * @return array
     */
    public function getAffectedFields($object)
    {
        $data = array();
        $data[$this->getTable()][] = array(
            'attribute_id' => $this->getAttribute()->getAttributeId(),
            'value_id' => $this->getEntityValueId($object)
        );
        return $data;
    }

    /**
     * By default attribute value is considered scalar that can be stored in a generic way
     *
     * {@inheritdoc}
     */
    public function isScalar()
    {
        return true;
    }
}
