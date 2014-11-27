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
namespace Magento\SalesRule\Model\Rule\Action\Discount;

class CartFixed extends AbstractDiscount
{
    /**
     * Store information about addresses which cart fixed rule applied for
     *
     * @var int[]
     */
    protected $_cartFixedRuleUsedForAddress = array();

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data
     */
    public function calculate($rule, $item, $qty)
    {
        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
        $discountData = $this->discountFactory->create();

        $ruleTotals = $this->validator->getRuleItemTotalsInfo($rule->getId());

        $quote = $item->getQuote();
        $address = $item->getAddress();

        $itemPrice = $this->validator->getItemPrice($item);
        $baseItemPrice = $this->validator->getItemBasePrice($item);
        $itemOriginalPrice = $this->validator->getItemOriginalPrice($item);
        $baseItemOriginalPrice = $this->validator->getItemBaseOriginalPrice($item);

        /**
         * prevent applying whole cart discount for every shipping order, but only for first order
         */
        if ($quote->getIsMultiShipping()) {
            $usedForAddressId = $this->getCartFixedRuleUsedForAddress($rule->getId());
            if ($usedForAddressId && $usedForAddressId != $address->getId()) {
                return $discountData;
            } else {
                $this->setCartFixedRuleUsedForAddress($rule->getId(), $address->getId());
            }
        }
        $cartRules = $address->getCartFixedRules();
        if (!isset($cartRules[$rule->getId()])) {
            $cartRules[$rule->getId()] = $rule->getDiscountAmount();
        }

        if ($cartRules[$rule->getId()] > 0) {
            $store = $quote->getStore();
            if ($ruleTotals['items_count'] <= 1) {
                $quoteAmount = $this->priceCurrency->convert($cartRules[$rule->getId()], $store);
                $baseDiscountAmount = min($baseItemPrice * $qty, $cartRules[$rule->getId()]);
            } else {
                $discountRate = $baseItemPrice * $qty / $ruleTotals['base_items_price'];
                $maximumItemDiscount = $rule->getDiscountAmount() * $discountRate;
                $quoteAmount = $this->priceCurrency->convert($maximumItemDiscount, $store);

                $baseDiscountAmount = min($baseItemPrice * $qty, $maximumItemDiscount);
                $this->validator->decrementRuleItemTotalsCount($rule->getId());
            }

            $baseDiscountAmount = $this->priceCurrency->round($baseDiscountAmount);

            $cartRules[$rule->getId()] -= $baseDiscountAmount;

            $discountData->setAmount($this->priceCurrency->round(min($itemPrice * $qty, $quoteAmount)));
            $discountData->setBaseAmount($baseDiscountAmount);
            $discountData->setOriginalAmount(min($itemOriginalPrice * $qty, $quoteAmount));
            $discountData->setBaseOriginalAmount($this->priceCurrency->round($baseItemOriginalPrice));
        }
        $address->setCartFixedRules($cartRules);

        return $discountData;
    }

    /**
     * Set information about usage cart fixed rule by quote address
     *
     * @param int $ruleId
     * @param int $itemId
     * @return void
     */
    protected function setCartFixedRuleUsedForAddress($ruleId, $itemId)
    {
        $this->_cartFixedRuleUsedForAddress[$ruleId] = $itemId;
    }

    /**
     * Retrieve information about usage cart fixed rule by quote address
     *
     * @param int $ruleId
     * @return int|null
     */
    protected function getCartFixedRuleUsedForAddress($ruleId)
    {
        if (isset($this->_cartFixedRuleUsedForAddress[$ruleId])) {
            return $this->_cartFixedRuleUsedForAddress[$ruleId];
        }
        return null;
    }
}
