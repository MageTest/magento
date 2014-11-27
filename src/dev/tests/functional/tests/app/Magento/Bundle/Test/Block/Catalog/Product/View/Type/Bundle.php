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

namespace Magento\Bundle\Test\Block\Catalog\Product\View\Type;

use Mtf\Block\Block;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;
use Magento\Bundle\Test\Block\Catalog\Product\View\Type\Option;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Mtf\Fixture\FixtureInterface;
use Mtf\Fixture\InjectableFixture;
use Magento\Bundle\Test\Fixture\Bundle as BundleDataFixture;
use Magento\Bundle\Test\Fixture\BundleProduct;

/**
 * Class Bundle
 * Catalog bundle product info block
 */
class Bundle extends Block
{
    /**
     * Selector for single option block
     *
     * @var string
     */
    protected $optionElement = './div[contains(@class,"option")][%d]';

    /**
     * Selector for title of option
     *
     * @var string
     */
    protected $title = './label/span';

    /**
     * Selector for required option
     *
     * @var string
     */
    protected $required = './self::*[contains(@class,"required")]';

    /**
     * Selector for select element of option
     *
     * @var string
     */
    protected $selectOption = './/div[@class="control"]/select';

    /**
     * Selector for label of option value element
     *
     * @var string
     */
    protected $optionLabel = './/div[@class="control"]//label[.//*[@class="product-name"]]';

    /**
     * Selector for option of select element
     *
     * @var string
     */
    protected $option = './/option[%d]';

    /**
     * Selector bundle option block for fill
     *
     * @var string
     */
    protected $bundleOptionBlock = './/div[label[span[contains(text(), "%s")]]]';

    /**
     * Fill bundle option on frontend add click "Add to cart" button
     *
     * @param BundleProduct $product
     * @param CatalogProductView $catalogProductView
     * @return void
     */
    public function addToCart(BundleProduct $product, CatalogProductView $catalogProductView)
    {
        $catalogProductView->getViewBlock()->fillOptions($product);
        $catalogProductView->getViewBlock()->clickAddToCart();
    }

    /**
     * Get product options
     *
     * @param FixtureInterface $product
     * @return array
     * @throws \Exception
     */
    public function getOptions(FixtureInterface $product)
    {
        if ($product instanceof InjectableFixture) {
            /** @var BundleProduct  $product */
            $bundleSelections = $product->getBundleSelections();
            $bundleOptions = isset($bundleSelections['bundle_options']) ? $bundleSelections['bundle_options'] : [];
        } else {
            // TODO: Removed after refactoring(removed) old product fixture.
            /** @var BundleDataFixture $product */
            $bundleOptions = $product->getBundleOptions();
        }

        $listFormOptions = $this->getListOptions();
        $formOptions = [];

        foreach ($bundleOptions as $option) {
            $title = $option['title'];
            if (!isset($listFormOptions[$title])) {
                throw new \Exception("Can't find option: \"{$title}\"");
            }

            /** @var Element $optionElement */
            $optionElement = $listFormOptions[$title];
            $getTypeData = 'get' . $this->optionNameConvert($option['type']) . 'Data';

            $optionData = $this->$getTypeData($optionElement);
            $optionData['title'] = $title;
            $optionData['type'] = $option['type'];
            $optionData['is_require'] = $optionElement->find($this->required, Locator::SELECTOR_XPATH)->isVisible()
                ? 'Yes'
                : 'No';

            $formOptions[] = $optionData;
        }

        return $formOptions;
    }

    /**
     * Get list options
     *
     * @return array
     */
    protected function getListOptions()
    {
        $options = [];

        $count = 1;
        $optionElement = $this->_rootElement->find(sprintf($this->optionElement, $count), Locator::SELECTOR_XPATH);
        while ($optionElement->isVisible()) {
            $title = $optionElement->find($this->title, Locator::SELECTOR_XPATH)->getText();
            $options[$title] = $optionElement;

            ++$count;
            $optionElement = $this->_rootElement->find(sprintf($this->optionElement, $count), Locator::SELECTOR_XPATH);
        }
        return $options;
    }

