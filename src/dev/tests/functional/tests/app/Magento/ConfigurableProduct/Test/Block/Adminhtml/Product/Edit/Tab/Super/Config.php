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

namespace Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Super;

use Magento\Backend\Test\Block\Widget\Tab;
use Mtf\Client\Element\Locator;
use Mtf\Client\Element;
use Magento\Catalog\Test\Fixture\CatalogCategory;
use Magento\Backend\Test\Block\Template;

/**
 * Class Config
 * Adminhtml catalog super product configurable tab
 */
class Config extends Tab
{
    /**
     * Selector for trigger show/hide "Variations" tab
     *
     * @var string
     */
    protected $variationsTabTrigger = '[data-panel="product-variations"] .title span';

    /**
     * Selector for content "Variations" tab
     *
     * @var string
     */
    protected $variationsTabContent = '#super_config-content';

    /**
     * Selector for button "Generate Variations"
     *
     * @var string
     */
    protected $generateVariations = '[data-ui-id="product-variations-generator-generate"]';

    /**
     * Selector for variations matrix
     *
     * @var string
     */
    protected $variationsMatrix = '[data-role="product-variations-matrix"]';

    /**
     * Selector for template block.
     *
     * @var string
     */
    protected $template = './ancestor::body';

    /**
     * Selector for variations tab wrapper
     *
     * @var string
     */
    protected $variationsTabWrapper = '#super_config-wrapper';

    /**
     * Attribute element selector
     *
     * @var string
     */
    protected $attributeElement = '.entry-edit.have-price';

    /**
     * Delete variation button selector
     *
     * @var string
     */
    protected $deleteVariationButton = '.action-delete';

    /**
     * Variations content selector
     *
     * @var string
     */
    protected $variationsContent = '#product_info_tabs_super_config_content';

    /**
     * Fill variations fieldset
     *
     * @param array $fields
     * @param Element|null $element
     * @return $this
     */
    public function fillFormTab(array $fields, Element $element = null)
    {
        $attributes = isset($fields['configurable_attributes_data']['value'])
            ? $fields['configurable_attributes_data']['value']
            : [];

        $this->showContent();

        if (!empty($attributes['attributes_data'])) {
            $this->getAttributeBlock()->fillAttributes($attributes['attributes_data']);
        }
        if (!empty($attributes['matrix'])) {
            $this->generateVariations();
            $this->getVariationsBlock()->fillVariations($attributes['matrix']);
        }

        return $this;
    }

    /**
     * Show "Variations" tab content
     *
     * @return void
     */
    public function showContent()
    {
        $content = $this->_rootElement->find($this->variationsTabContent);
        if (!$content->isVisible()) {
            $this->_rootElement->find($this->variationsTabWrapper)->click();
            $this->_rootElement->find($this->variationsTabTrigger)->click();
            $this->waitForElementVisible($this->variationsTabContent);
        }
    }

    /**
     * Generate variations
     *
     * @return void
     */
    public function generateVariations()
    {
        $this->_rootElement->find($this->generateVariations)->click();
        $this->getTemplateBlock()->waitLoader();
    }

    /**
     * Get block of attributes
     *
     * @return \Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Super\Config\Attribute
     */
    public function getAttributeBlock()
    {
        return $this->blockFactory->create(
            'Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Super\Config\Attribute',
            ['element' => $this->_rootElement]
        );
    }

    /**
     * Get block of variations
     *
     * @return \Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Super\Config\Matrix
     */
    public function getVariationsBlock()
    {
        return $this->blockFactory->create(
            'Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Super\Config\Matrix',
            ['element' => $this->_rootElement->find($this->variationsMatrix)]
        );
    }

    /**
     * Get template block.
     *
     * @return Template
     */
    public function getTemplateBlock()
    {
        return $this->blockFactory->create(
            'Magento\Backend\Test\Block\Template',
            ['element' => $this->_rootElement->find($this->template, Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * Get data of tab
     *
     * @param array|null $fields
     * @param Element|null $element
     * @return array
     */
    public function getDataFormTab($fields = null, Element $element = null)
    {
        $data = [];

        $this->showContent();
        $data['attributes_data'] = $this->getAttributeBlock()->getAttributesData();
        $data['matrix'] = $this->getVariationsBlock()->getVariationsData();

        return ['configurable_attributes_data' => $data];
    }

    /**
     * Delete all attributes
     *
     * @return void
     */
    public function deleteAttributes()
    {
        $attributeElements = $this->_rootElement->find($this->attributeElement)->getElements();
        $this->_rootElement->find($this->variationsContent)->click();
        foreach ($attributeElements as $element) {
            $element->find($this->deleteVariationButton)->click();
        }
    }
}
