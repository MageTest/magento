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
namespace Magento\Checkout\Service\V1\Coupon;

use \Magento\Checkout\Service\V1\Data\Cart\CouponBuilder as CouponBuilder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Coupon write service object.
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
     * Coupon builder.
     *
     * @var CouponBuilder
     */
    protected $couponBuilder;

    /**
     * Constructs a coupon write service object.
     *
     * @param \Magento\Sales\Model\QuoteRepository $quoteRepository Quote repository.
     * @param CouponBuilder $couponBuilder Coupon builder.
     */
    public function __construct(
        \Magento\Sales\Model\QuoteRepository $quoteRepository,
        CouponBuilder $couponBuilder
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->couponBuilder = $couponBuilder;
    }

    /**
     * {@inheritdoc}
     *
     * @param int $cartId The cart ID.
     * @param \Magento\Checkout\Service\V1\Data\Cart\Coupon $couponCodeData The coupon code data.
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @throws \Magento\Framework\Exception\CouldNotSaveException The specified coupon could not be added.
     */
    public function set($cartId, \Magento\Checkout\Service\V1\Data\Cart\Coupon $couponCodeData)
    {
        /** @var  \Magento\Sales\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException("Cart $cartId doesn't contain products");
        }
        $quote->getShippingAddress()->setCollectShippingRates(true);
        $couponCode = trim($couponCodeData->getCouponCode());

        try {
            $quote->setCouponCode($couponCode);
            $this->quoteRepository->save($quote->collectTotals());
        } catch (\Exception $e) {
            throw new CouldNotSaveException('Could not apply coupon code');
        }
        if ($quote->getCouponCode() != $couponCode) {
            throw new NoSuchEntityException('Coupon code is not valid');
        }
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param int $cartId The cart ID.
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @throws \Magento\Framework\Exception\CouldNotDeleteException The specified coupon could not be deleted.
     */
    public function delete($cartId)
    {
        /** @var  \Magento\Sales\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException("Cart $cartId doesn't contain products");
        }
        $quote->getShippingAddress()->setCollectShippingRates(true);
        try {
            $quote->setCouponCode('');
            $this->quoteRepository->save($quote->collectTotals());
        } catch (\Exception $e) {
            throw new CouldNotDeleteException('Could not delete coupon code');
        }
        if ($quote->getCouponCode() != '') {
            throw new CouldNotDeleteException('Could not delete coupon code');
        }
        return true;
    }
}
