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
namespace Magento\Sales\Block\Adminhtml\Order\Create\Sidebar;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Adminhtml sales order create sidebar block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class AbstractSidebar extends \Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate
{
    /**
     * Default Storage action on selected item
     *
     * @var string
     */
    protected $_sidebarStorageAction = 'add';

    /**
     * Sales config
     *
     * @var \Magento\Sales\Model\Config
     */
    protected $_salesConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Sales\Model\Config $salesConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Sales\Model\Config $salesConfig,
        array $data = array()
    ) {
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $data);
        $this->_salesConfig = $salesConfig;
    }

    /**
     * Return name of sidebar storage action
     *
     * @return string
     */
    public function getSidebarStorageAction()
    {
        return $this->_sidebarStorageAction;
    }

    /**
     * Retrieve display block availability
     *
     * @return bool
     */
    public function canDisplay()
    {
        return $this->getCustomerId();
    }

    /**
     * Retrieve disply item qty availablity
     *
     * @return false
     */
    public function canDisplayItemQty()
    {
        return false;
    }

    /**
     * Retrieve availability removing items in block
     *
     * @return true
     */
    public function canRemoveItems()
    {
        return true;
    }

    /**
     * Retrieve identifier of block item
     *
     * @param \Magento\Framework\Object $item
     * @return int
     */
    public function getIdentifierId($item)
    {
        return $item->getProductId();
    }

    /**
     * Retrieve item identifier of block item
     *
     * @param \Magento\Framework\Object $item
     * @return int
     */
    public function getItemId($item)
    {
        return $item->getId();
    }

    /**
     * Retrieve product identifier linked with item
     *
     * @param \Magento\Framework\Object $item
     * @return int
     */
    public function getProductId($item)
    {
        return $item->getId();
    }

    /**
     * Retrieve item count
     *
     * @return int
     */
    public function getItemCount()
    {
        $count = $this->getData('item_count');
        if (is_null($count)) {
            $count = count($this->getItems());
            $this->setData('item_count', $count);
        }
        return $count;
    }

    /**
     * Retrieve all items
     *
     * @return array
     */
    public function getItems()
    {
        $items = array();
        $collection = $this->getItemCollection();
        if ($collection) {
            $productTypes = $this->_salesConfig->getAvailableProductTypes();
            if (is_array($collection)) {
                $items = $collection;
            } else {
                $items = $collection->getItems();
            }

            /*
             * Filtering items by allowed product type
             */
            foreach ($items as $key => $item) {
                if ($item instanceof \Magento\Catalog\Model\Product) {
                    $type = $item->getTypeId();
                } else if ($item instanceof \Magento\Sales\Model\Order\Item) {
                    $type = $item->getProductType();
                } else if ($item instanceof \Magento\Sales\Model\Quote\Item) {
                    $type = $item->getProductType();
                } else {
                    $type = '';
                    // Maybe some item, that can give us product via getProduct()
                    if ($item instanceof \Magento\Framework\Object || method_exists($item, 'getProduct')) {
                        $product = $item->getProduct();
                        if ($product && $product instanceof \Magento\Catalog\Model\Product) {
                            $type = $product->getTypeId();
                        }
                    }
                }
                if (!in_array($type, $productTypes)) {
                    unset($items[$key]);
                }
            }
        }

        return $items;
    }

    /**
     * Retrieve item collection
     *
     * @return false
     */
    public function getItemCollection()
    {
        return false;
    }

    /**
     * Retrieve disply price availablity
     *
     * @return true
     */
    public function canDisplayPrice()
    {
        return true;
    }

    /**
     * Get item qty
     *
     * @param \Magento\Framework\Object $item
     * @return int
     */
    public function getItemQty(\Magento\Framework\Object $item)
    {
        return $item->getQty() * 1 ? $item->getQty() * 1 : 1;
    }

    /**
     * Check whether product configuration is required before adding to order
     *
     * @param string|int|null $productType
     * @return false
     */
    public function isConfigurationRequired($productType)
    {
        return false;
    }
}
