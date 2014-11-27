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
namespace Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab\Main;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Widget Instance page groups (predefined layouts group) to display on
 *
 * @method \Magento\Widget\Model\Widget\Instance getWidgetInstance()
 */
class Layout extends \Magento\Backend\Block\Template implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * @var AbstractElement|null
     */
    protected $_element = null;

    /**
     * @var string
     */
    protected $_template = 'instance/edit/layout.phtml';

    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_productType;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Catalog\Model\Product\Type $productType
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\Product\Type $productType,
        array $data = array()
    ) {
        $this->_productType = $productType;
        parent::__construct($context, $data);
    }

    /**
     * Render given element (return html of element)
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    /**
     * Setter
     *
     * @param AbstractElement $element
     * @return $this
     */
    public function setElement(AbstractElement $element)
    {
        $this->_element = $element;
        return $this;
    }

    /**
     * Getter
     *
     * @return AbstractElement
     */
    public function getElement()
    {
        return $this->_element;
    }

    /**
     * Generate url to get categories chooser by ajax query
     *
     * @return string
     */
    public function getCategoriesChooserUrl()
    {
        return $this->getUrl('adminhtml/*/categories', array('_current' => true));
    }

    /**
     * Generate url to get products chooser by ajax query
     *
     * @return string
     */
    public function getProductsChooserUrl()
    {
        return $this->getUrl('adminhtml/*/products', array('_current' => true));
    }

    /**
     * Generate url to get reference block chooser by ajax query
     *
     * @return string
     */
    public function getBlockChooserUrl()
    {
        return $this->getUrl('adminhtml/*/blocks', array('_current' => true));
    }

    /**
     * Generate url to get template chooser by ajax query
     *
     * @return string
     */
    public function getTemplateChooserUrl()
    {
        return $this->getUrl('adminhtml/*/template', array('_current' => true));
    }

    /**
     * Create and return html of select box Display On
     *
     * @return string
     */
    public function getDisplayOnSelectHtml()
    {
        $selectBlock = $this->getLayout()->createBlock(
            'Magento\Framework\View\Element\Html\Select'
        )->setName(
            'widget_instance[{{id}}][page_group]'
        )->setId(
            'widget_instance[{{id}}][page_group]'
        )->setClass(
            'required-entry page_group_select select'
        )->setExtraParams(
            "onchange=\"WidgetInstance.displayPageGroup(this.value+\'_{{id}}\')\""
        )->setOptions(
            $this->_getDisplayOnOptions()
        );
        return $selectBlock->toHtml();
    }

    /**
     * Retrieve Display On options array.
     * - Categories (anchor and not anchor)
     * - Products (product types depend on configuration)
     * - Generic (predefined) pages (all pages and single layout update)
     *
     * @return array
     */
    protected function _getDisplayOnOptions()
    {
        $options = array();
        $options[] = array('value' => '', 'label' => $this->escapeJsQuote(__('-- Please Select --')));
        $options[] = array(
            'label' => __('Categories'),
            'value' => array(
                array('value' => 'anchor_categories', 'label' => $this->escapeJsQuote(__('Anchor Categories'))),
                array('value' => 'notanchor_categories', 'label' => $this->escapeJsQuote(__('Non-Anchor Categories')))
            )
        );
        foreach ($this->_productType->getTypes() as $typeId => $type) {
            $productsOptions[] = array(
                'value' => $typeId . '_products',
                'label' => $this->escapeJsQuote($type['label'])
            );
        }
        array_unshift(
            $productsOptions,
            array('value' => 'all_products', 'label' => $this->escapeJsQuote(__('All Product Types')))
        );
        $options[] = array('label' => $this->escapeJsQuote(__('Products')), 'value' => $productsOptions);
        $options[] = array(
            'label' => $this->escapeJsQuote(__('Generic Pages')),
            'value' => array(
                array('value' => 'all_pages', 'label' => $this->escapeJsQuote(__('All Pages'))),
                array('value' => 'pages', 'label' => $this->escapeJsQuote(__('Specified Page'))),
                array('value' => 'page_layouts', 'label' => $this->escapeJsQuote(__('Page Layouts')))
            )
        );
        return $options;
    }

    /**
     * Generate array of parameters for every container type to create html template
     *
     * @return array
     */
    public function getDisplayOnContainers()
    {
        $container = array();
        $container['anchor'] = array(
            'label' => 'Categories',
            'code' => 'categories',
            'name' => 'anchor_categories',
            'layout_handle' => \Magento\Widget\Model\Widget\Instance::ANCHOR_CATEGORY_LAYOUT_HANDLE,
            'is_anchor_only' => 1,
            'product_type_id' => ''
        );
        $container['notanchor'] = array(
            'label' => 'Categories',
            'code' => 'categories',
            'name' => 'notanchor_categories',
            'layout_handle' => \Magento\Widget\Model\Widget\Instance::NOTANCHOR_CATEGORY_LAYOUT_HANDLE,
            'is_anchor_only' => 0,
            'product_type_id' => ''
        );
        $container['all_products'] = array(
            'label' => 'Products',
            'code' => 'products',
            'name' => 'all_products',
            'layout_handle' => \Magento\Widget\Model\Widget\Instance::PRODUCT_LAYOUT_HANDLE,
            'is_anchor_only' => '',
            'product_type_id' => ''
        );
        foreach ($this->_productType->getTypes() as $typeId => $type) {
            $container[$typeId] = array(
                'label' => 'Products',
                'code' => 'products',
                'name' => $typeId . '_products',
                'layout_handle' => str_replace(
                    '{{TYPE}}',
                    $typeId,
                    \Magento\Widget\Model\Widget\Instance::PRODUCT_TYPE_LAYOUT_HANDLE
                ),
                'is_anchor_only' => '',
                'product_type_id' => $typeId
            );
        }
        return $container;
    }

    /**
     * Retrieve layout select chooser html
     *
     * @return string
     */
    public function getLayoutsChooser()
    {
        $chooserBlock = $this->getLayout()->createBlock(
            'Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Chooser\Layout'
        )->setName(
            'widget_instance[{{id}}][pages][layout_handle]'
        )->setId(
            'layout_handle'
        )->setClass(
            'required-entry select'
        )->setExtraParams(
            "onchange=\"WidgetInstance.loadSelectBoxByType(\'block_reference\', " .
            "this.up(\'div.pages\'), this.value)\""
        )->setArea(
            $this->getWidgetInstance()->getArea()
        )->setTheme(
            $this->getWidgetInstance()->getThemeId()
        );
        return $chooserBlock->toHtml();
    }

    /**
     * Retrieve layout select chooser html
     *
     * @return string
     */
    public function getPageLayoutsPageChooser()
    {
        $chooserBlock = $this->getLayout()->createBlock(
            'Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Chooser\DesignAbstraction'
        )->setName(
            'widget_instance[{{id}}][page_layouts][layout_handle]'
        )->setId(
            'layout_handle'
        )->setClass(
            'required-entry select'
        )->setExtraParams(
            "onchange=\"WidgetInstance.loadSelectBoxByType(\'block_reference\', " .
            "this.up(\'div.pages\'), this.value)\""
        )->setArea(
            $this->getWidgetInstance()->getArea()
        )->setTheme(
            $this->getWidgetInstance()->getThemeId()
        );
        return $chooserBlock->toHtml();
    }

    /**
     * Retrieve add layout button html
     *
     * @return string
     */
    public function getAddLayoutButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            array(
                'label' => __('Add Layout Update'),
                'onclick' => 'WidgetInstance.addPageGroup({})',
                'class' => 'action-add'
            )
        );
        return $button->toHtml();
    }

    /**
     * Retrieve remove layout button html
     *
     * @return string
     */
    public function getRemoveLayoutButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            array(
                'label' => $this->escapeJsQuote(__('Remove Layout Update')),
                'onclick' => 'WidgetInstance.removePageGroup(this)',
                'class' => 'action-delete'
            )
        );
        return $button->toHtml();
    }

    /**
     * Prepare and retrieve page groups data of widget instance
     *
     * @return array
     */
    public function getPageGroups()
    {
        $widgetInstance = $this->getWidgetInstance();
        $pageGroups = array();
        if ($widgetInstance->getPageGroups()) {
            foreach ($widgetInstance->getPageGroups() as $pageGroup) {
                $pageGroups[] = array(
                    'page_id' => $pageGroup['page_id'],
                    'group' => $pageGroup['page_group'],
                    'block' => $pageGroup['block_reference'],
                    'for_value' => $pageGroup['page_for'],
                    'layout_handle' => $pageGroup['layout_handle'],
                    $pageGroup['page_group'] . '_entities' => $pageGroup['entities'],
                    'template' => $pageGroup['page_template']
                );
            }
        }
        return $pageGroups;
    }
}
