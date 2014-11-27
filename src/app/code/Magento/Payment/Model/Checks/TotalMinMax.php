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
namespace Magento\Payment\Model\Checks;

use Magento\Sales\Model\Quote;

class TotalMinMax implements SpecificationInterface
{
    /**
     * Config value key for min order total
     */
    const MIN_ORDER_TOTAL = 'min_order_total';

    /**
     * Config value key for max order total
     */
    const MAX_ORDER_TOTAL = 'max_order_total';

    /**
     * Check whether payment method is applicable to quote
     *
     * @param PaymentMethodChecksInterface $paymentMethod
     * @param \Magento\Sales\Model\Quote $quote
     * @return bool
     */
    public function isApplicable(PaymentMethodChecksInterface $paymentMethod, Quote $quote)
    {
        $total = $quote->getBaseGrandTotal();
        $minTotal = $paymentMethod->getConfigData(self::MIN_ORDER_TOTAL);
        $maxTotal = $paymentMethod->getConfigData(self::MAX_ORDER_TOTAL);
        if (!empty($minTotal) && $total < $minTotal || !empty($maxTotal) && $total > $maxTotal) {
            return false;
        }
        return true;
    }
}
