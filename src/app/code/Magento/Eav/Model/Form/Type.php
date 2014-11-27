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
namespace Magento\Eav\Model\Form;

/**
 * Eav Form Type Model
 *
 * @method \Magento\Eav\Model\Resource\Form\Type getResource()
 * @method string getCode()
 * @method \Magento\Eav\Model\Form\Type setCode(string $value)
 * @method string getLabel()
 * @method \Magento\Eav\Model\Form\Type setLabel(string $value)
 * @method int getIsSystem()
 * @method \Magento\Eav\Model\Form\Type setIsSystem(int $value)
 * @method string getTheme()
 * @method \Magento\Eav\Model\Form\Type setTheme(string $value)
 * @method int getStoreId()
 * @method \Magento\Eav\Model\Form\Type setStoreId(int $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Type extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'eav_form_type';

    /**
     * @var \Magento\Eav\Model\Form\FieldsetFactory
     */
    protected $_fieldsetFactory;

    /**
     * @var \Magento\Eav\Model\Form\ElementFactory
     */
    protected $_elementFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Eav\Model\Form\FieldsetFactory $fieldsetFactory
     * @param \Magento\Eav\Model\Form\ElementFactory $elementFactory
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Eav\Model\Form\FieldsetFactory $fieldsetFactory,
        \Magento\Eav\Model\Form\ElementFactory $elementFactory,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_fieldsetFactory = $fieldsetFactory;
        $this->_elementFactory = $elementFactory;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Eav\Model\Resource\Form\Type');
    }

    /**
     * Retrieve resource instance wrapper
     *
     * @return \Magento\Eav\Model\Resource\Form\Type
     */
    protected function _getResource()
    {
        return parent::_getResource();
    }

    /**
     * Retrieve resource collection instance wrapper
     *
     * @return \Magento\Eav\Model\Resource\Form\Type\Collection
     */
    public function getCollection()
    {
        return parent::getCollection();
    }

    /**
     * Retrieve assigned Eav Entity types
     *
     * @return array
     */
    public function getEntityTypes()
    {
        if (!$this->hasData('entity_types')) {
            $this->setData('entity_types', $this->_getResource()->getEntityTypes($this));
        }
        return $this->_getData('entity_types');
    }

    /**
     * Set assigned Eav Entity types
     *
     * @param array $entityTypes
     * @return $this
     */
    public function setEntityTypes(array $entityTypes)
    {
        $this->setData('entity_types', $entityTypes);
        return $this;
    }

    /**
     * Assign Entity Type to Form Type
     *
     * @param int $entityTypeId
     * @return $this
     */
    public function addEntityType($entityTypeId)
    {
        $entityTypes = $this->getEntityTypes();
        if (!empty($entityTypeId) && !in_array($entityTypeId, $entityTypes)) {
            $entityTypes[] = $entityTypeId;
            $this->setEntityTypes($entityTypes);
        }
        return $this;
    }

    /**
     * Copy Form Type properties from skeleton form type
     *
     * @param \Magento\Eav\Model\Form\Type $skeleton
     * @return $this
     */
    public function createFromSkeleton(\Magento\Eav\Model\Form\Type $skeleton)
    {
        $fieldsetCollection = $this->_fieldsetFactory->create()->getCollection()->addTypeFilter(
            $skeleton
        )->setSortOrder();
        $elementCollection = $this->_elementFactory->create()->getCollection()->addTypeFilter(
            $skeleton
        )->setSortOrder();

        // copy fieldsets
        $fieldsetMap = array();
        foreach ($fieldsetCollection as $skeletonFieldset) {
            $this->_fieldsetFactory->create()->setTypeId(
                $this->getId()
            )->setCode(
                $skeletonFieldset->getCode()
            )->setLabels(
                $skeletonFieldset->getLabels()
            )->setSortOrder(
                $skeletonFieldset->getSortOrder()
            )->save();
            $fieldsetMap[$skeletonFieldset->getId()] = $this->_fieldsetFactory->create()->getId();
        }

        // copy elements
        foreach ($elementCollection as $skeletonElement) {
            $fieldsetId = null;
            if ($skeletonElement->getFieldsetId()) {
                $fieldsetId = $fieldsetMap[$skeletonElement->getFieldsetId()];
            }
            $this->_elementFactory->create()->setTypeId(
                $this->getId()
            )->setFieldsetId(
                $fieldsetId
            )->setAttributeId(
                $skeletonElement->getAttributeId()
            )->setSortOrder(
                $skeletonElement->getSortOrder()
            );
        }

        return $this;
    }
}
