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
namespace Magento\Catalog\Test\Fixture;

use Mtf\System\Config;
use Mtf\Factory\Factory;
use Mtf\Fixture\DataFixture;

class Product extends DataFixture
{
    /**
     * Attribute set for mapping data into ui tabs
     */
    const GROUP_PRODUCT_DETAILS     = 'product-details';
    const GROUP_ADVANCED_SEO        = 'search-engine-optimization';
    const GROUP_PRODUCT_WEBSITE     = 'websites';
    const GROUP_PRODUCT_INVENTORY   = 'advanced-inventory';
    const GROUP_PRODUCT_PRICING     = 'advanced-pricing';
    const GROUP_CUSTOM_OPTIONS      = 'customer-options';

    /**
     * Possible options used for visibility field
     */
    const VISIBILITY_NOT_VISIBLE    = 'Not Visible Individually';
    const VISIBILITY_IN_CATALOG     = 'Catalog';
    const VISIBILITY_IN_SEARCH      = 'Search';
    const VISIBILITY_BOTH           = 'Catalog, Search';

    const DEFAULT_ATTRIBUTE_SET_ID  = 4;

    /**
     * List of categories fixtures
     *
     * @var array
     */
    protected $categories = array();

    /**
     * List of fixtures from created products
     *
     * @var array
     */
    protected $products = array();

