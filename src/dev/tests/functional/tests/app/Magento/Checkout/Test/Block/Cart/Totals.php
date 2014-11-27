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

namespace Magento\Checkout\Test\Block\Cart;

use Mtf\Block\Block;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;

/**
 * Class Totals
 * Cart totals block
 */
class Totals extends Block
{
    /**
     * Grand total search mask
     *
     * @var string
     */
    protected $grandTotal = '//tr[normalize-space(td)="Grand Total"]//span';

    /**
     * Grand total search mask
     *
     * @var string
     */
    protected $grandTotalExclTax = '.totals.grand.excl span';

    /**
     * Grand total search mask
     *
     * @var string
     */
    protected $grandTotalInclTax = '.totals.grand.incl span';

    /**
     * Subtotal search mask
     *
     * @var string
     */
    protected $subtotal = '//tr[normalize-space(td)="Subtotal"]//span';

    /**
     * Subtotal search mask
     *
     * @var string
     */
    protected $subtotalExclTax = '.totals.sub.excl span';

    /**
     * Subtotal search mask
     *
     * @var string
     */
    protected $subtotalInclTax = '.totals.sub.incl span';

    /**
     * Tax search mask
     *
     * @var string
     */
    protected $tax = '.totals-tax span';

    /**
     * Get shipping price selector
     *
     * @var string
     */
    protected $shippingPriceSelector = '.shipping.excl .price';

    /**
     * Get discount
     *
     * @var string
     */
    protected $discount = '//tr[normalize-space(td)="Discount"]//span';

    /**
     * Get shipping price including tax selector
     *
     * @var string
     */
    protected $shippingPriceInclTaxSelector = '.shipping.incl .price';

    /**
     * Get shipping price block selector
     *
     * @var string
     */
    protected $shippingPriceBlockSelector = '.totals.shipping.excl';

    /**
     * Get Grand Total Text
     *
     * @return array|string
     */
    public function getGrandTotal()
    {
        $grandTotal = $this->_rootElement->find($this->grandTotal, Locator::SELECTOR_XPATH)->getText();
        return $this->escapeCurrency($grandTotal);
    }

    /**
     * Get Grand Total Text
     *
     * @return string
     */
    public function getGrandTotalIncludingTax()
    {
        $grandTotal = $this->_rootElement->find($this->grandTotalInclTax, Locator::SELECTOR_CSS)->getText();
        return $this->escapeCurrency($grandTotal);
    }

    /**
     * Get Grand Total Text
     *
     * @return string
     */
    public function getGrandTotalExcludingTax()
    {
        $grandTotal = $this->_rootElement->find($this->grandTotalExclTax, Locator::SELECTOR_CSS)->getText();
        return $this->escapeCurrency($grandTotal);
    }

    /**
     * Get Tax text from Order Totals
     *
     * @return array|string
     */
    public function getTax()
    {
        $taxPrice = $this->_rootElement->find($this->tax, Locator::SELECTOR_CSS)->getText();
        return $this->escapeCurrency($taxPrice);
    }

    /**
     * Check that Tax is visible
     *
     * @return bool
     */
    public function isTaxVisible()
    {
        return $this->_rootElement->find($this->tax, Locator::SELECTOR_CSS)->isVisible();
    }

    /**
     * Get Subtotal text
     *
     * @return string
     */
    public function getSubtotal()
    {
        $subTotal = $this->_rootElement->find($this->subtotal, Locator::SELECTOR_XPATH)->getText();
        return $this->escapeCurrency($subTotal);
    }

    /**
     * Get Subtotal text
     *
     * @return string
     */
    public function getSubtotalIncludingTax()
    {
        $subTotal = $this->_rootElement->find($this->subtotalInclTax, Locator::SELECTOR_CSS)->getText();
        return $this->escapeCurrency($subTotal);
    }

    /**
     * Get Subtotal text
     *
     * @return string
     */
    public function getSubtotalExcludingTax()
    {
        $subTotal = $this->_rootElement->find($this->subtotalExclTax, Locator::SELECTOR_CSS)->getText();
        return $this->escapeCurrency($subTotal);
    }

    /**
     * Method that escapes currency symbols
     *
     * @param string $price
     * @return string
     */
    protected function escapeCurrency($price)
    {
        preg_match("/^\\D*\\s*([\\d,\\.]+)\\s*\\D*$/", $price, $matches);
        return (isset($matches[1])) ? $matches[1] : null;
    }

    /**
     * Get discount
     *
     * @return string
     */
    public function getDiscount()
    {
        $discount = $this->_rootElement->find($this->discount, Locator::SELECTOR_XPATH)->getText();
        return $this->escapeCurrency($discount);
    }

    /**
     * Get shipping price
     *
     * @return string
     */
    public function getShippingPrice()
    {
        $shippingPrice = $this->_rootElement->find($this->shippingPriceSelector, Locator::SELECTOR_CSS)->getText();
        return $this->escapeCurrency($shippingPrice);
    }

    /**
     * Get shipping price
     *
     * @return string
     */
    public function getShippingPriceInclTax()
    {
        $shippingPrice = $this->_rootElement
            ->find($this->shippingPriceInclTaxSelector, Locator::SELECTOR_CSS)->getText();
        return $this->escapeCurrency($shippingPrice);
    }

    /**
     * Is visible shipping price block
     *
     * @return bool
     */
    public function isVisibleShippingPriceBlock()
    {
        return  $this->_rootElement->find($this->shippingPriceBlockSelector, Locator::SELECTOR_CSS)->isVisible();
    }
}
