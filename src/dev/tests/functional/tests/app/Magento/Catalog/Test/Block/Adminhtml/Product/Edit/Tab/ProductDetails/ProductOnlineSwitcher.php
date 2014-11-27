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

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\ProductDetails;

use Mtf\Client\Driver\Selenium\Element;
use Mtf\Client\Element\Locator;

/**
 * Class ProductOnlineSwitcher
 * Typified element class for product status element
 */
class ProductOnlineSwitcher extends Element
{
    /**
     * CSS locator button status of the product
     *
     * @var string
     */
    protected $onlineSwitcher = '#product-online-switcher%s + [for="product-online-switcher"]';

    /**
     * Selector for top page click.
     *
     * @var string
     */
    protected $topPage = './ancestor::body//*[@class="page-main-actions"]';

    /**
     * Set value
     *
     * @param string $value
     * @return void
     * @throws \Exception
     */
    public function setValue($value)
    {
        if (!$this->find(sprintf($this->onlineSwitcher, ''))->isVisible()) {
            throw new \Exception("Can't find product online switcher.");
        }
        if (($value === 'Product offline' && $this->find(sprintf($this->onlineSwitcher, ':checked'))->isVisible())
            || ($value === 'Product online'
                && $this->find(sprintf($this->onlineSwitcher, ':not(:checked)'))->isVisible()
            )
        ) {
            $this->find($this->topPage, Locator::SELECTOR_XPATH)->click();
            $this->find(sprintf($this->onlineSwitcher, ''))->click();
        }
    }

    /**
     * Get value
     *
     * @return string
     * @throws \Exception
     */
    public function getValue()
    {
        if (!$this->find(sprintf($this->onlineSwitcher, ''))->isVisible()) {
            throw new \Exception("Can't find product online switcher.");
        }
        if ($this->find(sprintf($this->onlineSwitcher, ':checked'))->isVisible()) {
            return 'Product online';
        }
        return 'Product offline';
    }
}
