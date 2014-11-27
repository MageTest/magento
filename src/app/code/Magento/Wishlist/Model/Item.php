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
namespace Magento\Wishlist\Model;

use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Wishlist\Model\Item\Option;
use Magento\Wishlist\Model\Item\OptionFactory;
use Magento\Wishlist\Model\Resource\Item\Option\CollectionFactory;

/**
 * Wishlist item model
 *
 * @method \Magento\Wishlist\Model\Resource\Item getResource()
 * @method int getWishlistId()
 * @method \Magento\Wishlist\Model\Item setWishlistId(int $value)
 * @method int getProductId()
 * @method \Magento\Wishlist\Model\Item setProductId(int $value)
 * @method int getStoreId()
 * @method \Magento\Wishlist\Model\Item setStoreId(int $value)
 * @method string getAddedAt()
 * @method \Magento\Wishlist\Model\Item setAddedAt(string $value)
 * @method string getDescription()
 * @method \Magento\Wishlist\Model\Item setDescription(string $value)
 */
class Item extends AbstractModel implements ItemInterface
{
    const EXCEPTION_CODE_NOT_SALABLE = 901;

    const EXCEPTION_CODE_HAS_REQUIRED_OPTIONS = 902;

    /**
     * Custom path to download attached file
     * @var string
     */
    protected $_customOptionDownloadUrl = 'wishlist/index/downloadCustomOption';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'wishlist_item';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getItem() in this case
     *
     * @var string
     */
    protected $_eventObject = 'item';

    /**
     * Item options array
     *
     * @var Option[]
     */
    protected $_options = array();

    /**
     * Item options by code cache
     *
     * @var array
     */
    protected $_optionsByCode = array();

    /**
     * Not Represent options
     *
     * @var string[]
     */
    protected $_notRepresentOptions = array('info_buyRequest');

    /**
     * Flag stating that options were successfully saved
     *
     * @var bool|null
     */
    protected $_flagOptionsSaved = null;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Catalog\Model\Resource\Url
     */
    protected $_catalogUrl;

    /**
     * @var OptionFactory
     */
    protected $_wishlistOptFactory;

