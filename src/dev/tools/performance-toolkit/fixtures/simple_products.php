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
/** @var \Magento\ToolkitFramework\Application $this */
$simpleProductsCount = \Magento\ToolkitFramework\Config::getInstance()->getValue('simple_products', 180);
$this->resetObjectManager();

/** @var \Magento\Store\Model\StoreManager $storeManager */
$storeManager = $this->getObjectManager()->create('\Magento\Store\Model\StoreManager');
/** @var $category \Magento\Catalog\Model\Category */
$category = $this->getObjectManager()->get('Magento\Catalog\Model\Category');

$result = array();
//Get all websites
$websites = $storeManager->getWebsites();
foreach ($websites as $website) {
    $websiteCode = $website->getCode();
    //Get all groups
    $websiteGroups = $website->getGroups();
    foreach ($websiteGroups as $websiteGroup) {
        $websiteGroupRootCategory = $websiteGroup->getRootCategoryId();
        $category->load($websiteGroupRootCategory);
        $categoryResource = $category->getResource();
        //Get all categories
        $resultsCategories = $categoryResource->getAllChildren($category);
        foreach ($resultsCategories as $resultsCategory) {
            $category->load($resultsCategory);
            $structure = explode('/', $category->getPath());
            $pathSize  = count($structure);
            if ($pathSize > 1) {
                $path = array();
                for ($i = 1; $i < $pathSize; $i++) {
                    $path[] = $category->load($structure[$i])->getName();
                }
                array_shift($path);
                $resultsCategoryName = implode('/', $path);
            } else {
                $resultsCategoryName = $category->getName();
            }
            //Deleted root categories
            if (trim($resultsCategoryName)!='') {
                $result[$resultsCategory] = array($websiteCode, $resultsCategoryName);
            }
        }
    }
}
$result = array_values($result);

$productWebsite = function ($index) use ($result) {
    return $result[$index % count($result)][0];
};
$productCategory = function ($index) use ($result) {
    return $result[$index % count($result)][1];
};

/**
 * Create products
 */
$pattern = array(
    '_attribute_set'    => 'Default',
    '_type'             => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
    '_product_websites' => $productWebsite,
    '_category'         => $productCategory,
    'name'              => 'Simple Product %s',
    'short_description' => 'Short simple product description %s',
    'weight'            => 1,
    'description'       => 'Full simple product Description %s',
    'sku'               => 'product_dynamic_%s',
    'price'             => 10,
    'visibility'        => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
    'status'            => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
    'tax_class_id'      => 2,

    // actually it saves without stock data, but by default system won't show on the frontend products out of stock
    'is_in_stock'                   => 1,
    'qty'                           => 100500,
    'use_config_min_qty'            => '1',
    'use_config_backorders'         => '1',
    'use_config_min_sale_qty'       => '1',
    'use_config_max_sale_qty'       => '1',
    'use_config_notify_stock_qty'   => '1',
    'use_config_manage_stock'       => '1',
    'use_config_qty_increments'     => '1',
    'use_config_enable_qty_inc'     => '1'
);
$generator = new \Magento\ToolkitFramework\ImportExport\Fixture\Generator($pattern, $simpleProductsCount);
/** @var \Magento\ImportExport\Model\Import $import */
$import = $this->getObjectManager()->create(
    'Magento\ImportExport\Model\Import',
    array('data' => array('entity' => 'catalog_product', 'behavior' => 'append'))
);
// it is not obvious, but the validateSource() will actually save import queue data to DB
$import->validateSource($generator);
// this converts import queue into actual entities
$import->importSource();
