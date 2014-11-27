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

/**
 * Tax Event Observer
 */
namespace Magento\Tax\Model;

class Observer
{
    /**
     * Tax data
     *
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxData;

    /**
     * @var \Magento\Tax\Model\Sales\Order\TaxFactory
     */
    protected $_orderTaxFactory;

    /**
     * @var \Magento\Tax\Model\Sales\Order\Tax\ItemFactory
     */
    protected $_taxItemFactory;

    /**
     * @var \Magento\Tax\Model\Calculation
     */
    protected $_calculation;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Tax\Model\Resource\Report\TaxFactory
     */
    protected $_reportTaxFactory;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Tax\Model\Sales\Order\TaxFactory $orderTaxFactory
     * @param \Magento\Tax\Model\Sales\Order\Tax\ItemFactory $taxItemFactory
     * @param \Magento\Tax\Model\Calculation $calculation
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Tax\Model\Resource\Report\TaxFactory $reportTaxFactory
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     */
    public function __construct(
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Tax\Model\Sales\Order\TaxFactory $orderTaxFactory,
        \Magento\Tax\Model\Sales\Order\Tax\ItemFactory $taxItemFactory,
        \Magento\Tax\Model\Calculation $calculation,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Tax\Model\Resource\Report\TaxFactory $reportTaxFactory,
        \Magento\Framework\Locale\ResolverInterface $localeResolver
    ) {
        $this->_taxData = $taxData;
        $this->_orderTaxFactory = $orderTaxFactory;
        $this->_taxItemFactory = $taxItemFactory;
        $this->_calculation = $calculation;
        $this->_localeDate = $localeDate;
        $this->_reportTaxFactory = $reportTaxFactory;
        $this->_localeResolver = $localeResolver;
    }

    /**
     * Put quote address tax information into order
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function salesEventConvertQuoteAddressToOrder(\Magento\Framework\Event\Observer $observer)
    {
        $address = $observer->getEvent()->getAddress();
        $order = $observer->getEvent()->getOrder();

        $taxes = $address->getAppliedTaxes();
        if (is_array($taxes)) {
            if (is_array($order->getAppliedTaxes())) {
                $taxes = array_merge($order->getAppliedTaxes(), $taxes);
            }
            $order->setAppliedTaxes($taxes);
            $order->setConvertingFromQuote(true);
        }

        $itemAppliedTaxes = $address->getItemsAppliedTaxes();
        if (is_array($itemAppliedTaxes)) {
            if (is_array($order->getItemAppliedTaxes())) {
                $itemAppliedTaxes = array_merge($order->getItemAppliedTaxes(), $itemAppliedTaxes);
            }
            $order->setItemAppliedTaxes($itemAppliedTaxes);
        }
    }

    /**
     * Save order tax information
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function salesEventOrderAfterSave(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        if (!$order->getConvertingFromQuote() || $order->getAppliedTaxIsSaved()) {
            return;
        }

        $getTaxesForItems = $order->getItemAppliedTaxes();
        $taxes = $order->getAppliedTaxes();

        $ratesIdQuoteItemId = array();
        if (!is_array($getTaxesForItems)) {
            $getTaxesForItems = array();
        }
        foreach ($getTaxesForItems as $quoteItemId => $taxesArray) {
            foreach ($taxesArray as $rates) {
                if (count($rates['rates']) == 1) {
                    $ratesIdQuoteItemId[$rates['id']][] = array(
                        'id' => $rates['item_id'],
                        'percent' => $rates['percent'],
                        'code' => $rates['rates'][0]['code'],
                        'associated_item_id' => $rates['associated_item_id'],
                        'item_type' => $rates['item_type'],
                        'amount' => $rates['amount'],
                        'base_amount' => $rates['base_amount'],
                        'real_amount' => $rates['amount'],
                        'real_base_amount' => $rates['base_amount'],
                    );
                } else {
                    $percentDelta = $rates['percent'];
                    $percentSum = 0;
                    foreach ($rates['rates'] as $rate) {
                        $real_amount = $rates['amount'] * $rate['percent'] / $rates['percent'];
                        $real_base_amount = $rates['base_amount'] * $rate['percent'] / $rates['percent'];
                        $ratesIdQuoteItemId[$rates['id']][] = array(
                            'id' => $rates['item_id'],
                            'percent' => $rate['percent'],
                            'code' => $rate['code'],
                            'associated_item_id' => $rates['associated_item_id'],
                            'item_type' => $rates['item_type'],
                            'amount' => $rates['amount'],
                            'base_amount' => $rates['base_amount'],
                            'real_amount' => $real_amount,
                            'real_base_amount' => $real_base_amount,
                        );
                        $percentSum += $rate['percent'];
                    }

                    if ($percentDelta != $percentSum) {
                        $delta = $percentDelta - $percentSum;
                        foreach ($ratesIdQuoteItemId[$rates['id']] as &$rateTax) {
                            if ($rateTax['id'] == $quoteItemId) {
                                $rateTax['percent'] = $rateTax['percent'] / $percentSum * $delta + $rateTax['percent'];
                            }
                        }
                    }
                }
            }
        }

        foreach ($taxes as $id => $row) {
            foreach ($row['rates'] as $tax) {
                if (is_null($row['percent'])) {
                    $baseRealAmount = $row['base_amount'];
                } else {
                    if ($row['percent'] == 0 || $tax['percent'] == 0) {
                        continue;
                    }
                    $baseRealAmount = $row['base_amount'] / $row['percent'] * $tax['percent'];
                }
                $hidden = isset($row['hidden']) ? $row['hidden'] : 0;
                $priority = isset($tax['priority']) ? $tax['priority'] : 0;
                $position = isset($tax['position']) ? $tax['position'] : 0;
                $process = isset($row['process']) ? $row['process'] : 0;
                $data = array(
                    'order_id' => $order->getId(),
                    'code' => $tax['code'],
                    'title' => $tax['title'],
                    'hidden' => $hidden,
                    'percent' => $tax['percent'],
                    'priority' => $priority,
                    'position' => $position,
                    'amount' => $row['amount'],
                    'base_amount' => $row['base_amount'],
                    'process' => $process,
                    'base_real_amount' => $baseRealAmount
                );

                /** @var $orderTax \Magento\Tax\Model\Sales\Order\Tax */
                $orderTax = $this->_orderTaxFactory->create();
                $result = $orderTax->setData($data)->save();

