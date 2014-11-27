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
namespace Magento\Sales\Block\Adminhtml\Report\Filter\Form;

/**
 * Sales Adminhtml report filter form for coupons report
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Coupon extends \Magento\Sales\Block\Adminhtml\Report\Filter\Form
{
    /**
     * Flag that keep info should we render specific dependent element or not
     *
     * @var bool
     */
    protected $_renderDependentElement = false;

    /**
     * Rule factory
     *
     * @var \Magento\SalesRule\Model\Resource\Report\RuleFactory
     */
    protected $_reportRule;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Sales\Model\Order\ConfigFactory $orderConfig
     * @param \Magento\SalesRule\Model\Resource\Report\RuleFactory $reportRule
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Sales\Model\Order\ConfigFactory $orderConfig,
        \Magento\SalesRule\Model\Resource\Report\RuleFactory $reportRule,
        array $data = array()
    ) {
        $this->_reportRule = $reportRule;
        parent::__construct($context, $registry, $formFactory, $orderConfig, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();

        /** @var \Magento\Framework\Data\Form\Element\Fieldset $fieldset */
        $fieldset = $this->getForm()->getElement('base_fieldset');

        if (is_object($fieldset) && $fieldset instanceof \Magento\Framework\Data\Form\Element\Fieldset) {

            $fieldset->addField(
                'price_rule_type',
                'select',
                array(
                    'name' => 'price_rule_type',
                    'options' => array(__('Any'), __('Specified')),
                    'label' => __('Shopping Cart Price Rule')
                )
            );

            $rulesList = $this->_reportRule->create()->getUniqRulesNamesList();

            $rulesListOptions = array();

            foreach ($rulesList as $key => $ruleName) {
                $rulesListOptions[] = array('label' => $ruleName, 'value' => $key, 'title' => $ruleName);
            }

            $fieldset->addField(
                'rules_list',
                'multiselect',
                array('name' => 'rules_list', 'values' => $rulesListOptions, 'display' => 'none'),
                'price_rule_type'
            );

            $this->_renderDependentElement = true;
        }

        return $this;
    }

    /**
     * Processing block html after rendering
     *
     * @param string $html
     * @return string
     */
    protected function _afterToHtml($html)
    {
        if ($this->_renderDependentElement) {
            $form = $this->getForm();
            $htmlIdPrefix = $form->getHtmlIdPrefix();

            /**
             * Form template has possibility to render child block 'form_after', but we can't use it because parent
             * form creates appropriate child block and uses this alias. In this case we can't use the same alias
             * without core logic changes, that's why the code below was moved inside method '_afterToHtml'.
             */
            /** @var $formAfterBlock \Magento\Backend\Block\Widget\Form\Element\Dependence */
            $formAfterBlock = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Form\Element\Dependence',
                'adminhtml.block.widget.form.element.dependence'
            );
            $formAfterBlock->addFieldMap(
                $htmlIdPrefix . 'price_rule_type',
                'price_rule_type'
            )->addFieldMap(
                $htmlIdPrefix . 'rules_list',
                'rules_list'
            )->addFieldDependence(
                'rules_list',
                'price_rule_type',
                '1'
            );
            $html = $html . $formAfterBlock->toHtml();
        }

        return $html;
    }
}
