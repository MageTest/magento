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
namespace Magento\Weee\Model\Total\Invoice;

use Magento\Weee\Helper\Data as WeeeHelper;

class Weee extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{
    /**
     * Weee data
     *
     * @var \Magento\Weee\Helper\Data
     */
    protected $_weeeData = null;

    /**
     * Constructor
     *
     * By default is looking for first argument as array and assigns it as object
     * attributes This behavior may change in child classes
     *
     * @param \Magento\Weee\Helper\Data $weeeData
     * @param array $data
     */
    public function __construct(\Magento\Weee\Helper\Data $weeeData, array $data = array())
    {
        $this->_weeeData = $weeeData;
        parent::__construct($data);
    }

    /**
     * Collect Weee amounts for the invoice
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $store = $invoice->getStore();
        $order = $invoice->getOrder();

        $totalWeeeAmount = 0;
        $baseTotalWeeeAmount = 0;
        $totalWeeeAmountInclTax = 0;
        $baseTotalWeeeAmountInclTax = 0;
        $totalWeeeTaxAmount = 0;
        $baseTotalWeeeTaxAmount = 0;

        /** @var \Magento\Sales\Model\Order\Invoice\Item $item */
        foreach ($invoice->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();
            $orderItemQty = $orderItem->getQtyOrdered();

            if (!$orderItemQty || $orderItem->isDummy() || $item->getQty() <= 0) {
                continue;
            }

            $ratio = $item->getQty() / $orderItemQty;
            $orderItemWeeeAmount = $orderItem->getWeeeTaxAppliedRowAmount();
            $orderItemBaseWeeeAmount = $orderItem->getBaseWeeeTaxAppliedRowAmnt();
            $weeeAmount = $invoice->roundPrice($orderItemWeeeAmount * $ratio);
            $baseWeeeAmount = $invoice->roundPrice($orderItemBaseWeeeAmount * $ratio, 'base');

            $orderItemWeeeInclTax = $this->_weeeData->getRowWeeeTaxInclTax($orderItem);
            $orderItemBaseWeeeInclTax = $this->_weeeData->getBaseRowWeeeTaxInclTax($orderItem);
            $weeeAmountInclTax = $invoice->roundPrice($orderItemWeeeInclTax * $ratio);
            $baseWeeeAmountInclTax = $invoice->roundPrice($orderItemBaseWeeeInclTax * $ratio, 'base');

            $orderItemWeeeTax = $orderItemWeeeInclTax - $orderItemWeeeAmount;
            $itemWeeeTax = $weeeAmountInclTax - $weeeAmount;
            $itemBaseWeeeTax = $baseWeeeAmountInclTax - $baseWeeeAmount;

            if ($item->isLast()) {
                $weeeAmount = $orderItemWeeeAmount - $this->_weeeData->getWeeeAmountInvoiced($orderItem);
                $baseWeeeAmount =
                    $orderItemBaseWeeeAmount - $this->_weeeData->getBaseWeeeAmountInvoiced($orderItem);
                $itemWeeeTax = $orderItemWeeeTax - $this->_weeeData->getWeeeTaxAmountInvoiced($orderItem);
                $itemBaseWeeeTax =
                    $orderItemWeeeTax - $this->_weeeData->getBaseWeeeTaxAmountInvoiced($orderItem);
            }

            $totalWeeeTaxAmount += $itemWeeeTax;
            $baseTotalWeeeTaxAmount += $itemBaseWeeeTax;

            //Set the ratio of the tax amount in invoice item compared to tax amount in order item
            //This information is needed to calculate tax per tax rate later
            if ($orderItemWeeeTax != 0) {
                if (!$item->getTaxRatio()) {
                    $item->setTaxRatio([]);
                }
                $taxRatio = $item->getTaxRatio();
                $taxRatio[\Magento\Weee\Model\Total\Quote\Weee::ITEM_TYPE] = $itemWeeeTax / $orderItemWeeeTax;
                $item->setTaxRatio($taxRatio);
            }

            $item->setWeeeTaxAppliedRowAmount($weeeAmount);
            $item->setBaseWeeeTaxAppliedRowAmount($baseWeeeAmount);
            $newApplied = array();
            $applied = $this->_weeeData->getApplied($orderItem);
            foreach ($applied as $one) {
                $title = $one['title'];
                $one['base_row_amount'] = $invoice->roundPrice($one['base_row_amount'] * $ratio, $title . '_base');
                $one['row_amount'] = $invoice->roundPrice($one['row_amount'] * $ratio, $title);
                $one['base_row_amount_incl_tax'] = $invoice->roundPrice(
                    $one['base_row_amount_incl_tax'] * $ratio,
                    $title . '_base'
                );
                $one['row_amount_incl_tax'] = $invoice->roundPrice($one['row_amount_incl_tax'] * $ratio, $title);

                $newApplied[] = $one;
            }
            $this->_weeeData->setApplied($item, $newApplied);

            //Update order item
            $newApplied = array();
            $applied = $this->_weeeData->getApplied($orderItem);
            foreach ($applied as $one) {
                if (isset($one[WeeeHelper::KEY_BASE_WEEE_AMOUNT_INVOICED])) {
                    $one[WeeeHelper::KEY_BASE_WEEE_AMOUNT_INVOICED] =
                        $one[WeeeHelper::KEY_BASE_WEEE_AMOUNT_INVOICED] + $baseWeeeAmount;
                } else {
                    $one[WeeeHelper::KEY_BASE_WEEE_AMOUNT_INVOICED] = $baseWeeeAmount;
                }
                if (isset($one[WeeeHelper::KEY_WEEE_AMOUNT_INVOICED])) {
                    $one[WeeeHelper::KEY_WEEE_AMOUNT_INVOICED] =
                        $one[WeeeHelper::KEY_WEEE_AMOUNT_INVOICED] + $weeeAmount;
                } else {
                    $one[WeeeHelper::KEY_WEEE_AMOUNT_INVOICED] = $weeeAmount;
                }
                if (isset($one[WeeeHelper::KEY_BASE_WEEE_TAX_AMOUNT_INVOICED])) {
                    $one[WeeeHelper::KEY_BASE_WEEE_TAX_AMOUNT_INVOICED] =
                        $one[WeeeHelper::KEY_BASE_WEEE_TAX_AMOUNT_INVOICED] + $itemWeeeTax;
                } else {
                    $one[WeeeHelper::KEY_BASE_WEEE_TAX_AMOUNT_INVOICED] = $itemWeeeTax;
                }
                if (isset($one[WeeeHelper::KEY_WEEE_TAX_AMOUNT_INVOICED])) {
                    $one[WeeeHelper::KEY_WEEE_TAX_AMOUNT_INVOICED] =
                        $one[WeeeHelper::KEY_WEEE_TAX_AMOUNT_INVOICED] + $itemBaseWeeeTax;
                } else {
                    $one[WeeeHelper::KEY_WEEE_TAX_AMOUNT_INVOICED] = $itemBaseWeeeTax;
                }
                $newApplied[] = $one;
            }
            $this->_weeeData->setApplied($orderItem, $newApplied);

            $item->setWeeeTaxRowDisposition($item->getWeeeTaxDisposition() * $item->getQty());
            $item->setBaseWeeeTaxRowDisposition($item->getBaseWeeeTaxDisposition() * $item->getQty());

            $totalWeeeAmount += $weeeAmount;
            $baseTotalWeeeAmount += $baseWeeeAmount;
            
            $totalWeeeAmountInclTax += $weeeAmountInclTax;
            $baseTotalWeeeAmountInclTax += $baseWeeeAmountInclTax;
        }

