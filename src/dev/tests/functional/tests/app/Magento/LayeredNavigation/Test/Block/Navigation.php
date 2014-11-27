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
namespace Magento\LayeredNavigation\Test\Block;

use Mtf\Block\Block;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;

/**
 * Catalog layered navigation view block
 */
class Navigation extends Block
{
    /**
     * 'Clear All' link.
     *
     * @var string
     */
    protected $clearAll = '.action.clear';

    /**
     * Price range.
     *
     * @var string
     */
    protected $priceRange = "[href$='?price=%s']";

    /**
     * Attribute option.
     *
     * @var string
     */
    protected $attributeOption = "//a[contains(text(), '%s')]";

    /**
     * Attribute option title selector.
     *
     * @var string
     */
    protected $optionTitle = '.filter-options-title';

    /**
     * Attribute option content selector.
     *
     * @var string
     */
    protected $optionContent = '.filter-options-content';

    /**
     * Click on 'Clear All' link.
     *
     * @return void
     */
    public function clearAll()
    {
        $this->reinitRootElement();
        $this->_rootElement->find($this->clearAll, locator::SELECTOR_CSS)->click();
    }

    /**
     * Select product price range.
     *
     * @param string $range
     * @return void
     */
    public function selectPriceRange($range)
    {
        $this->reinitRootElement();
        $this->_rootElement->find(sprintf($this->priceRange, $range))->click();
    }

    /**
     * Select attribute option.
     *
     * @param string $optionName
     * @return void
     */
    public function selectAttributeOption($optionName)
    {
        $this->reinitRootElement();
        $this->_rootElement->find(sprintf($this->attributeOption, $optionName), Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Get array of available filters.
     *
     * @return array
     */
    public function getFilters()
    {
        $options = $this->_rootElement->find($this->optionTitle)->getElements();
        $data = [];
        foreach ($options as $option) {
            $data[] = $option->getText();
        }
        return $data;
    }
}
