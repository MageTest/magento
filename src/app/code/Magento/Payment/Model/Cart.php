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
namespace Magento\Payment\Model;

/**
 * Provide methods for collecting cart items information of specific sales model entity
 */
class Cart
{
    /**#@+
     * Amounts
     */
    const AMOUNT_TAX = 'tax';

    const AMOUNT_SHIPPING = 'shipping';

    const AMOUNT_DISCOUNT = 'discount';

    const AMOUNT_SUBTOTAL = 'subtotal';

    /**@@+*/

    /**
     * Sales model
     *
     * @var \Magento\Payment\Model\Cart\SalesModel\SalesModelInterface
     */
    protected $_salesModel;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Amounts
     *
     * @var array
     */
    protected $_amounts;

    /**
     * Custom items list
     *
     * @var array
     */
    protected $_customItems = array();

    /**
     * Items imported from sales model
     *
     * @var array
     */
    protected $_salesModelItems = array();

    /**
     * Flags that indicates whether discount, shopping and taxes should be transferred as cart item
     *
     * @var array
     */
    protected $_transferFlags = array();

    /**
     * Flags which indicates whether items data is outdated and has to be recollected
     *
     * @var bool
     */
    protected $_itemsCollectingRequired = true;

    /**
     * @param \Magento\Payment\Model\Cart\SalesModel\Factory $salesModelFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Sales\Model\Order|\Magento\Sales\Model\Quote $salesModel
     */
    public function __construct(
        \Magento\Payment\Model\Cart\SalesModel\Factory $salesModelFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        $salesModel
    ) {
        $this->_eventManager = $eventManager;
        $this->_salesModel = $salesModelFactory->create($salesModel);
        $this->_resetAmounts();
    }

    /**
     * Return payment cart sales model
     *
     * @return \Magento\Payment\Model\Cart\SalesModel\SalesModelInterface
     */
    public function getSalesModel()
    {
        return $this->_salesModel;
    }

    /**
     * Add amount to existing tax amount
     *
     * @param float $taxAmount
     * @return void
     */
    public function addTax($taxAmount)
    {
        $this->_addAmount(self::AMOUNT_TAX, $taxAmount);
    }

    /**
     * Set tax. Old value will be overwritten
     *
     * @param float $taxAmount
     * @return void
     */
    public function setTax($taxAmount)
    {
        $this->_setAmount(self::AMOUNT_TAX, $taxAmount);
    }

    /**
     * Get tax amount
     *
     * @return float
     */
    public function getTax()
    {
        return $this->_getAmount(self::AMOUNT_TAX);
    }

    /**
     * Add amount to existing discount amount
     *
     * @param float $discountAmount
     * @return void
     */
    public function addDiscount($discountAmount)
    {
        $this->_addAmount(self::AMOUNT_DISCOUNT, $discountAmount);
    }

    /**
     * Set discount. Old value will be overwritten
     *
     * @param float $discountAmount
     * @return void
     */
    public function setDiscount($discountAmount)
    {
        $this->_setAmount(self::AMOUNT_DISCOUNT, $discountAmount);
    }

    /**
     * Get discount amount
     *
     * @return float
     */
    public function getDiscount()
    {
        return $this->_getAmount(self::AMOUNT_DISCOUNT);
    }

    /**
     * Add amount to existing shipping amount
     *
     * @param float $shippingAmount
     * @return void
     */
    public function addShipping($shippingAmount)
    {
        $this->_addAmount(self::AMOUNT_SHIPPING, $shippingAmount);
    }

    /**
     * Set shipping. Old value will be overwritten
     *
     * @param float $shippingAmount
     * @return void
     */
    public function setShipping($shippingAmount)
    {
        $this->_setAmount(self::AMOUNT_SHIPPING, $shippingAmount);
    }

    /**
     * Get shipping amount
     *
     * @return float
     */
    public function getShipping()
    {
        return $this->_getAmount(self::AMOUNT_SHIPPING);
    }

    /**
     * Add amount to existing subtotal amount
     *
     * @param float $subtotalAmount
     * @return void
     */
    public function addSubtotal($subtotalAmount)
    {
        $this->_addAmount(self::AMOUNT_SUBTOTAL, $subtotalAmount);
    }

    /**
     * Get subtotal amount
     *
     * @return float
     */
    public function getSubtotal()
    {
        return $this->_getAmount(self::AMOUNT_SUBTOTAL);
    }

    /**
     * Add custom item (such as discount as line item, shipping as line item, etc)
     *
     * @param string $name
     * @param int $qty
     * @param float $amount
     * @param string|null $identifier
     * @return void
     */
    public function addCustomItem($name, $qty, $amount, $identifier = null)
    {
        $this->_customItems[] = $this->_createItemFromData($name, $qty, $amount, $identifier);
    }

