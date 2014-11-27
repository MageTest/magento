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
namespace Magento\GoogleShopping\Model\Attribute;

use Magento\Catalog\Model\Product;
use Magento\Framework\Gdata\Gshopping\Entry;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Tax\Model\Config;

/**
 * Price attribute model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Price extends \Magento\GoogleShopping\Model\Attribute\DefaultAttribute
{
    /**
     * @var \Magento\Tax\Helper\Data|null
     */
    protected $_taxData = null;

    /**
     * @var \Magento\Catalog\Helper\Data|null
     */
    protected $_catalogData = null;

    /**
     * Config
     *
     * @var \Magento\GoogleShopping\Model\Config
     */
    protected $_config;

    /**
     * Store manager
     *
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Model\Product\CatalogPrice
     */
    protected $catalogPrice;

    /**
     * @var  \Magento\Customer\Service\V1\CustomerGroupService
     */
    protected $_customerGroupService;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\GoogleShopping\Helper\Data $googleShoppingHelper
     * @param \Magento\GoogleShopping\Helper\Product $gsProduct
     * @param \Magento\Catalog\Model\Product\CatalogPrice $catalogPrice
     * @param \Magento\GoogleShopping\Model\Resource\Attribute $resource
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\GoogleShopping\Model\Config $config
     * @param \Magento\Customer\Service\V1\CustomerGroupService $customerGroupService
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\GoogleShopping\Helper\Data $googleShoppingHelper,
        \Magento\GoogleShopping\Helper\Product $gsProduct,
        \Magento\Catalog\Model\Product\CatalogPrice $catalogPrice,
        \Magento\GoogleShopping\Model\Resource\Attribute $resource,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\GoogleShopping\Model\Config $config,
        \Magento\Customer\Service\V1\CustomerGroupService $customerGroupService,
        \Magento\Catalog\Helper\Data $catalogData,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->_storeManager = $storeManager;
        $this->_config = $config;
        $this->_taxData = $taxData;
        $this->_customerGroupService = $customerGroupService;
        $this->catalogPrice = $catalogPrice;
        $this->_catalogData = $catalogData;
        parent::__construct(
            $context,
            $registry,
            $productFactory,
            $googleShoppingHelper,
            $gsProduct,
            $catalogPrice,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Set current attribute to entry (for specified product)
     *
     * @param Product $product
     * @param Entry $entry
     * @return Entry
     */
    public function convertAttribute($product, $entry)
    {
        $product->setWebsiteId($this->_storeManager->getStore($product->getStoreId())->getWebsiteId());
        $defaultCustomerGroup = $this->_customerGroupService->getDefaultGroup($product->getStoreId());
        $product->setCustomerGroupId($defaultCustomerGroup->getId());

        /** @var \Magento\Store\Model\Store $store */
        $store = $this->_storeManager->getStore($product->getStoreId());
        $isSalePriceAllowed = $this->_config->getTargetCountry($product->getStoreId()) == 'US';

        // get tax settings
        $priceDisplayType = $this->_taxData->getPriceDisplayType($product->getStoreId());
        $inclTax = $priceDisplayType == Config::DISPLAY_TYPE_INCLUDING_TAX;

        $finalPrice = $this->_getFinalPrice($product, $store, $inclTax, $isSalePriceAllowed);

        // calculate price attribute value
        $price = $this->_getPrice($product, $store, $priceDisplayType, $inclTax, $isSalePriceAllowed);

        if ($isSalePriceAllowed) {
            // set sale_price and effective dates for it
            if ($price && $price - $finalPrice > .0001) {
                $this->_setAttributePrice($entry, $product, $price);
                $this->_setAttributePrice($entry, $product, $finalPrice, 'sale_price');

                $this->_setEffectiveDate($product, $entry);
            } else {
                $this->_setAttributePrice($entry, $product, $finalPrice);
                $entry->removeContentAttribute('sale_price_effective_date');
                $entry->removeContentAttribute('sale_price');
            }

            // calculate taxes
            $tax = $this->getGroupAttributeTax();
            if (!$inclTax && !is_null($tax)) {
                $tax->convertAttribute($product, $entry);
            }
        } else {
            $this->_setAttributePrice($entry, $product, $price);
        }

        return $entry;
    }

    /**
     * Custom setter for 'price' attribute
     *
     * @param Entry $entry
     * @param Product $product
     * @param mixed $value Fload price value
     * @param string $name Google Content attribute name
     * @return Entry
     */
    protected function _setAttributePrice($entry, $product, $value, $name = 'price')
    {
        $store = $this->_storeManager->getStore($product->getStoreId());
        $price = $this->priceCurrency->convert($value, $store);
        return $this->_setAttribute(
            $entry,
            $name,
            self::ATTRIBUTE_TYPE_FLOAT,
            sprintf('%.2f', $this->priceCurrency->round($price)),
            $store->getDefaultCurrencyCode()
        );
    }

    /**
     * Sets sales price effective date from/to and sets current attribute to entry (for specified product)
     *
     * @param Product $product
     * @param Entry $entry
     * @return void
     */
    private function _setEffectiveDate($product, $entry)
    {
        /** @var SalePriceEffectiveDate $effectiveDate */
        $effectiveDate = $this->getGroupAttributeSalePriceEffectiveDate();
        if (!is_null($effectiveDate)) {
            $effectiveDate->setGroupAttributeSalePriceEffectiveDateFrom(
                $this->getGroupAttributeSalePriceEffectiveDateFrom()
            )->setGroupAttributeSalePriceEffectiveDateTo(
                $this->getGroupAttributeSalePriceEffectiveDateTo()
            )->convertAttribute(
                $product,
                $entry
            );
        }
    }

    /**
     * Calculate price attribute value
     *
     * @param Product $product
     * @param \Magento\Store\Model\Store $store
     * @param int $priceDisplayType
     * @param bool $inclTax
     * @param bool $isSalePriceAllowed
     * @return float|null|string
     */
    private function _getPrice($product, $store, $priceDisplayType, $inclTax, $isSalePriceAllowed)
    {
        $priceMapValue = $this->getProductAttributeValue($product);
        $price = null;
        if (!is_null($priceMapValue) && floatval($priceMapValue) > .0001) {
            $price = $priceMapValue;
        } else {
            if ($isSalePriceAllowed) {
                $price = $this->catalogPrice->getCatalogRegularPrice($product, $store);
            } else {
                $inclTax = $priceDisplayType != Config::DISPLAY_TYPE_EXCLUDING_TAX;
                $price = $this->catalogPrice->getCatalogPrice($product, $store, $inclTax);
            }
        }
        if ($product->getTypeId() != Product\Type::TYPE_BUNDLE) {
            $price = $this->_catalogData->getTaxPrice($product, $price, $inclTax, null, null, null, $product->getStoreId());
        }
        return $price;
    }

    /**
     * Calculate final price
     *
     * @param Product $product
     * @param \Magento\Store\Model\Store $store
     * @param bool $inclTax
     * @param bool $isSalePriceAllowed
     * @return float|null
     */
    private function _getFinalPrice($product, $store, $inclTax, $isSalePriceAllowed)
    {
        // calculate sale_price attribute value
        $salePriceAttribute = $this->getGroupAttributeSalePrice();
        $salePriceMapValue = null;
        $finalPrice = null;
        if (!is_null($salePriceAttribute)) {
            $salePriceMapValue = $salePriceAttribute->getProductAttributeValue($product);
        }
        if (!is_null($salePriceMapValue) && floatval($salePriceMapValue) > .0001) {
            $finalPrice = $salePriceMapValue;
        } else {
            if ($isSalePriceAllowed) {
                $finalPrice = $this->catalogPrice->getCatalogPrice($product, $store, $inclTax);
            }
        }
        if ($product->getTypeId() != Product\Type::TYPE_BUNDLE) {
            $finalPrice = $this->_catalogData->getTaxPrice(
                $product,
                $finalPrice,
                $inclTax,
                null,
                null,
                null,
                $product->getStoreId()
            );
        }
        return $finalPrice;
    }
}
