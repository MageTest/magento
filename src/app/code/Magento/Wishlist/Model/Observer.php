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
 * Shopping cart operation observer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Wishlist\Model;

class Observer
{
    /**
     * Wishlist data
     *
     * @var \Magento\Wishlist\Helper\Data
     */
    protected $_wishlistData = null;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var WishlistFactory
     */
    protected $_wishlistFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @param \Magento\Wishlist\Helper\Data $wishlistData
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param WishlistFactory $wishlistFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Wishlist\Helper\Data $wishlistData,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        WishlistFactory $wishlistFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_wishlistData = $wishlistData;
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->_wishlistFactory = $wishlistFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * Get customer wishlist model instance
     *
     * @param   int $customerId
     * @return  Wishlist|false
     */
    protected function _getWishlist($customerId)
    {
        if (!$customerId) {
            return false;
        }
        return $this->_wishlistFactory->create()->loadByCustomerId($customerId, true);
    }

    /**
     * Check move quote item to wishlist request
     *
     * @param   \Magento\Framework\Event\Observer $observer
     * @return  $this
     */
    public function processCartUpdateBefore($observer)
    {
        $cart = $observer->getEvent()->getCart();
        $data = $observer->getEvent()->getInfo()->toArray();
        $productIds = array();

        $wishlist = $this->_getWishlist($cart->getQuote()->getCustomerId());
        if (!$wishlist) {
            return $this;
        }

        /**
         * Collect product ids marked for move to wishlist
         */
        foreach ($data as $itemId => $itemInfo) {
            if (!empty($itemInfo['wishlist'])) {
                if ($item = $cart->getQuote()->getItemById($itemId)) {
                    $productId = $item->getProductId();
                    $buyRequest = $item->getBuyRequest();

                    if (isset($itemInfo['qty']) && is_numeric($itemInfo['qty'])) {
                        $buyRequest->setQty($itemInfo['qty']);
                    }
                    $wishlist->addNewItem($productId, $buyRequest);

                    $productIds[] = $productId;
                    $cart->getQuote()->removeItem($itemId);
                }
            }
        }

        if (!empty($productIds)) {
            $wishlist->save();
            $this->_wishlistData->calculate();
        }
        return $this;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function processAddToCart($observer)
    {
        $request = $observer->getEvent()->getRequest();
        $sharedWishlist = $this->_checkoutSession->getSharedWishlist();
        $messages = $this->_checkoutSession->getWishlistPendingMessages();
        $urls = $this->_checkoutSession->getWishlistPendingUrls();
        $wishlistIds = $this->_checkoutSession->getWishlistIds();
        $singleWishlistId = $this->_checkoutSession->getSingleWishlistId();

        if ($singleWishlistId) {
            $wishlistIds = array($singleWishlistId);
        }

        if (count($wishlistIds) && $request->getParam('wishlist_next')) {
            $wishlistId = array_shift($wishlistIds);

            if ($this->_customerSession->isLoggedIn()) {
                $wishlist = $this->_wishlistFactory->create()
                    ->loadByCustomerId($this->_customerSession->getCustomerId(), true);
            } elseif ($sharedWishlist) {
                $wishlist = $this->_wishlistFactory->create()->loadByCode($sharedWishlist);
            } else {
                return;
            }

            $wishlists = $wishlist->getItemCollection()->load();
            foreach ($wishlists as $wishlistItem) {
                if ($wishlistItem->getId() == $wishlistId) {
                    $wishlistItem->delete();
                }
            }
            $this->_checkoutSession->setWishlistIds($wishlistIds);
            $this->_checkoutSession->setSingleWishlistId(null);
        }

        if ($request->getParam('wishlist_next') && count($urls)) {
            $url = array_shift($urls);
            $message = array_shift($messages);

            $this->_checkoutSession->setWishlistPendingUrls($urls);
            $this->_checkoutSession->setWishlistPendingMessages($messages);

            $this->messageManager->addError($message);

            $observer->getEvent()->getResponse()->setRedirect($url);
            $this->_checkoutSession->setNoCartRedirect(true);
        }
    }

    /**
     * Customer login processing
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function customerLogin(\Magento\Framework\Event\Observer $observer)
    {
        $this->_wishlistData->calculate();

        return $this;
    }

    /**
     * Customer logout processing
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function customerLogout(\Magento\Framework\Event\Observer $observer)
    {
        $this->_customerSession->setWishlistItemCount(0);

        return $this;
    }
}