    /**
     * @var CollectionFactory
     */
    protected $_wishlOptionCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface
     */
    protected $productTypeConfig;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\Resource\Url $catalogUrl
     * @param OptionFactory $wishlistOptFactory
     * @param CollectionFactory $wishlOptionCollectionFactory
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Resource\Url $catalogUrl,
        OptionFactory $wishlistOptFactory,
        CollectionFactory $wishlOptionCollectionFactory,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->productTypeConfig = $productTypeConfig;
        $this->_storeManager = $storeManager;
        $this->_date = $date;
        $this->_productFactory = $productFactory;
        $this->_catalogUrl = $catalogUrl;
        $this->_wishlistOptFactory = $wishlistOptFactory;
        $this->_wishlOptionCollectionFactory = $wishlOptionCollectionFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Wishlist\Model\Resource\Item');
    }

    /**
     * Set quantity. If quantity is less than 0 - set it to 1
     *
     * @param int $qty
     * @return $this
     */
    public function setQty($qty)
    {
        $this->setData('qty', $qty >= 0 ? $qty : 1);
        return $this;
    }

    /**
     * Retrieve resource instance wrapper
     *
     * @return \Magento\Wishlist\Model\Resource\Item
     */
    protected function _getResource()
    {
        return parent::_getResource();
    }

    /**
     * Check if two options array are identical
     *
     * @param array $options1
     * @param array $options2
     * @return bool
     */
    protected function _compareOptions($options1, $options2)
    {
        $skipOptions = array('id', 'qty', 'return_url');
        foreach ($options1 as $code => $value) {
            if (in_array($code, $skipOptions)) {
                continue;
            }
            if (!isset($options2[$code]) || $options2[$code] != $value) {
                return false;
            }
        }
        return true;
    }

    /**
     * Register option code
     *
     * @param   Option $option
     * @return  $this
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _addOptionCode($option)
    {
        if (!isset($this->_optionsByCode[$option->getCode()])) {
            $this->_optionsByCode[$option->getCode()] = $option;
        } else {
            throw new \Magento\Framework\Model\Exception(__('An item option with code %1 already exists.', $option->getCode()));
        }
        return $this;
    }

    /**
     * Checks that item model has data changes.
     * Call save item options if model isn't need to save in DB
     *
     * @return boolean
     */
    protected function _hasModelChanged()
    {
        if (!$this->hasDataChanges()) {
            return false;
        }

        return $this->_getResource()->hasDataChanged($this);
    }

    /**
     * Save item options
     *
     * @return $this
     */
    protected function _saveItemOptions()
    {
        foreach ($this->_options as $index => $option) {
            if ($option->isDeleted()) {
                $option->delete();
                unset($this->_options[$index]);
                unset($this->_optionsByCode[$option->getCode()]);
            } else {
                $option->save();
            }
        }

        $this->_flagOptionsSaved = true;
        // Report to watchers that options were saved

        return $this;
    }

    /**
     * Save model plus its options
     * Ensures saving options in case when resource model was not changed
     *
     * @return void
     */
    public function save()
    {
        $hasDataChanges = $this->hasDataChanges();
        $this->_flagOptionsSaved = false;

        parent::save();

        if ($hasDataChanges && !$this->_flagOptionsSaved) {
            $this->_saveItemOptions();
        }
    }

    /**
     * Save item options after item saved
     *
     * @return $this
     */
    protected function _afterSave()
    {
        $this->_saveItemOptions();
        return parent::_afterSave();
    }

    /**
     * Validate wish list item data
     *
     * @return bool
     * @throws \Magento\Framework\Model\Exception
     */
    public function validate()
    {
        if (!$this->getWishlistId()) {
            throw new \Magento\Framework\Model\Exception(__('We can\'t specify a wish list.'));
        }
        if (!$this->getProductId()) {
            throw new \Magento\Framework\Model\Exception(__('Cannot specify product.'));
        }

        return true;
    }

    /**
     * Check required data
     *
     * @return $this
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();

        // validate required item data
        $this->validate();

        // set current store id if it is not defined
        if (is_null($this->getStoreId())) {
            $this->setStoreId($this->_storeManager->getStore()->getId());
        }

        // set current date if added at data is not defined
        if (is_null($this->getAddedAt())) {
            $this->setAddedAt($this->_date->gmtDate());
        }

        return $this;
    }

    /**
     * Load item by product, wishlist and shared stores
     *
     * @param int $wishlistId
     * @param int $productId
     * @param array $sharedStores
     * @return $this
     */
    public function loadByProductWishlist($wishlistId, $productId, $sharedStores)
    {
        $this->_getResource()->loadByProductWishlist($this, $wishlistId, $productId, $sharedStores);
        $this->_afterLoad();
        $this->setOrigData();

        return $this;
    }

    /**
     * Retrieve item product instance
     *
     * @throws \Magento\Framework\Model\Exception
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        $product = $this->_getData('product');
        if (is_null($product)) {
            if (!$this->getProductId()) {
                throw new \Magento\Framework\Model\Exception(__('Cannot specify product.'));
            }

            $product = $this->_productFactory->create()->setStoreId($this->getStoreId())->load($this->getProductId());

            $this->setData('product', $product);
        }

        /**
         * Reset product final price because it related to custom options
         */
        $product->setFinalPrice(null);
        $product->setCustomOptions($this->_optionsByCode);
        return $product;
    }

    /**
     * Add or Move item product to shopping cart
     *
     * Return true if product was successful added or exception with code
     * Return false for disabled or unvisible products
     *
     * @param \Magento\Checkout\Model\Cart $cart
     * @param bool $delete  delete the item after successful add to cart
     * @return bool
     * @throws \Magento\Framework\Model\Exception
     */
    public function addToCart(\Magento\Checkout\Model\Cart $cart, $delete = false)
    {
        $product = $this->getProduct();

        $storeId = $this->getStoreId();

        if ($product->getStatus() != \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED) {
            return false;
        }

        if (!$product->isVisibleInSiteVisibility()) {
            if ($product->getStoreId() == $storeId) {
                return false;
            }
            $urlData = $this->_catalogUrl->getRewriteByProductStore(array($product->getId() => $storeId));
            if (!isset($urlData[$product->getId()])) {
                return false;
            }
            $product->setUrlDataObject(new \Magento\Framework\Object($urlData));
            $visibility = $product->getUrlDataObject()->getVisibility();
            if (!in_array($visibility, $product->getVisibleInSiteVisibilities())) {
                return false;
            }
        }

        if (!$product->isSalable()) {
            throw new \Magento\Framework\Model\Exception(null, self::EXCEPTION_CODE_NOT_SALABLE);
        }

        $buyRequest = $this->getBuyRequest();

        $cart->addProduct($product, $buyRequest);
        if (!$product->isVisibleInSiteVisibility()) {
            $cart->getQuote()->getItemByProduct($product)->setStoreId($storeId);
        }

        if ($delete) {
            $this->delete();
        }

        return true;
    }

    /**
     * Retrieve Product View Page URL
     *
     * If product has required options add special key to URL
     *
     * @return string
     */
    public function getProductUrl()
    {
        $product = $this->getProduct();
        $query = array();

        if ($product->getTypeInstance()->hasRequiredOptions($product)) {
            $query['options'] = 'cart';
        }

        return $product->getUrlModel()->getUrl($product, array('_query' => $query));
    }

    /**
     * Returns formatted buy request - object, holding request received from
     * product view page with keys and options for configured product
     *
     * @return \Magento\Framework\Object
     */
    public function getBuyRequest()
    {
        $option = $this->getOptionByCode('info_buyRequest');
        $initialData = $option ? unserialize($option->getValue()) : null;

        if ($initialData instanceof \Magento\Framework\Object) {
            $initialData = $initialData->getData();
        }

        $buyRequest = new \Magento\Framework\Object($initialData);
        $buyRequest->setOriginalQty($buyRequest->getQty())->setQty($this->getQty() * 1);
        return $buyRequest;
    }

    /**
     * Merge data to item info_buyRequest option
     *
     * @param array|\Magento\Framework\Object $buyRequest
     * @return $this
     */
    public function mergeBuyRequest($buyRequest)
    {
        if ($buyRequest instanceof \Magento\Framework\Object) {
            $buyRequest = $buyRequest->getData();
        }

        if (empty($buyRequest) || !is_array($buyRequest)) {
            return $this;
        }

        $oldBuyRequest = $this->getBuyRequest()->getData();
        $sBuyRequest = serialize($buyRequest + $oldBuyRequest);

        $option = $this->getOptionByCode('info_buyRequest');
        if ($option) {
            $option->setValue($sBuyRequest);
        } else {
            $this->addOption(array('code' => 'info_buyRequest', 'value' => $sBuyRequest));
        }

        return $this;
    }

    /**
     * Set buy request - object, holding request received from
     * product view page with keys and options for configured product
     *
     * @param \Magento\Framework\Object $buyRequest
     * @return $this
     */
    public function setBuyRequest($buyRequest)
    {
        $buyRequest->setId($this->getId());

        $_buyRequest = serialize($buyRequest->getData());
        $this->setData('buy_request', $_buyRequest);
        return $this;
    }

    /**
     * Check product representation in item
     *
     * @param   \Magento\Catalog\Model\Product $product
     * @param   \Magento\Framework\Object $buyRequest
     * @return  bool
     */
    public function isRepresent($product, $buyRequest)
    {
        if ($this->getProductId() != $product->getId()) {
            return false;
        }

        $selfOptions = $this->getBuyRequest()->getData();

        if (empty($buyRequest) && !empty($selfOptions)) {
            return false;
        }
        if (empty($selfOptions) && !empty($buyRequest)) {
            if (!$product->isComposite()) {
                return true;
            } else {
                return false;
            }
        }

        $requestArray = $buyRequest->getData();

        if (!$this->_compareOptions($requestArray, $selfOptions)) {
            return false;
        }
        if (!$this->_compareOptions($selfOptions, $requestArray)) {
            return false;
        }
        return true;
    }

    /**
     * Check product representation in item
     *
     * @param   \Magento\Catalog\Model\Product $product
     * @return  bool
     */
    public function representProduct($product)
    {
        $itemProduct = $this->getProduct();
        if ($itemProduct->getId() != $product->getId()) {
            return false;
        }

        $itemOptions = $this->getOptionsByCode();
        $productOptions = $product->getCustomOptions();

        if (!$this->compareOptions($itemOptions, $productOptions)) {
            return false;
        }
        if (!$this->compareOptions($productOptions, $itemOptions)) {
            return false;
        }
        return true;
    }

    /**
     * Check if two options array are identical
     * First options array is prerogative
     * Second options array checked against first one
     *
     * @param array $options1
     * @param array $options2
     * @return bool
     */
    public function compareOptions($options1, $options2)
    {
        foreach ($options1 as $option) {
            $code = $option->getCode();
            if (in_array($code, $this->_notRepresentOptions)) {
                continue;
            }
            if (!isset($options2[$code]) || $options2[$code]->getValue() != $option->getValue()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Initialize item options
     *
     * @param   array $options
     * @return  $this
     */
    public function setOptions($options)
    {
        foreach ($options as $option) {
            $this->addOption($option);
        }
        return $this;
    }

    /**
     * Get all item options
     *
     * @return Option[]
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Get all item options as array with codes in array key
     *
     * @return array
     */
    public function getOptionsByCode()
    {
        return $this->_optionsByCode;
    }

    /**
     * Add option to item
     *
     * @param   Option|\Magento\Framework\Object|array $option
     * @return  $this
     * @throws \Magento\Framework\Model\Exception
     */
    public function addOption($option)
    {
        if (is_array($option)) {
            $option = $this->_wishlistOptFactory->create()->setData($option)->setItem($this);
        } elseif ($option instanceof Option) {
            $option->setItem($this);
        } elseif ($option instanceof \Magento\Framework\Object) {
            $option = $this->_wishlistOptFactory->create()->setData($option->getData())
               ->setProduct($option->getProduct())
               ->setItem($this);
        } else {
            throw new \Magento\Framework\Model\Exception(__('Invalid item option format.'));
        }

        $exOption = $this->getOptionByCode($option->getCode());
        if ($exOption) {
            $exOption->addData($option->getData());
        } else {
            $this->_addOptionCode($option);
            $this->_options[] = $option;
        }
        return $this;
    }

    /**
     *Remove option from item options
     *
     * @param string $code
     * @return $this
     */
    public function removeOption($code)
    {
        $option = $this->getOptionByCode($code);
        if ($option) {
            $option->isDeleted(true);
        }
        return $this;
    }

    /**
     * Get item option by code
     *
     * @param   string $code
     * @return  Option|null
     */
    public function getOptionByCode($code)
    {
        if (isset($this->_optionsByCode[$code]) && !$this->_optionsByCode[$code]->isDeleted()) {
            return $this->_optionsByCode[$code];
        }
        return null;
    }

    /**
     * Returns whether Qty field is valid for this item
     *
     * @return bool
     */
    public function canHaveQty()
    {
        $product = $this->getProduct();
        return !$this->productTypeConfig->isProductSet($product->getTypeId());
    }

    /**
     * Get current custom option download url
     *
     * @return string
     */
    public function getCustomDownloadUrl()
    {
        return $this->_customOptionDownloadUrl;
    }

    /**
     * Sets custom option download url
     *
     * @param string $url
     * @return void
     */
    public function setCustomDownloadUrl($url)
    {
        $this->_customOptionDownloadUrl = $url;
    }

    /**
     * Returns special download params (if needed) for custom option with type = 'file'.
     * Needed to implement \Magento\Catalog\Model\Product\Configuration\Item\Interface.
     *
     * We have to customize only controller url, so return it.
     *
     * @return null|\Magento\Framework\Object
     */
    public function getFileDownloadParams()
    {
        $params = new \Magento\Framework\Object();
        $params->setUrl($this->_customOptionDownloadUrl);
        return $params;
    }

    /**
     * Loads item together with its options (default load() method doesn't load options).
     * If we need to load only some of options, then option code or array of option codes
     * can be provided in $optionsFilter.
     *
     * @param int $id
     * @param null|string|array $optionsFilter
     *
     * @return $this
     */
    public function loadWithOptions($id, $optionsFilter = null)
    {
        $this->load($id);
        if (!$this->getId()) {
            return $this;
        }

        $options = $this->_wishlOptionCollectionFactory->create()->addItemFilter($this);
        if ($optionsFilter) {
            $options->addFieldToFilter('code', $optionsFilter);
        }

        $this->setOptions($options->getOptionsByItem($this));
        return $this;
    }
}
