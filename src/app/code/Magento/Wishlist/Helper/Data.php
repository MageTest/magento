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
namespace Magento\Wishlist\Helper;

use Magento\Wishlist\Controller\WishlistProviderInterface;

/**
 * Wishlist Data Helper
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Config key 'Display Wishlist Summary'
     */
    const XML_PATH_WISHLIST_LINK_USE_QTY = 'wishlist/wishlist_link/use_qty';

    /**
     * Config key 'Display Out of Stock Products'
     */
    const XML_PATH_CATALOGINVENTORY_SHOW_OUT_OF_STOCK = 'cataloginventory/options/show_out_of_stock';

    /**
     * Currently logged in customer
     *
     * @var \Magento\Customer\Service\V1\Data\Customer
     */
    protected $_currentCustomer;

    /**
     * Customer Wishlist instance
     *
     * @var \Magento\Wishlist\Model\Wishlist
     */
    protected $_wishlist;

    /**
     * Wishlist Product Items Collection
     *
     * @var \Magento\Wishlist\Model\Resource\Item\Collection
     */
    protected $_productCollection;

    /**
     * Wishlist Items Collection
     *
     * @var \Magento\Wishlist\Model\Resource\Item\Collection
     */
    protected $_wishlistItemCollection;

    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Wishlist\Model\WishlistFactory
     */
    protected $_wishlistFactory;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Core\Helper\PostData
     */
    protected $_postDataHelper;

    /**
     * @var \Magento\Customer\Helper\View
     */
    protected $_customerViewHelper;

    /**
     * @var \Magento\Wishlist\Controller\WishlistProviderInterface
     */
    protected $wishlistProvider;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Wishlist\Model\WishlistFactory $wishlistFactory
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Core\Helper\PostData $postDataHelper
     * @param \Magento\Customer\Helper\View $customerViewHelper
     * @param WishlistProviderInterface $wishlistProvider
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Wishlist\Model\WishlistFactory $wishlistFactory,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Core\Helper\PostData $postDataHelper,
        \Magento\Customer\Helper\View $customerViewHelper,
        WishlistProviderInterface $wishlistProvider
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_coreData = $coreData;
        $this->_scopeConfig = $scopeConfig;
        $this->_customerSession = $customerSession;
        $this->_wishlistFactory = $wishlistFactory;
        $this->_storeManager = $storeManager;
        $this->_postDataHelper = $postDataHelper;
        $this->_customerViewHelper = $customerViewHelper;
        $this->wishlistProvider = $wishlistProvider;
        parent::__construct($context);
    }

    /**
     * Retrieve customer login status
     *
     * @return bool
     */
    protected function _isCustomerLogIn()
    {
        return $this->_customerSession->isLoggedIn();
    }

    /**
     * Retrieve logged in customer
     *
     * @return \Magento\Customer\Service\V1\Data\Customer
     */
    protected function _getCurrentCustomer()
    {
        return $this->getCustomer();
    }

    /**
     * Set current customer
     *
     * @param \Magento\Customer\Service\V1\Data\Customer $customer
     * @return void
     */
    public function setCustomer(\Magento\Customer\Service\V1\Data\Customer $customer)
    {
        $this->_currentCustomer = $customer;
    }

    /**
     * Retrieve current customer
     *
     * @return \Magento\Customer\Service\V1\Data\Customer|null
     */
    public function getCustomer()
    {
        if (!$this->_currentCustomer && $this->_customerSession->isLoggedIn()) {
            $this->_currentCustomer = $this->_customerSession->getCustomerDataObject();
        }
        return $this->_currentCustomer;
    }

    /**
     * Retrieve wishlist by logged in customer
     *
     * @return \Magento\Wishlist\Model\Wishlist
     */
    public function getWishlist()
    {
        if (is_null($this->_wishlist)) {
            if ($this->_coreRegistry->registry('shared_wishlist')) {
                $this->_wishlist = $this->_coreRegistry->registry('shared_wishlist');
            } else {
                $this->_wishlist = $this->wishlistProvider->getWishlist();
            }
        }
        return $this->_wishlist;
    }

    /**
     * Retrieve wishlist item count (include config settings)
     * Used in top link menu only
     *
     * @return int
     */
    public function getItemCount()
    {
        $storedDisplayType = $this->_customerSession->getWishlistDisplayType();
        $currentDisplayType = $this->_scopeConfig->getValue(
            self::XML_PATH_WISHLIST_LINK_USE_QTY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $storedDisplayOutOfStockProducts = $this->_customerSession->getDisplayOutOfStockProducts();
        $currentDisplayOutOfStockProducts = $this->_scopeConfig->getValue(
            self::XML_PATH_CATALOGINVENTORY_SHOW_OUT_OF_STOCK,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!$this->_customerSession->hasWishlistItemCount() ||
            $currentDisplayType != $storedDisplayType ||
            $this->_customerSession->hasDisplayOutOfStockProducts() ||
            $currentDisplayOutOfStockProducts != $storedDisplayOutOfStockProducts
        ) {
            $this->calculate();
        }

        return $this->_customerSession->getWishlistItemCount();
    }

    /**
     * Create wishlist item collection
     *
     * @return \Magento\Wishlist\Model\Resource\Item\Collection
     */
    protected function _createWishlistItemCollection()
    {
        return $this->getWishlist()->getItemCollection();
    }

    /**
     * Retrieve wishlist items collection
     *
     * @return \Magento\Wishlist\Model\Resource\Item\Collection
     */
    public function getWishlistItemCollection()
    {
        if (is_null($this->_wishlistItemCollection)) {
            $this->_wishlistItemCollection = $this->_createWishlistItemCollection();
        }
        return $this->_wishlistItemCollection;
    }

    /**
     * Retrieve Item Store for URL
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Wishlist\Model\Item $item
     * @return \Magento\Store\Model\Store
     */
    protected function _getUrlStore($item)
    {
        $storeId = null;
        $product = null;
        if ($item instanceof \Magento\Wishlist\Model\Item) {
            $product = $item->getProduct();
        } elseif ($item instanceof \Magento\Catalog\Model\Product) {
            $product = $item;
        }
        if ($product) {
            if ($product->isVisibleInSiteVisibility()) {
                $storeId = $product->getStoreId();
            } else {
                if ($product->hasUrlDataObject()) {
                    $storeId = $product->getUrlDataObject()->getStoreId();
                }
            }
        }
        return $this->_storeManager->getStore($storeId);
    }

    /**
     * Retrieve params for removing item from wishlist
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Wishlist\Model\Item $item
     * @return string
     */
    public function getRemoveParams($item)
    {
        $url = $this->_getUrl('wishlist/index/remove');
        $params = array('item' => $item->getWishlistItemId());
        return $this->_postDataHelper->getPostData($url, $params);
    }

    /**
     * Retrieve URL for configuring item from wishlist
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Wishlist\Model\Item $item
     * @return string
     */
    public function getConfigureUrl($item)
    {
        return $this->_getUrl('wishlist/index/configure', array('id' => $item->getWishlistItemId()));
    }

    /**
     * Retrieve params for adding product to wishlist
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Wishlist\Model\Item $item
     * @param array $params
     * @return string
     */
    public function getAddParams($item, array $params = array())
    {
        $productId = null;
        if ($item instanceof \Magento\Catalog\Model\Product) {
            $productId = $item->getEntityId();
        }
        if ($item instanceof \Magento\Wishlist\Model\Item) {
            $productId = $item->getProductId();
        }

        $url = $this->_getUrlStore($item)->getUrl('wishlist/index/add');
        if ($productId) {
            $params['product'] = $productId;
        }

        return $this->_postDataHelper->getPostData($url, $params);
    }

    /**
     * Retrieve params for adding product to wishlist
     *
     * @param int $itemId
     *
     * @return string
     */
    public function getMoveFromCartParams($itemId)
    {
        $url = $this->_getUrl('wishlist/index/fromcart');
        $params = array('item' => $itemId);
        return $this->_postDataHelper->getPostData($url, $params);
    }

    /**
     * Retrieve params for updating product in wishlist
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Wishlist\Model\Item $item
     *
     * @return  string|false
     */
    public function getUpdateParams($item)
    {
        $itemId = null;
        if ($item instanceof \Magento\Catalog\Model\Product) {
            $itemId = $item->getWishlistItemId();
            $productId = $item->getId();
        }
        if ($item instanceof \Magento\Wishlist\Model\Item) {
            $itemId = $item->getId();
            $productId = $item->getProduct()->getId();
        }

        $url = $this->_getUrl('wishlist/index/updateItemOptions');
        if ($itemId) {
            $params = array('id' => $itemId, 'product' => $productId, 'qty' => $item->getQty());
            return $this->_postDataHelper->getPostData($url, $params);
        }

        return false;
    }

    /**
     * Retrieve URL for adding item to shopping cart
     *
     * @param string|\Magento\Catalog\Model\Product|\Magento\Wishlist\Model\Item $item
     * @return  string
     */
    public function getAddToCartUrl($item)
    {
        return $this->_getUrlStore($item)->getUrl('wishlist/index/cart', $this->_getCartUrlParameters($item));
    }

    /**
     * Retrieve URL for adding item to shopping cart from shared wishlist
     *
     * @param string|\Magento\Catalog\Model\Product|\Magento\Wishlist\Model\Item $item
     * @return  string
     */
    public function getSharedAddToCartUrl($item)
    {
        return $this->_getUrlStore($item)->getUrl('wishlist/shared/cart', $this->_getCartUrlParameters($item));
    }

    /**
     * @param string|\Magento\Catalog\Model\Product|\Magento\Wishlist\Model\Item $item
     * @return array
     */
    protected function _getCartUrlParameters($item)
    {
        $continueUrl = $this->_coreData->urlEncode(
            $this->_getUrl('*/*/*', array('_current' => true, '_use_rewrite' => true, '_scope_to_url' => true))
        );

        return array(
            'item' => is_string($item) ? $item : $item->getWishlistItemId(),
            \Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED => $continueUrl
        );
    }

    /**
     * Retrieve customer wishlist url
     *
     * @param int $wishlistId
     * @return string
     */
    public function getListUrl($wishlistId = null)
    {
        $params = array();
        if ($wishlistId) {
            $params['wishlist_id'] = $wishlistId;
        }
        return $this->_getUrl('wishlist', $params);
    }

    /**
     * Check is allow wishlist module
     *
     * @return bool
     */
    public function isAllow()
    {
        if ($this->isModuleOutputEnabled() && $this->_scopeConfig->getValue(
            'wishlist/general/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )
        ) {
            return true;
        }
        return false;
    }

    /**
     * Check is allow wishlist action in shopping cart
     *
     * @return bool
     */
    public function isAllowInCart()
    {
        return $this->isAllow() && $this->getCustomer();
    }

    /**
     * Retrieve customer name
     *
     * @return string|void
     */
    public function getCustomerName()
    {
        return $this->getCustomer()
            ? $this->_customerViewHelper->getCustomerName($this->getCustomer())
            : null;
    }

    /**
     * Retrieve RSS URL
     *
     * @param int|string|null $wishlistId
     * @return string
     */
    public function getRssUrl($wishlistId = null)
    {
        $customer = $this->_getCurrentCustomer();
        if ($customer) {
            $key = $customer->getId() . ',' . $customer->getEmail();
            $params = array('data' => $this->_coreData->urlEncode($key), '_secure' => false);
        }
        if ($wishlistId) {
            $params['wishlist_id'] = $wishlistId;
        }
        return $this->_getUrl('wishlist/index/rss', $params);
    }

    /**
     * Retrieve default empty comment message
     *
     * @return string
     */
    public function defaultCommentString()
    {
        return __('Comment');
    }

    /**
     * Retrieve default empty comment message
     *
     * @return string
     */
    public function getDefaultWishlistName()
    {
        return __('Wish List');
    }

    /**
     * Calculate count of wishlist items and put value to customer session.
     * Method called after wishlist modifications and trigger 'wishlist_items_renewed' event.
     * Depends from configuration.
     *
     * @return $this
     */
    public function calculate()
    {
        $count = 0;
        if ($this->getCustomer()) {
            $collection = $this->getWishlistItemCollection()->setInStockFilter(true);
            if ($this->_scopeConfig->getValue(
                self::XML_PATH_WISHLIST_LINK_USE_QTY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
            ) {
                $count = $collection->getItemsQty();
            } else {
                $count = $collection->getSize();
            }
            $this->_customerSession->setWishlistDisplayType(
                $this->_scopeConfig->getValue(
                    self::XML_PATH_WISHLIST_LINK_USE_QTY,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            );
            $this->_customerSession->setDisplayOutOfStockProducts(
                $this->_scopeConfig->getValue(
                    self::XML_PATH_CATALOGINVENTORY_SHOW_OUT_OF_STOCK,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            );
        }
        $this->_customerSession->setWishlistItemCount($count);
        $this->_eventManager->dispatch('wishlist_items_renewed');
        return $this;
    }

    /**
     * Should display item quantities in my wishlist link
     *
     * @return bool
     */
    public function isDisplayQty()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_WISHLIST_LINK_USE_QTY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
