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

namespace Magento\Framework\Pricing\Price;

use Magento\Framework\Pricing\Amount\AmountInterface;

/**
 * Catalog price interface
 */
interface PriceInterface
{
    /**
     * Get price type code
     *
     * @return string
     */
    public function getPriceCode();

    /**
     * Get price value
     *
     * @return float
     */
    public function getValue();

    /**
     * Get Price Amount object
     *
     * @return AmountInterface
     */
    public function getAmount();

    /**
     * Get Custom Amount object
     * (specify adjustment code to exclude)
     *
     * @param float $amount
     * @param null|bool|string $exclude
     * @param null|array $context
     * @return AmountInterface
     */
    public function getCustomAmount($amount = null, $exclude = null, $context = []);
}
