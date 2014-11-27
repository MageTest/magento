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
namespace Magento\Catalog\Helper;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Tax\Service\V1\Data\QuoteDetailsBuilder;
use Magento\Tax\Service\V1\Data\QuoteDetails\ItemBuilder as QuoteDetailsItemBuilder;
use Magento\Tax\Service\V1\Data\TaxClassKey;
use Magento\Customer\Model\Address\Converter as AddressConverter;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Tax\Model\Config;

/**
 * Catalog data helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const PRICE_SCOPE_GLOBAL = 0;

    const PRICE_SCOPE_WEBSITE = 1;

    const XML_PATH_PRICE_SCOPE = 'catalog/price/scope';

    const CONFIG_USE_STATIC_URLS = 'cms/wysiwyg/use_static_urls_in_catalog';

    const CONFIG_PARSE_URL_DIRECTIVES = 'catalog/frontend/parse_url_directives';

    const XML_PATH_DISPLAY_PRODUCT_COUNT = 'catalog/layered_navigation/display_product_count';

    /**
     * Cache context
     */
    const CONTEXT_CATALOG_SORT_DIRECTION = 'catalog_sort_direction';

    const CONTEXT_CATALOG_SORT_ORDER = 'catalog_sort_order';

    const CONTEXT_CATALOG_DISPLAY_MODE = 'catalog_mode';

    const CONTEXT_CATALOG_LIMIT = 'catalog_limit';

    /**
     * Breadcrumb Path cache
     *
     * @var string
     */
    protected $_categoryPath;

    /**
     * Currently selected store ID if applicable
     *
     * @var int
     */
    protected $_storeId;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Catalog product
     *
     * @var Product
     */
    protected $_catalogProduct;

    /**
     * Catalog category
     *
     * @var Category
     */
    protected $_catalogCategory;

    /**
     * @var \Magento\Framework\Stdlib\String
     */
    protected $string;

    /**
     * @var string
     */
    protected $_templateFilterModel;

    /**
     * Catalog session
     *
     * @var \Magento\Catalog\Model\Session
     */
    protected $_catalogSession;

    /**
     * Store manager
     *
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Product factory
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * Category factory
     *
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * Template filter factory
     *
     * @var \Magento\Catalog\Model\Template\Filter\Factory
     */
    protected $_templateFilterFactory;

    /**
     * Tax class key builder
     *
     * @var \Magento\Tax\Service\V1\Data\TaxClassKeyBuilder
     */
    protected $_taxClassKeyBuilder;

    /**
     * Tax helper
     *
     * @var \Magento\Tax\Model\Config
     */
    protected $_taxConfig;

    /**
     * Quote details builder
     *
     * @var QuoteDetailsBuilder
     */
    protected $_quoteDetailsBuilder;

    /**
     * Quote details item builder
     *
     * @var QuoteDetailsItemBuilder
     */
    protected $_quoteDetailsItemBuilder;

    /**
     * Address converter
     *
     * @var AddressConverter
     */
    protected $_addressConverter;

    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * Tax calculation service interface
     *
     * @var \Magento\Tax\Service\V1\TaxCalculationServiceInterface
     */
    protected $_taxCalculationService;

    /**
     * Price currency
     *
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param \Magento\Framework\Stdlib\String $string
     * @param Category $catalogCategory
     * @param Product $catalogProduct
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\Template\Filter\Factory $templateFilterFactory
     * @param string $templateFilterModel
     * @param TaxClassKeyBuilder $taxClassKeyBuilder
     * @param Config $taxConfig
     * @param QuoteDetailsBuilder $quoteDetailsBuilder
     * @param QuoteDetailsItemBuilder $quoteDetailsItemBuilder
     * @param TaxCalculationServiceInterface $taxCalculationService
     * @param CustomerSession $customerSession
     * @param AddressConverter $addressConverter
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Framework\Stdlib\String $string,
        Category $catalogCategory,
        Product $catalogProduct,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Template\Filter\Factory $templateFilterFactory,
        $templateFilterModel,
        \Magento\Tax\Service\V1\Data\TaxClassKeyBuilder $taxClassKeyBuilder,
        \Magento\Tax\Model\Config $taxConfig,
        QuoteDetailsBuilder $quoteDetailsBuilder,
        QuoteDetailsItemBuilder $quoteDetailsItemBuilder,
        \Magento\Tax\Service\V1\TaxCalculationServiceInterface $taxCalculationService,
        CustomerSession $customerSession,
        AddressConverter $addressConverter,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->_categoryFactory = $categoryFactory;
        $this->_productFactory = $productFactory;
        $this->_storeManager = $storeManager;
        $this->_catalogSession = $catalogSession;
        $this->_templateFilterFactory = $templateFilterFactory;
        $this->string = $string;
        $this->_catalogCategory = $catalogCategory;
        $this->_catalogProduct = $catalogProduct;
        $this->_scopeConfig = $scopeConfig;
        $this->_coreRegistry = $coreRegistry;
        $this->_templateFilterModel = $templateFilterModel;
        $this->_taxClassKeyBuilder = $taxClassKeyBuilder;
        $this->_taxConfig = $taxConfig;
        $this->_quoteDetailsBuilder = $quoteDetailsBuilder;
        $this->_quoteDetailsItemBuilder = $quoteDetailsItemBuilder;
        $this->_taxCalculationService = $taxCalculationService;
        $this->_customerSession = $customerSession;
        $this->_addressConverter = $addressConverter;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context);
    }

    /**
     * Set a specified store ID value
     *
     * @param int $store
     * @return $this
     */
    public function setStoreId($store)
    {
        $this->_storeId = $store;
        return $this;
    }

    /**
     * Return current category path or get it from current category
     * and creating array of categories|product paths for breadcrumbs
     *
     * @return string
     */
    public function getBreadcrumbPath()
    {
        if (!$this->_categoryPath) {

            $path = array();
            $category = $this->getCategory();
            if ($category) {
                $pathInStore = $category->getPathInStore();
                $pathIds = array_reverse(explode(',', $pathInStore));

                $categories = $category->getParentCategories();

                // add category path breadcrumb
                foreach ($pathIds as $categoryId) {
                    if (isset($categories[$categoryId]) && $categories[$categoryId]->getName()) {
                        $path['category' . $categoryId] = array(
                            'label' => $categories[$categoryId]->getName(),
                            'link' => $this->_isCategoryLink($categoryId) ? $categories[$categoryId]->getUrl() : ''
                        );
                    }
                }
            }

            if ($this->getProduct()) {
                $path['product'] = array('label' => $this->getProduct()->getName());
            }

            $this->_categoryPath = $path;
        }
        return $this->_categoryPath;
    }

    /**
     * Check is category link
     *
     * @param int $categoryId
     * @return bool
     */
    protected function _isCategoryLink($categoryId)
    {
        if ($this->getProduct()) {
            return true;
        }
        if ($categoryId != $this->getCategory()->getId()) {
            return true;
        }
        return false;
    }

    /**
     * Return current category object
     *
     * @return \Magento\Catalog\Model\Category|null
     */
    public function getCategory()
    {
        return $this->_coreRegistry->registry('current_category');
    }

    /**
     * Retrieve current Product object
     *
     * @return \Magento\Catalog\Model\Product|null
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    /**
     * Retrieve Visitor/Customer Last Viewed URL
     *
     * @return string
     */
    public function getLastViewedUrl()
    {
        $productId = $this->_catalogSession->getLastViewedProductId();
        if ($productId) {
            $product = $this->_productFactory->create()->load($productId);
            /* @var $product \Magento\Catalog\Model\Product */
            if ($this->_catalogProduct->canShow($product, 'catalog')) {
                return $product->getProductUrl();
            }
        }
        $categoryId = $this->_catalogSession->getLastViewedCategoryId();
        if ($categoryId) {
            $category = $this->_categoryFactory->create()->load($categoryId);
            /* @var $category \Magento\Catalog\Model\Category */
            if (!$this->_catalogCategory->canShow($category)) {
                return '';
            }
            return $category->getCategoryUrl();
        }
        return '';
    }

    /**
     * Split SKU of an item by dashes and spaces
     * Words will not be broken, unless this length is greater than $length
     *
     * @param string $sku
     * @param int $length
     * @return string[]
     */
    public function splitSku($sku, $length = 30)
    {
        return $this->string->split($sku, $length, true, false, '[\-\s]');
    }

    /**
     * Retrieve attribute hidden fields
     *
     * @return array
     */
    public function getAttributeHiddenFields()
    {
        if ($this->_coreRegistry->registry('attribute_type_hidden_fields')) {
            return $this->_coreRegistry->registry('attribute_type_hidden_fields');
        } else {
            return array();
        }
    }

    /**
     * Retrieve Catalog Price Scope
     *
     * @return int
     */
    public function getPriceScope()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_PRICE_SCOPE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is Global Price
     *
     * @return bool
     */
    public function isPriceGlobal()
    {
        return $this->getPriceScope() == self::PRICE_SCOPE_GLOBAL;
    }

    /**
     * Check if the store is configured to use static URLs for media
     *
     * @return bool
     */
    public function isUsingStaticUrlsAllowed()
    {
        return $this->_scopeConfig->isSetFlag(
            self::CONFIG_USE_STATIC_URLS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeId
        );
    }

    /**
     * Check if the parsing of URL directives is allowed for the catalog
     *
     * @return bool
     */
    public function isUrlDirectivesParsingAllowed()
    {
        return $this->_scopeConfig->isSetFlag(
            self::CONFIG_PARSE_URL_DIRECTIVES,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeId
        );
    }

    /**
     * Retrieve template processor for catalog content
     *
     * @return \Magento\Framework\Filter\Template
     */
    public function getPageTemplateProcessor()
    {
        return $this->_templateFilterFactory->create($this->_templateFilterModel);
    }

    /**
     * Whether to display items count for each filter option
     * @param int $storeId Store view ID
     * @return bool
     */
    public function shouldDisplayProductCountOnLayer($storeId = null)
    {
        return $this->_scopeConfig->isSetFlag(
            self::XML_PATH_DISPLAY_PRODUCT_COUNT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get product price with all tax settings processing
     *
     * @param   \Magento\Catalog\Model\Product $product
     * @param   float $price inputted product price
     * @param   bool $includingTax return price include tax flag
     * @param   null|\Magento\Customer\Model\Address\AbstractAddress $shippingAddress
     * @param   null|\Magento\Customer\Model\Address\AbstractAddress $billingAddress
     * @param   null|int $ctc customer tax class
     * @param   null|string|bool|int|\Magento\Store\Model\Store $store
     * @param   bool $priceIncludesTax flag what price parameter contain tax
     * @param   bool $roundPrice
     * @return  float
     */
    public function getTaxPrice(
        $product,
        $price,
        $includingTax = null,
        $shippingAddress = null,
        $billingAddress = null,
        $ctc = null,
        $store = null,
        $priceIncludesTax = null,
        $roundPrice = true
    ) {
        if (!$price) {
            return $price;
        }

        $store = $this->_storeManager->getStore($store);
        if ($this->_taxConfig->needPriceConversion($store)) {
            if (is_null($priceIncludesTax)) {
                $priceIncludesTax = $this->_taxConfig->priceIncludesTax($store);
            }

            $shippingAddressDataObject = null;
            if ($shippingAddress instanceof \Magento\Customer\Model\Address\AbstractAddress) {
                $shippingAddressDataObject = $this->_addressConverter->createAddressFromModel(
                    $shippingAddress,
                    null,
                    null
                );
            }

            $billingAddressDataObject = null;
            if ($billingAddress instanceof \Magento\Customer\Model\Address\AbstractAddress) {
                $billingAddressDataObject = $this->_addressConverter->createAddressFromModel(
                    $billingAddress,
                    null,
                    null
                );
            }

            $item = $this->_quoteDetailsItemBuilder->setQuantity(1)
                ->setCode($product->getSku())
                ->setShortDescription($product->getShortDescription())
                ->setTaxClassKey(
                    $this->_taxClassKeyBuilder->setType(TaxClassKey::TYPE_ID)
                        ->setValue($product->getTaxClassId())->create()
                )->setTaxIncluded($priceIncludesTax)
                ->setType('product')
                ->setUnitPrice($price)
                ->create();
            $quoteDetails = $this->_quoteDetailsBuilder
                ->setShippingAddress($shippingAddressDataObject)
                ->setBillingAddress($billingAddressDataObject)
                ->setCustomerTaxClassKey(
                    $this->_taxClassKeyBuilder->setType(TaxClassKey::TYPE_ID)
                        ->setValue($ctc)->create()
                )->setItems([$item])
                ->setCustomerId($this->_customerSession->getCustomerId())
                ->create();

            $storeId = null;
            if ($store) {
                $storeId = $store->getId();
            }
            $taxDetails = $this->_taxCalculationService->calculateTax($quoteDetails, $storeId);
            $items = $taxDetails->getItems();
            $taxDetailsItem = array_shift($items);

            if (!is_null($includingTax)) {
                if ($includingTax) {
                    $price = $taxDetailsItem->getPriceInclTax();
                } else {
                    $price = $taxDetailsItem->getPrice();
                }
            } else {
                switch ($this->_taxConfig->getPriceDisplayType($store)) {
                    case Config::DISPLAY_TYPE_EXCLUDING_TAX:
                    case Config::DISPLAY_TYPE_BOTH:
                        $price = $taxDetailsItem->getPrice();
                        break;
                    case Config::DISPLAY_TYPE_INCLUDING_TAX:
                        $price = $taxDetailsItem->getPriceInclTax();
                        break;
                    default:
                        break;
                }
            }
        }

        if ($roundPrice) {
            return $this->priceCurrency->round($price);
        } else {
            return $price;
        }
    }
}
