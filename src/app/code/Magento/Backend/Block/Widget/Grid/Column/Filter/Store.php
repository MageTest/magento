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
 * Store grid column filter
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Filter;

class Store extends \Magento\Backend\Block\Widget\Grid\Column\Filter\AbstractFilter
{
    const ALL_STORE_VIEWS = '0';
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\DB\Helper $resourceHelper
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\DB\Helper $resourceHelper,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = array()
    ) {
        $this->_systemStore = $systemStore;
        parent::__construct($context, $resourceHelper, $data);
    }

    /**
     * Render HTML of the element
     *
     * @return string
     */
    public function getHtml()
    {
        $websiteCollection = $this->_systemStore->getWebsiteCollection();
        $groupCollection = $this->_systemStore->getGroupCollection();
        $storeCollection = $this->_systemStore->getStoreCollection();

        $allShow = $this->getColumn()->getStoreAll();

        $html = '<select name="' . $this->escapeHtml(
            $this->_getHtmlName()
        ) . '" ' . $this->getColumn()->getValidateClass() . $this->getUiId(
            'filter',
            $this->_getHtmlName()
        ) . '>';
        $value = $this->getColumn()->getValue();
        if ($allShow) {
            $html .= '<option value="' . self::ALL_STORE_VIEWS . '"'
                 . ($value == self::ALL_STORE_VIEWS ? ' selected="selected"' : '') . '>'
                 . __('All Store Views') . '</option>';
        } else {
            $html .= '<option value=""' . (!$value ? ' selected="selected"' : '') . '></option>';
        }
        foreach ($websiteCollection as $website) {
            $websiteShow = false;
            foreach ($groupCollection as $group) {
                if ($group->getWebsiteId() != $website->getId()) {
                    continue;
                }
                $groupShow = false;
                foreach ($storeCollection as $store) {
                    if ($store->getGroupId() != $group->getId()) {
                        continue;
                    }
                    if (!$websiteShow) {
                        $websiteShow = true;
                        $html .= '<optgroup label="' . $this->escapeHtml($website->getName()) . '"></optgroup>';
                    }
                    if (!$groupShow) {
                        $groupShow = true;
                        $html .= '<optgroup label="&nbsp;&nbsp;&nbsp;&nbsp;' . $this->escapeHtml(
                            $group->getName()
                        ) . '">';
                    }
                    $value = $this->getValue();
                    $selected = $value == $store->getId() ? ' selected="selected"' : '';
                    $html .= '<option value="' .
                        $store->getId() .
                        '"' .
                        $selected .
                        '>&nbsp;&nbsp;&nbsp;&nbsp;' .
                        $this->escapeHtml(
                            $store->getName()
                        ) . '</option>';
                }
                if ($groupShow) {
                    $html .= '</optgroup>';
                }
            }
        }
        if ($this->getColumn()->getDisplayDeleted()) {
            $selected = $this->getValue() == '_deleted_' ? ' selected' : '';
            $html .= '<option value="_deleted_"' . $selected . '>' . __('[ deleted ]') . '</option>';
        }
        $html .= '</select>';
        return $html;
    }

    /**
     * Form condition from element's value
     *
     * @return array|null
     */
    public function getCondition()
    {
        $value = $this->getValue();
        if (is_null($value) || $value == self::ALL_STORE_VIEWS) {
            return null;
        }
        if ($value == '_deleted_') {
            return array('null' => true);
        } else {
            return array('eq' => $value);
        }
    }
}
