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

namespace Magento\Catalog\Model\Product\Option\Validator;

use Magento\Catalog\Model\Product\Option;

class Select extends DefaultValidator
{
    /**
     * Check if all values are marked for removal
     *
     * @param array $values
     * @return bool
     */
    protected function checkAllValuesRemoved($values)
    {
        foreach ($values as $value) {
            if (!array_key_exists('is_delete', $value) || $value['is_delete'] != 1) {
                return false;
            }
        }
        return true;
    }

    /**
     * Validate option type fields
     *
     * @param Option $option
     * @return bool
     */
    protected function validateOptionValue(Option $option)
    {
        $values = $option->getData('values');
        if (!is_array($values) || $this->isEmpty($values)) {
            return false;
        }

        //forbid removal of last value for option
        if ($this->checkAllValuesRemoved($values)) {
            return false;
        }

        $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        if ($option->getProduct()) {
            $storeId = $option->getProduct()->getStoreId();
        }
        foreach ($option->getData('values') as $value) {
            $type = isset($value['price_type']) ? $value['price_type'] : null;
            $price = isset($value['price']) ? $value['price'] : null;
            $title = isset($value['title']) ? $value['title'] : null;
            if (!$this->isValidOptionPrice($type, $price, $storeId)
                || !$this->isValidOptionTitle($title, $storeId)
            ) {
                return false;
            }
        }
        return true;
    }

    /**
     * Validate option price
     *
     * @param string $priceType
     * @param int $price
     * @param int $storeId
     * @return bool
     */
    protected function isValidOptionPrice($priceType, $price, $storeId)
    {
        // we should be able to remove website values for default store fallback
        if ($storeId > \Magento\Store\Model\Store::DEFAULT_STORE_ID && $priceType === null && $price === null) {
            return true;
        }
        if (!$this->isInRange($priceType, $this->priceTypes) || $this->isNegative($price)) {
            return false;
        }

        return true;
    }
}
