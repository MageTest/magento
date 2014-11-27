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
namespace Magento\RecurringPayment\Block\Adminhtml\Product\Edit\Tab\Price;

/**
 * Recurring payment attribute edit renderer
 */
class Recurring extends \Magento\Catalog\Block\Adminhtml\Form\Renderer\Fieldset\Element
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = array()
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Element output getter
     *
     * @return string
     */
    public function getElementHtml()
    {
        $product = $this->_coreRegistry->registry('current_product');

        /** @var $formBlock \Magento\RecurringPayment\Block\Adminhtml\Payment\Edit\Form */
        $formBlock = $this->getLayout()->createBlock(
            'Magento\RecurringPayment\Block\Adminhtml\Payment\Edit\Form',
            'adminhtml_recurring_payment_edit_form'
        );
        $formBlock->setParentElement($this->_element);
        $formBlock->setProductEntity($product);
        $output = $formBlock->toHtml();

        // make the payment element dependent on is_recurring
        /** @var $dependencies \Magento\Backend\Block\Widget\Form\Element\Dependence */
        $dependencies = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Form\Element\Dependence',
            'adminhtml_recurring_payment_edit_form_dependence'
        );
        $dependencies->addFieldMap('is_recurring', 'product[is_recurring]');
        $dependencies->addFieldMap($this->_element->getHtmlId(), $this->_element->getName());
        $dependencies->addFieldDependence($this->_element->getName(), 'product[is_recurring]', '1');
        $dependencies->addConfigOptions(array('levels_up' => 2));

        $output .= $dependencies->toHtml();

        return $output;
    }
}
