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
namespace Magento\CatalogUrlRewrite\Model;

class ProductUrlPathGenerator
{
    const XML_PATH_PRODUCT_URL_SUFFIX = 'catalog/seo/product_url_suffix';

    /**
     * Cache for product rewrite suffix
     *
     * @var array
     */
    protected $productUrlSuffix = [];

    /** @var \Magento\Framework\StoreManagerInterface */
    protected $storeManager;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface */
    protected $scopeConfig;

    /** @var \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator */
    protected $categoryUrlPathGenerator;

    /**
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param CategoryUrlPathGenerator $categoryUrlPathGenerator
     */
    public function __construct(
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator $categoryUrlPathGenerator
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->categoryUrlPathGenerator = $categoryUrlPathGenerator;
    }

    /**
     * Retrieve Product Url path (with category if exists)
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\Category $category
     *
     * @return string
     */
    public function getUrlPath($product, $category = null)
    {
        $path = $product->getData('url_path');
        if ($path === null) {
            $path = $this->generateUrlKey($product);
        }
        return $category === null ? $path
            : $this->categoryUrlPathGenerator->getUrlPath($category) . '/' . $path;
    }

    /**
     * Retrieve Product Url path with suffix
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param int $storeId
     * @param \Magento\Catalog\Model\Category $category
     * @return string
     */
    public function getUrlPathWithSuffix($product, $storeId, $category = null)
    {
        return $this->getUrlPath($product, $category) . $this->getProductUrlSuffix($storeId);
    }

    /**
     * Get canonical product url path
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\Category|null $category
     * @return string
     */
    public function getCanonicalUrlPath($product, $category = null)
    {
        $path =  'catalog/product/view/id/' . $product->getId();
        return $category ? $path . '/category/' . $category->getId() : $path;
    }

    /**
     * Generate product url key based on url_key entered by merchant or product name
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function generateUrlKey($product)
    {
        $urlKey = $product->getUrlKey();
        return $product->formatUrlKey($urlKey === '' || $urlKey === null ? $product->getName() : $urlKey);
    }

    /**
     * Retrieve product rewrite suffix for store
     *
     * @param int $storeId
     * @return string
     */
    protected function getProductUrlSuffix($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        if (!isset($this->productUrlSuffix[$storeId])) {
            $this->productUrlSuffix[$storeId] = $this->scopeConfig->getValue(
                self::XML_PATH_PRODUCT_URL_SUFFIX,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
        return $this->productUrlSuffix[$storeId];
    }
}