    /**
     * Get data of "Drop-down" option
     *
     * @param Element $option
     * @return array
     */
    protected function getDropdownData(Element $option)
    {
        $select = $option->find($this->selectOption, Locator::SELECTOR_XPATH, 'select');
        // Skip "Choose option ..."(option #1)
        return $this->getSelectOptionsData($select, 2);
    }

    /**
     * Get data of "Multiple select" option
     *
     * @param Element $option
     * @return array
     */
    protected function getMultipleselectData(Element $option)
    {
        $multiselect = $option->find($this->selectOption, Locator::SELECTOR_XPATH, 'multiselect');
        $data = $this->getSelectOptionsData($multiselect, 1);

        foreach ($data['options'] as $key => $option) {
            $option['title'] = trim(preg_replace('/^[\d]+ x/', '', $option['title']));
            $data['options'][$key] = $option;
        }

        return $data;
    }

    /**
     * Get data of "Radio buttons" option
     *
     * @param Element $option
     * @return array
     */
    protected function getRadiobuttonsData(Element $option)
    {
        $listOptions = [];
        $optionLabels = $option->find($this->optionLabel, Locator::SELECTOR_XPATH)->getElements();

        foreach ($optionLabels as $optionLabel) {
            if ($optionLabel->isVisible()) {
                $listOptions[] = $this->parseOptionText($optionLabel->getText());
            }
        }

        return ['options' => $listOptions];
    }

    /**
     * Get data of "Checkbox" option
     *
     * @param Element $option
     * @return array
     */
    protected function getCheckboxData(Element $option)
    {
        $data =  $this->getRadiobuttonsData($option);

        foreach ($data['options'] as $key => $option) {
            $option['title'] = trim(preg_replace('/^[\d]+ x/', '', $option['title']));
            $data['options'][$key] = $option;
        }

        return $data;
    }

    /**
     * Get data from option of select and multiselect
     *
     * @param Element $element
     * @param int $firstOption
     * @return array
     */
    protected function getSelectOptionsData(Element $element, $firstOption = 1)
    {
        $listOptions = [];

        $count = $firstOption;
        $selectOption = $element->find(sprintf($this->option, $count), Locator::SELECTOR_XPATH);
        while ($selectOption->isVisible()) {
            $listOptions[] = $this->parseOptionText($selectOption->getText());
            ++$count;
            $selectOption = $element->find(sprintf($this->option, $count), Locator::SELECTOR_XPATH);
        }

        return ['options' => $listOptions];
    }

    /**
     * Parse option text to title and price
     *
     * @param string $optionText
     * @return array
     */
    protected function parseOptionText($optionText)
    {
        preg_match('`^(.*?)\+ ?\$(\d.*?)$`', $optionText, $match);
        $optionPrice = isset($match[2]) ? str_replace(',', '', $match[2]) : 0;
        $optionTitle = isset($match[1]) ? trim($match[1]) : $optionText;

        return [
            'title' => $optionTitle,
            'price' => $optionPrice
        ];
    }

    /**
     * Fill bundle options
     *
     * @param array $bundleOptions
     * @return void
     */
    public function fillBundleOptions($bundleOptions)
    {
        foreach ($bundleOptions as $option) {
            $selector = sprintf($this->bundleOptionBlock, $option['title']);
            /** @var Option $optionBlock */
            $optionBlock = $this->blockFactory->create(
                'Magento\Bundle\Test\Block\Catalog\Product\View\Type\Option\\'
                . $this->optionNameConvert($option['type']),
                ['element' => $this->_rootElement->find($selector, Locator::SELECTOR_XPATH)]
            );
            $optionBlock->fillOption($option['value']);
        }
    }

    /**
     * Convert option name
     *
     * @param string $optionType
     * @return string
     */
    protected function optionNameConvert($optionType)
    {
        $trimmedOptionType = preg_replace('/[^a-zA-Z]/', '', $optionType);
        return ucfirst(strtolower($trimmedOptionType));
    }
}
