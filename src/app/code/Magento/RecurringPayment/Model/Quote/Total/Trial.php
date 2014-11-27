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
namespace Magento\RecurringPayment\Model\Quote\Total;

/**
 * Total model for recurring payment trial payment
 */
class Trial extends \Magento\RecurringPayment\Model\Quote\Total\AbstractRecurring
{
    /**
     * Custom row total/payment keys
     *
     * @var string
     */
    protected $_itemRowTotalKey = 'recurring_trial_payment';

    /**
     * @var string
     */
    protected $_paymentDataKey = 'trial_billing_amount';

    /**
     * Get trial payment label
     *
     * @return string
     */
    public function getLabel()
    {
        return __('Trial Payment');
    }

    /**
     * Prevent compounding nominal subtotal in case if the trial payment exists
     *
     * @param \Magento\Sales\Model\Quote\Address $address
     * @param \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @return void
     * @see \Magento\Sales\Model\Quote\Address\Total\Nominal\Subtotal
     */
    protected function _afterCollectSuccess($address, $item)
    {
        $item->setData('skip_compound_row_total', true);
    }
}
