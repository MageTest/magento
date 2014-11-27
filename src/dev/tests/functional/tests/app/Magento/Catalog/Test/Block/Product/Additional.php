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

namespace Magento\Catalog\Test\Block\Product;

use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;
use Mtf\Block\Block;

/**
 * Product additional information block on the product page.
 */
class Additional extends Block
{
    /**
     * Custom attribute selector.
     *
     * @var string
     */
    protected $attributeSelector = '//tr/th';

    /**
     * Custom attribute value selector.
     *
     * @var string
     */
    protected $attributeValueSelector = '/following::td[1]';

    /**
     * Get product attributes.
     *
     * @return Element[]
     */
    protected function getProductAttributes()
    {
        $data = [];
        $elements = $this->_rootElement->find($this->attributeSelector, Locator::SELECTOR_XPATH)->getElements();
        foreach ($elements as $element) {
            $data[$element->getText()] = $this->_rootElement->find(
                $this->attributeSelector . $this->attributeValueSelector,
                Locator::SELECTOR_XPATH
            );
        }
        return $data;
    }

    /**
     * Check if attribute value contains tag.
     *
     * @param CatalogProductAttribute $attribute
     * @return bool
     */
    public function hasHtmlTagInAttributeValue(CatalogProductAttribute $attribute)
    {
        $data = $attribute->getData();
        $defaultValue = preg_grep('/^default_value/', array_keys($data));
        $selector = $this->resolveHtmlStructure($data[array_shift($defaultValue)]);
        $element = $this->getProductAttributes()[$attribute->getFrontendLabel()];

        return $this->checkHtmlTagStructure($element, $selector)->isVisible();
    }

    /**
     * Find <tag1><tag2><tagN> ... </tagN></tag2></tag1> tag structure in element.
     *
     * @param Element $element
     * @param string $selector
     * @return Element
     */
    protected function checkHtmlTagStructure(Element $element, $selector)
    {
        return $element->find($selector);
    }

    /**
     * Get list of available attributes.
     *
     * @return array
     */
    public function getAttributeLabels()
    {
        return array_keys($this->getProductAttributes());
    }

    /**
     * Resolve html structure from given string, which contains html tags.
     *
     * @param string $stringWithHtml
     * @return array
     */
    protected function resolveHtmlStructure($stringWithHtml)
    {
        $selector = '';
        $dom = new \DOMDocument();
        $dom->loadHTML($stringWithHtml);
        $xmlStructure = $xmlStructure = $dom->saveXML();
        $parser = xml_parser_create();
        xml_parse_into_struct($parser, $xmlStructure, $htmlData);
        $htmlData = array_slice($htmlData, 2, -2); //Remove <html> and <body> tags
        $middleElement = ceil(count($htmlData) / 2);
        for ($index = 0; $index < $middleElement; $index++) {
            $selector .= $htmlData[$index]['tag'] . " ";
        }
        return trim($selector);
    }
}
