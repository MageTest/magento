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
namespace Magento\Bundle\Model\Sales\Order\Pdf\Items;

use Magento\Catalog\Model\Product\Type\AbstractType;

/**
 * Sales Order Pdf Items renderer
 */
abstract class AbstractItems extends \Magento\Sales\Model\Order\Pdf\Items\AbstractItems
{
    /**
     * Getting all available children for Invoice, Shipment or CreditMemo item
     *
     * @param \Magento\Framework\Object $item
     * @return array
     */
    public function getChilds($item)
    {
        $itemsArray = array();

        $items = null;
        if ($item instanceof \Magento\Sales\Model\Order\Invoice\Item) {
            $items = $item->getInvoice()->getAllItems();
        } elseif ($item instanceof \Magento\Sales\Model\Order\Shipment\Item) {
            $items = $item->getShipment()->getAllItems();
        } elseif ($item instanceof \Magento\Sales\Model\Order\Creditmemo\Item) {
            $items = $item->getCreditmemo()->getAllItems();
        }

        if ($items) {
            foreach ($items as $value) {
                $parentItem = $value->getOrderItem()->getParentItem();
                if ($parentItem) {
                    $itemsArray[$parentItem->getId()][$value->getOrderItemId()] = $value;
                } else {
                    $itemsArray[$value->getOrderItem()->getId()][$value->getOrderItemId()] = $value;
                }
            }
        }

        if (isset($itemsArray[$item->getOrderItem()->getId()])) {
            return $itemsArray[$item->getOrderItem()->getId()];
        } else {
            return null;
        }
    }

    /**
     * Retrieve is Shipment Separately flag for Item
     *
     * @param \Magento\Framework\Object $item
     * @return bool
     */
    public function isShipmentSeparately($item = null)
    {
        if ($item) {
            if ($item->getOrderItem()) {
                $item = $item->getOrderItem();
            }
            $parentItem = $item->getParentItem();
            if ($parentItem) {
                $options = $parentItem->getProductOptions();
                if ($options) {
                    return (isset($options['shipment_type'])
                        && $options['shipment_type'] == AbstractType::SHIPMENT_SEPARATELY);
                }
            } else {
                $options = $item->getProductOptions();
                if ($options) {
                    return !(isset($options['shipment_type'])
                        && $options['shipment_type'] == AbstractType::SHIPMENT_SEPARATELY);
                }
            }
        }

        $options = $this->getOrderItem()->getProductOptions();
        if ($options) {
            if (isset($options['shipment_type']) && $options['shipment_type'] == AbstractType::SHIPMENT_SEPARATELY) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retrieve is Child Calculated
     *
     * @param \Magento\Framework\Object $item
     * @return bool
     */
    public function isChildCalculated($item = null)
    {
        if ($item) {
            if ($item->getOrderItem()) {
                $item = $item->getOrderItem();
            }
            $parentItem = $item->getParentItem();
            if ($parentItem) {
                $options = $parentItem->getProductOptions();
                if ($options) {
                    return (isset($options['product_calculations'])
                        && $options['product_calculations'] == AbstractType::CALCULATE_CHILD);
                }
            } else {
                $options = $item->getProductOptions();
                if ($options) {
                    return !(isset($options['product_calculations'])
                        && $options['product_calculations'] == AbstractType::CALCULATE_CHILD);
                }
            }
        }

        $options = $this->getOrderItem()->getProductOptions();
        if ($options) {
            if (isset($options['product_calculations'])
                && $options['product_calculations'] == AbstractType::CALCULATE_CHILD
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retrieve Bundle Options
     *
     * @param \Magento\Framework\Object $item
     * @return array
     */
    public function getBundleOptions($item = null)
    {
        $options = $this->getOrderItem()->getProductOptions();
        if ($options && isset($options['bundle_options'])) {
            return $options['bundle_options'];
        }
        return array();
    }

    /**
     * Retrieve Selection attributes
     *
     * @param \Magento\Framework\Object $item
     * @return mixed
     */
    public function getSelectionAttributes($item)
    {
        if ($item instanceof \Magento\Sales\Model\Order\Item) {
            $options = $item->getProductOptions();
        } else {
            $options = $item->getOrderItem()->getProductOptions();
        }
        if (isset($options['bundle_selection_attributes'])) {
            return unserialize($options['bundle_selection_attributes']);
        }
        return null;
    }

    /**
     * Retrieve Order options
     *
     * @param \Magento\Framework\Object $item
     * @return array
     */
    public function getOrderOptions($item = null)
    {
        $result = array();
        $options = $this->getOrderItem()->getProductOptions();
        if ($options) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (!empty($options['attributes_info'])) {
                $result = array_merge($options['attributes_info'], $result);
            }
        }
        return $result;
    }

    /**
     * Retrieve Order Item
     *
     * @return \Magento\Sales\Model\Order\Item
     */
    public function getOrderItem()
    {
        if ($this->getItem() instanceof \Magento\Sales\Model\Order\Item) {
            return $this->getItem();
        } else {
            return $this->getItem()->getOrderItem();
        }
    }

    /**
     * Retrieve Value HTML
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return string
     */
    public function getValueHtml($item)
    {
        $result = $this->filterManager->stripTags($item->getName());
        if (!$this->isShipmentSeparately($item)) {
            $attributes = $this->getSelectionAttributes($item);
            if ($attributes) {
                $result = $this->filterManager->sprintf($attributes['qty'], array('format' => '%d')) . ' x ' . $result;
            }
        }
        if (!$this->isChildCalculated($item)) {
            $attributes = $this->getSelectionAttributes($item);
            if ($attributes) {
                $result .= " " . $this->filterManager->stripTags(
                    $this->getOrderItem()->getOrder()->formatPrice($attributes['price'])
                );
            }
        }
        return $result;
    }

    /**
     * Can show price info for item
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return bool
     */
    public function canShowPriceInfo($item)
    {
        if ($item->getOrderItem()->getParentItem() && $this->isChildCalculated() ||
            !$item->getOrderItem()->getParentItem() && !$this->isChildCalculated()
        ) {
            return true;
        }
        return false;
    }
}
