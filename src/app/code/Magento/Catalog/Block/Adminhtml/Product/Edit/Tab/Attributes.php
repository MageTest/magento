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
 * Product attributes tab
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Tab;

class Attributes extends \Magento\Catalog\Block\Adminhtml\Form
{
    /**
     * Prepare attributes form
     *
     * @return void
     */
    protected function _prepareForm()
    {
        /** @var $group \Magento\Eav\Model\Entity\Attribute\Group */
        $group = $this->getGroup();
        if ($group) {
            /** @var \Magento\Framework\Data\Form $form */
            $form = $this->_formFactory->create();
            $product = $this->_coreRegistry->registry('product');
            $isWrapped = $this->_coreRegistry->registry('use_wrapper');
            if (!isset($isWrapped)) {
                $isWrapped = true;
            }
            $isCollapsable = $isWrapped && $group->getAttributeGroupCode() == 'product-details';
            $legend = $isWrapped ? __($group->getAttributeGroupName()) : null;
            // Initialize product object as form property to use it during elements generation
            $form->setDataObject($product);

            $fieldset = $form->addFieldset(
                'group-fields-' . $group->getAttributeGroupCode(),
                array('class' => 'user-defined', 'legend' => $legend, 'collapsable' => $isCollapsable)
            );

            $attributes = $this->getGroupAttributes();

            $this->_setFieldset($attributes, $fieldset, array('gallery'));

            $tierPrice = $form->getElement('tier_price');
            if ($tierPrice) {
                $tierPrice->setRenderer(
                    $this->getLayout()->createBlock('Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Price\Tier')
                );
            }

            $groupPrice = $form->getElement('group_price');
            if ($groupPrice) {
                $groupPrice->setRenderer(
                    $this->getLayout()->createBlock('Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Price\Group')
                );
            }

            // Add new attribute controls if it is not an image tab
            if (!$form->getElement(
                'media_gallery'
            ) && $this->_authorization->isAllowed(
                'Magento_Catalog::attributes_attributes'
            ) && $isWrapped
            ) {
                $attributeCreate = $this->getLayout()->createBlock(
                    'Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes\Create'
                );

                $attributeCreate->getConfig()->setAttributeGroupCode(
                    $group->getAttributeGroupCode()
                )->setTabId(
                    'group_' . $group->getId()
                )->setGroupId(
                    $group->getId()
                )->setStoreId(
                    $form->getDataObject()->getStoreId()
                )->setAttributeSetId(
                    $form->getDataObject()->getAttributeSetId()
                )->setTypeId(
                    $form->getDataObject()->getTypeId()
                )->setProductId(
                    $form->getDataObject()->getId()
                );

                $attributeSearch = $this->getLayout()->createBlock(
                    'Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes\Search'
                )->setGroupId(
                    $group->getId()
                )->setGroupCode(
                    $group->getAttributeGroupCode()
                );

                $attributeSearch->setAttributeCreate($attributeCreate->toHtml());

                $fieldset->setHeaderBar($attributeSearch->toHtml());
            }

            $values = $product->getData();

            // Set default attribute values for new product or on attribute set change
            if (!$product->getId() || $product->dataHasChangedFor('attribute_set_id')) {
                foreach ($attributes as $attribute) {
                    if (!isset($values[$attribute->getAttributeCode()])) {
                        $values[$attribute->getAttributeCode()] = $attribute->getDefaultValue();
                    }
                }
            }

            if ($product->hasLockedAttributes()) {
                foreach ($product->getLockedAttributes() as $attribute) {
                    $element = $form->getElement($attribute);
                    if ($element) {
                        $element->setReadonly(true, true);
                        $element->lock();
                    }
                }
            }
            $form->addValues($values);
            $form->setFieldNameSuffix('product');

            $this->_eventManager->dispatch(
                'adminhtml_catalog_product_edit_prepare_form',
                array('form' => $form, 'layout' => $this->getLayout())
            );

            $this->setForm($form);
        }
    }

    /**
     * Retrieve additional element types
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        $result = array(
            'price' => 'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Price',
            'weight' => 'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Weight',
            'gallery' => 'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery',
            'image' => 'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Image',
            'boolean' => 'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Boolean',
            'textarea' => 'Magento\Catalog\Block\Adminhtml\Helper\Form\Wysiwyg'
        );

        $response = new \Magento\Framework\Object();
        $response->setTypes(array());
        $this->_eventManager->dispatch('adminhtml_catalog_product_edit_element_types', array('response' => $response));

        foreach ($response->getTypes() as $typeName => $typeClass) {
            $result[$typeName] = $typeClass;
        }

        return $result;
    }
}
