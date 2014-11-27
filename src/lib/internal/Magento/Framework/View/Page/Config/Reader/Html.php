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
namespace Magento\Framework\View\Page\Config\Reader;

use Magento\Framework\View\Layout;
use Magento\Framework\View\Page\Config as PageConfig;

class Html implements Layout\ReaderInterface
{
    /**#@+
     * Supported types
     */
    const TYPE_HTML = 'html';
    /**#@-*/

    /**#@+
     * Supported html elements
     */
    const HTML_ATTRIBUTE = 'attribute';
    /**#@-*/

    /**
     * @return string[]
     */
    public function getSupportedNodes()
    {
        return [self::TYPE_HTML];
    }

    /**
     * Process Html structure
     *
     * @param Layout\Reader\Context $readerContext
     * @param Layout\Element $htmlElement
     * @param Layout\Element $parentElement
     * @return $this
     */
    public function process(
        Layout\Reader\Context $readerContext,
        Layout\Element $htmlElement,
        Layout\Element $parentElement
    ) {
        /** @var \Magento\Framework\View\Layout\Element $element */
        foreach ($htmlElement as $element) {
            switch ($element->getName()) {
                case self::HTML_ATTRIBUTE:
                    $$readerContext->getPageConfigStructure()->setElementAttribute(
                        PageConfig::ELEMENT_TYPE_HTML,
                        $element->getAttribute('name'),
                        $element->getAttribute('value')
                    );
                    break;

                default:
                    break;
            }
        }
        return $this;
    }
}