                if (isset($ratesIdQuoteItemId[$id])) {
                    foreach ($ratesIdQuoteItemId[$id] as $quoteItemId) {
                        if ($quoteItemId['code'] == $tax['code']) {
                            $itemId = null;
                            $associatedItemId = null;
                            if (isset($quoteItemId['id'])) {
                                //This is a product item
                                $item = $order->getItemByQuoteItemId($quoteItemId['id']);
                                $itemId = $item->getId();
                            } elseif (isset($quoteItemId['associated_item_id'])) {
                                //This item is associated with a product item
                                $item = $order->getItemByQuoteItemId($quoteItemId['associated_item_id']);
                                $associatedItemId = $item->getId();
                            }

                            $data = array(
                                'item_id' => $itemId,
                                'tax_id' => $result->getTaxId(),
                                'tax_percent' => $quoteItemId['percent'],
                                'associated_item_id' => $associatedItemId,
                                'amount' => $quoteItemId['amount'],
                                'base_amount' => $quoteItemId['base_amount'],
                                'real_amount' => $quoteItemId['real_amount'],
                                'real_base_amount' => $quoteItemId['real_base_amount'],
                                'taxable_item_type' => $quoteItemId['item_type'],
                            );
                            /** @var $taxItem \Magento\Tax\Model\Sales\Order\Tax\Item */
                            $taxItem = $this->_taxItemFactory->create();
                            $taxItem->setData($data)->save();
                        }
                    }
                }
            }
        }

        $order->setAppliedTaxIsSaved(true);
    }

    /**
     * Refresh sales tax report statistics for last day
     *
     * @param \Magento\Cron\Model\Schedule $schedule
     * @return $this
     */
    public function aggregateSalesReportTaxData($schedule)
    {
        $this->_localeResolver->emulate(0);
        $currentDate = $this->_localeDate->date();
        $date = $currentDate->subHour(25);
        /** @var $reportTax \Magento\Tax\Model\Resource\Report\Tax */
        $reportTax = $this->_reportTaxFactory->create();
        $reportTax->aggregate($date);
        $this->_localeResolver->revert();
        return $this;
    }

    /**
     * Reset extra tax amounts on quote addresses before recollecting totals
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function quoteCollectTotalsBefore(\Magento\Framework\Event\Observer $observer)
    {
        /* @var $quote \Magento\Sales\Model\Quote */
        $quote = $observer->getEvent()->getQuote();
        foreach ($quote->getAllAddresses() as $address) {
            $address->setExtraTaxAmount(0);
            $address->setBaseExtraTaxAmount(0);
        }
        return $this;
    }
}