    /**
     * Get all cart items
     *
     * @return array
     */
    public function getAllItems()
    {
        $this->_collectItemsAndAmounts();
        return array_merge($this->_salesModelItems, $this->_customItems);
    }

    /**
     * Get shipping, tax, subtotal and discount amounts all together
     *
     * @return array
     */
    public function getAmounts()
    {
        $this->_collectItemsAndAmounts();

        return $this->_amounts;
    }

    /**
     * Specify that shipping should be transferred as cart item
     *
     * @return void
     */
    public function setTransferShippingAsItem()
    {
        $this->_setTransferFlag(self::AMOUNT_SHIPPING, true);
    }

    /**
     * Specify that discount should be transferred as cart item
     *
     * @return void
     */
    public function setTransferDiscountAsItem()
    {
        $this->_setTransferFlag(self::AMOUNT_DISCOUNT, true);
    }

    /**
     * Collect all items, discounts, taxes, shipping to cart
     *
     * @return void
     */
    protected function _collectItemsAndAmounts()
    {
        if (!$this->_itemsCollectingRequired) {
            return;
        }

        $this->_itemsCollectingRequired = false;

        $this->_salesModelItems = array();
        $this->_customItems = array();

        $this->_resetAmounts();

        $this->_eventManager->dispatch('payment_cart_collect_items_and_amounts', array('cart' => $this));

        $this->_importItemsFromSalesModel();
        $this->_calculateCustomItemsSubtotal();
    }

    /**
     * Import items from sales model
     *
     * @return void
     */
    protected function _importItemsFromSalesModel()
    {
        $this->_salesModelItems = array();

        foreach ($this->_salesModel->getAllItems() as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            $this->_salesModelItems[] = $this->_createItemFromData(
                $item->getName(),
                $item->getQty(),
                $item->getPrice(),
                $item->getOriginalItem()->getId()
            );
        }

        $this->addSubtotal($this->_salesModel->getBaseSubtotal());
        $this->addTax($this->_salesModel->getBaseTaxAmount());
        $this->addShipping($this->_salesModel->getBaseShippingAmount());
        $this->addDiscount(abs($this->_salesModel->getBaseDiscountAmount()));
    }

    /**
     * Calculate subtotal from custom items
     *
     * @return void
     */
    protected function _calculateCustomItemsSubtotal()
    {
        if (!empty($this->_transferFlags[self::AMOUNT_DISCOUNT]) && $this->getDiscount()) {
            $this->addCustomItem(__('Discount'), 1, -1.00 * $this->getDiscount());
            $this->setDiscount(0);
        }

        if (!empty($this->_transferFlags[self::AMOUNT_SHIPPING]) && $this->getShipping()) {
            $this->addCustomItem(__('Shipping'), 1, $this->getShipping());
            $this->setShipping(0);
        }

        foreach ($this->_customItems as $item) {
            $this->_amounts[self::AMOUNT_SUBTOTAL] += $item->getAmount();
        }
    }

    /**
     * @param string $flagType
     * @param bool $value
     * @return void
     */
    protected function _setTransferFlag($flagType, $value)
    {
        $this->_transferFlags[$flagType] = (bool)$value;
        $this->_itemsCollectingRequired = true;
    }

    /**
     * @param string $amountType
     * @param float $amount
     * @return void
     */
    protected function _setAmount($amountType, $amount)
    {
        $this->_amounts[$amountType] = (double)$amount;
    }

    /**
     * @param string $amountType
     * @param float $amount
     * @return void
     */
    protected function _addAmount($amountType, $amount)
    {
        $this->_amounts[$amountType] += (double)$amount;
    }

    /**
     * Get amount of specified type
     *
     * @param string $amountType
     * @return float
     */
    protected function _getAmount($amountType)
    {
        return $this->_amounts[$amountType];
    }

    /**
     * Create item object from item data
     *
     * @param string $name
     * @param int $qty
     * @param float $amount
     * @param null|string $identifier
     * @return \Magento\Framework\Object
     */
    protected function _createItemFromData($name, $qty, $amount, $identifier = null)
    {
        $item = new \Magento\Framework\Object(array('name' => $name, 'qty' => $qty, 'amount' => (double)$amount));

        if ($identifier) {
            $item->setData('id', $identifier);
        }

        return $item;
    }

    /**
     * Set all amount types to zero
     *
     * @return void
     */
    protected function _resetAmounts()
    {
        $this->_amounts = array(
            self::AMOUNT_DISCOUNT => 0,
            self::AMOUNT_SHIPPING => 0,
            self::AMOUNT_SUBTOTAL => 0,
            self::AMOUNT_TAX => 0
        );
    }
}