        $allowedTax = $order->getTaxAmount() - $order->getTaxInvoiced() - $invoice->getTaxAmount();
        $allowedBaseTax = $order->getBaseTaxAmount() - $order->getBaseTaxInvoiced() - $invoice->getBaseTaxAmount();
        $totalWeeeTaxAmount = min($totalWeeeTaxAmount, $allowedTax);
        $baseTotalWeeeTaxAmount = min($baseTotalWeeeTaxAmount, $allowedBaseTax);

        $invoice->setTaxAmount($invoice->getTaxAmount() + $totalWeeeTaxAmount);
        $invoice->setBaseTaxAmount($invoice->getBaseTaxAmount() + $baseTotalWeeeTaxAmount);

        // Add FPT to subtotal and grand total
        if ($this->_weeeData->includeInSubtotal($store)) {
            $order = $invoice->getOrder();
            $allowedSubtotal = $order->getSubtotal() - $order->getSubtotalInvoiced() - $invoice->getSubtotal();
            $allowedBaseSubtotal = $order->getBaseSubtotal() -
                $order->getBaseSubtotalInvoiced() -
                $invoice->getBaseSubtotal();
            $totalWeeeAmount = min($allowedSubtotal, $totalWeeeAmount);
            $baseTotalWeeeAmount = min($allowedBaseSubtotal, $baseTotalWeeeAmount);

            $invoice->setSubtotal($invoice->getSubtotal() + $totalWeeeAmount);
            $invoice->setBaseSubtotal($invoice->getBaseSubtotal() + $baseTotalWeeeAmount);
        }

        if (!$invoice->isLast()) {
            // need to add the Weee amounts including all their taxes
            $invoice->setSubtotalInclTax($invoice->getSubtotalInclTax() + $totalWeeeAmountInclTax);
            $invoice->setBaseSubtotalInclTax($invoice->getBaseSubtotalInclTax() + $baseTotalWeeeAmountInclTax);
        } else {
            // since the Subtotal Incl Tax line will already have the taxes on Weee, just add the non-taxable amounts
            $invoice->setSubtotalInclTax($invoice->getSubtotalInclTax() + $totalWeeeAmount);
            $invoice->setBaseSubtotalInclTax($invoice->getBaseSubtotalInclTax() + $baseTotalWeeeAmount);
        }

        $invoice->setGrandTotal($invoice->getGrandTotal() + $totalWeeeAmount + $totalWeeeTaxAmount);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseTotalWeeeAmount + $baseTotalWeeeTaxAmount);

        return $this;
    }
}
