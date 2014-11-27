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
namespace Magento\Checkout\Service\V1\Item;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;

/** 
 * Write service object. 
 */
class WriteService implements WriteServiceInterface
{
    /**
     * Quote repository.
     *
     * @var \Magento\Sales\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * Product loader.
     *
     * @var \Magento\Catalog\Service\V1\Product\ProductLoader
     */
    protected $productLoader;

    /**
     * Constructs a write service object.
     *
     * @param \Magento\Sales\Model\QuoteRepository $quoteRepository Quote repository.
     * @param \Magento\Catalog\Service\V1\Product\ProductLoader $productLoader Product loader.
     */
    public function __construct(
        \Magento\Sales\Model\QuoteRepository $quoteRepository,
        \Magento\Catalog\Service\V1\Product\ProductLoader $productLoader
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->productLoader = $productLoader;
    }

    /**
     * {@inheritDoc}
     *
     * @param int $cartId The cart ID.
     * @param \Magento\Checkout\Service\V1\Data\Cart\Item $data The item.
     * @return int Item ID.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @throws \Magento\Framework\Exception\CouldNotSaveException The specified item could not be saved to the cart.
     * @throws \Magento\Framework\Exception\InputException The specified item or cart is not valid.
     */
    public function addItem($cartId, \Magento\Checkout\Service\V1\Data\Cart\Item $data)
    {
        $qty = $data->getQty();
        if (!is_numeric($qty) || $qty <= 0) {
            throw InputException::invalidFieldValue('qty', $qty);
        }
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        $product = $this->productLoader->load($data->getSku());

        try {
            $quote->addProduct($product, $qty);
            $this->quoteRepository->save($quote->collectTotals());
        } catch (\Exception $e) {
            throw new CouldNotSaveException('Could not add item to quote');
        }
        return $quote->getItemByProduct($product)->getId();
    }

    /**
     * {@inheritDoc}
     *
     * @param int $cartId The cart ID.
     * @param int $itemId The item ID of the item to be updated.
     * @param \Magento\Checkout\Service\V1\Data\Cart\Item $data The item.
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified item or cart does not exist.
     * @throws \Magento\Framework\Exception\CouldNotSaveException The item could not be updated.
     * @throws \Magento\Framework\Exception\InputException The specified item or cart is not valid.
     */
    public function updateItem($cartId, $itemId, \Magento\Checkout\Service\V1\Data\Cart\Item $data)
    {
        $qty = $data->getQty();
        if (!is_numeric($qty) || $qty <= 0) {
            throw InputException::invalidFieldValue('qty', $qty);
        }
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        $quoteItem = $quote->getItemById($itemId);
        if (!$quoteItem) {
            throw new NoSuchEntityException("Cart $cartId doesn't contain item  $itemId");
        }
        $quoteItem->setData('qty', $qty);

        try {
            $this->quoteRepository->save($quote->collectTotals());
        } catch (\Exception $e) {
            throw new CouldNotSaveException('Could not update quote item');
        }
        return true;
    }

    /**
     * {@inheritDoc}
     *
     * @param int $cartId The cart ID.
     * @param int $itemId The item ID of the item to be removed.
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified item or cart does not exist.
     * @throws \Magento\Framework\Exception\CouldNotSaveException The item could not be removed.
     */
    public function removeItem($cartId, $itemId)
    {
        /**
         * Quote.
         *
         * @var \Magento\Sales\Model\Quote $quote
         */
        $quote = $this->quoteRepository->getActive($cartId);
        $quoteItem = $quote->getItemById($itemId);
        if (!$quoteItem) {
            throw new NoSuchEntityException("Cart $cartId doesn't contain item  $itemId");
        }
        try {
            $quote->removeItem($itemId);
            $this->quoteRepository->save($quote->collectTotals());
        } catch (\Exception $e) {
            throw new CouldNotSaveException('Could not remove item from quote');
        }
        return true;
    }
}