    /**
     * Custom constructor to create product with assigned category
     *
     * @param Config $configuration
     * @param array $placeholders
     */
    public function __construct(Config $configuration, $placeholders = array())
    {
        parent::__construct($configuration, $placeholders);

        if (isset($placeholders['categories'])) {
            $this->categories = $placeholders['categories'];
        } else {
            $this->_placeholders['category::getCategoryName'] = array($this, 'categoryProvider');
            $this->_placeholders['category::getCategoryId'] = array($this, 'categoryProvider');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function _initData()
    {
        $this->_data = array_merge_recursive(
            $this->_data,
            array(
                'fields' => array(
                    'name' => array(
                        'value' => substr(get_class($this), strrpos(get_class($this), '\\') + 1) . ' %isolation%',
                        'group' => static::GROUP_PRODUCT_DETAILS
                    ),
                    'sku' => array(
                        'value' => substr(get_class($this), strrpos(get_class($this), '\\') + 1) . '_sku_%isolation%',
                        'group' => static::GROUP_PRODUCT_DETAILS
                    )
                )
            )
        );
    }

    /**
     * Get data from repository and reassign it
     *
     * @return void
     */
    public function reset()
    {
        $default = $this->_repository->get('default');

        $this->_dataConfig = $default['config'];
        $this->_data = $default['data'];
    }

    /**
     * Retrieve specify data from product.
     *
     * @param string $placeholder
     * @return mixed
     */
    protected function productProvider($placeholder)
    {
        list($productData, $method) = explode('::', $placeholder);
        $product = $this->getProduct($this->formatProductType($productData));
        return is_callable(array($product, $method)) ? $product->$method() : null;
    }

    /**
     * @param string $productData
     * @return string
     */
    protected function formatProductType($productData)
    {
        return $productData;
    }

    /**
     * Create a new product
     *
     * @param string $productType
     * @throws \InvalidArgumentException
     * @return Product
     */
    public function getProduct($productType)
    {
        if (!isset($this->products[$productType])) {
            switch ($productType) {
                case 'simple':
                    $product = Factory::getFixtureFactory()->getMagentoCatalogSimpleProduct();
                    $product->switchData($productType . '_required');
                    break;
                case 'virtual':
                    $product = Factory::getFixtureFactory()->getMagentoCatalogVirtualProduct();
                    $product->switchData($productType . '_required');
                    break;
                case 'downloadable':
                    $product = Factory::getFixtureFactory()
                        ->getMagentoDownloadableDownloadableProductLinksNotPurchasedSeparately();
                    break;
                case 'configurable':
                    $product = Factory::getFixtureFactory()->getMagentoConfigurableProductConfigurableProduct();
                    break;
                default:
                    throw new \InvalidArgumentException(
                        "Product of type '$productType' cannot be added to grouped product."
                    );
            }
            $product->persist();
            $this->products[$productType] = $product;
        }
        return $this->products[$productType];
    }

    /**
     * Retrieve specify data from category.
     *
     * @param string $placeholder
     * @return mixed
     */
    protected function categoryProvider($placeholder)
    {
        list($key, $method) = explode('::', $placeholder);
        $category = $this->getCategory($key);
        return is_callable(array($category, $method)) ? $category->$method() : null;
    }

    /**
     * Create a new category and retrieve category fixture
     *
     * @param string $key
     * @return mixed
     */
    protected function getCategory($key)
    {
        if (!isset($this->categories[$key])) {
            $category = Factory::getFixtureFactory()->getMagentoCatalogCategory();
            $category->switchData('subcategory');
            $category->persist();
            $this->categories[$key] = $category;
        }
        return $this->categories[$key];
    }

    /**
     * Get categories
     *
     * @return array
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Get category Ids
     *
     * @return array
     */
    public function getCategoryIds()
    {
        $categoryIds = array();
        /** @var Category $category */
        foreach ($this->categories as $category) {
            $categoryIds[] = $category->getCategoryId();
        }
        return $categoryIds;
    }

    /**
     * Get product name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getData('fields/name/value');
    }

    /**
     * Get product sku
     *
     * @return string
     */
    public function getSku()
    {
        return $this->getData('fields/sku/value');
    }

    /**
     * Get product price
     *
     * @return string
     */
    public function getProductPrice()
    {
        return $this->getData('fields/price/value');
    }

    /**
     * Get product special price
     *
     * @return string
     */
    public function getProductSpecialPrice()
    {
        return $this->getData('fields/special_price/value');
    }

    /**
     * Get category name
     *
     * @return string
     */
    public function getCategoryName()
    {
        return $this->getData('category_name');
    }

    /**
     * Get product url
     *
     * @return string
     */
    public function getUrlKey()
    {
        $fields = $this->getData('fields');
        if (isset($fields['url'])) {
            return $fields['url'];
        } else {
            return trim(strtolower(preg_replace('#[^0-9a-z]+#i', '-', $this->getName())), '-');
        }
    }

    /**
     * Get product id
     *
     * @return string
     */
    public function getProductId()
    {
        return $this->getData('fields/id/value');
    }

    /**
     * Create product
     *
     * @return void
     */
    public function persist()
    {
        $id = Factory::getApp()->magentoCatalogCreateProduct($this);
        $this->_data['fields']['id']['value'] = $id;
    }

    /**
     * Stab for filling product options
     *
     * @return void
     */
    public function getProductOptions()
    {
        $selections = $this->getData('checkout/selections');
        $options = array();
        if (!empty($selection)) {
            foreach ($selections as $selection) {
                $options[$selection['attribute_name']] = $selection['option_name'];
            }
        }
        return $options;
    }

    /**
     * Get Url params
     *
     * @param string $urlKey
     * @return string
     */
    public function getUrlParams($urlKey)
    {
        $params = array();
        $config = $this->getDataConfig();
        if (!empty($config[$urlKey]) && is_array($config[$urlKey])) {
            foreach ($config[$urlKey] as $key => $value) {
                $params[] = $key . '/' . $value;
            }
        }
        return implode('/', $params);
    }

    /**
     * Get new category name
     *
     * @return string
     */
    public function getNewCategoryName()
    {
        return $this->getData('category_new/category_name/value');
    }

    /**
     * Get parent for new category
     *
     * @return string
     */
    public function getNewCategoryParent()
    {
        return $this->getData('category_new/parent_category/value');
    }

    /**
     * Get product Minimum Advertised Price
     *
     * @return string
     */
    public function getProductMapPrice()
    {
        return $this->getData('fields/msrp/value');
    }

    /**
     * Get checkout data for fill options of product
     *
     * @return array|null
     */
    public function getCheckoutData()
    {
        return $this->getData('fields/checkout_data');
    }
}
