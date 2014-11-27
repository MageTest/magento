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
use \Magento\Checkout\Service\V1\Data\Cart\Coupon as Coupon;

/**
 * Coupon read service object.
 */
class ReadService implements ReadServiceInterface
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
     * Constructs a coupon read service object.
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
     * {@inheritDoc}
     *
     * @param int $cartId The cart ID.
     * @return \Magento\Checkout\Service\V1\Data\Cart\Coupon Coupon object.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     */
    public function get($cartId)
    {
        /** @var  \Magento\Sales\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        $data = [Coupon::COUPON_CODE => $quote->getCouponCode()];
        $output = $this->couponBuilder->populateWithArray($data)->create();
        return $output;
    }
}
