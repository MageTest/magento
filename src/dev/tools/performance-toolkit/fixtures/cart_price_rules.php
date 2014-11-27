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
$cartPriceRulesCount = \Magento\ToolkitFramework\Config::getInstance()->getValue('cart_price_rules', 9);
$cartPriceRulesProductsFloor = \Magento\ToolkitFramework\Config::getInstance()->getValue('cart_price_rules_floor', 3);
$this->resetObjectManager();

/** @var \Magento\Store\Model\StoreManager $storeManager */
$storeManager = $this->getObjectManager()->create('\Magento\Store\Model\StoreManager');
/** @var $category \Magento\Catalog\Model\Category */
$category = $this->getObjectManager()->get('Magento\Catalog\Model\Category');
/** @var $model  \Magento\SalesRule\Model\Rule*/
$model = $this->getObjectManager()->get('Magento\SalesRule\Model\Rule');

//Get all websites
$categoriesArray = array();
$websites = $storeManager->getWebsites();
foreach ($websites as $website) {
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
            if (count($structure) > 2) {
                $categoriesArray[] = array($category->getId(), $website->getId());
            }
        }
    }
}
asort($categoriesArray);
$categoriesArray = array_values($categoriesArray);
$idField = $model->getIdFieldName();


for ($i = 0; $i < $cartPriceRulesCount; $i++) {
    $ruleName = sprintf('Shopping Cart Price Rule %1$d', $i);
    $data = array(
        $idField                => null,
        'product_ids'           => '',
        'name'                  => $ruleName,
        'description'           => '',
        'is_active'             => '1',
        'website_ids'           => $categoriesArray[$i % count($categoriesArray)][1],
        'customer_group_ids'    => array (
            0 => '0',
            1 => '1',
            2 => '2',
            3 => '3',
        ),
        'coupon_type'           => '1',
        'coupon_code'           => '',
        'uses_per_customer'     => '',
        'from_date'             => '',
        'to_date'               => '',
        'sort_order'            => '',
        'is_rss'                => '1',
        'rule'                  => array (
            'conditions' =>
            array (
                1 =>
                array (
                    'type' => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Combine',
                    'aggregator' => 'all',
                    'value' => '1',
                    'new_child' => '',
                ),
                '1--1' =>
                array (
                    'type' => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Address',
                    'attribute' => 'total_qty',
                    'operator' => '>=',
                    'value' => $cartPriceRulesProductsFloor + $i,
                ),
                '1--2' =>
                array (
                    'type' => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Product\\Found',
                    'value' => '1',
                    'aggregator' => 'all',
                    'new_child' => '',
                ),
                '1--2--1' =>
                array (
                    'type' => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Product',
                    'attribute' => 'category_ids',
                    'operator' => '==',
                    'value' => $categoriesArray[$i % count($categoriesArray)][0],
                ),
            ),
            'actions' =>
            array (
                1 =>
                array (
                    'type' => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Product\\Combine',
                    'aggregator' => 'all',
                    'value' => '1',
                    'new_child' => '',
                ),
            ),
        ),
        'simple_action'             => 'by_percent',
        'discount_amount'           => '10',
        'discount_qty'              => '0',
        'discount_step'             => '',
        'apply_to_shipping'         => '0',
        'simple_free_shipping'      => '0',
        'stop_rules_processing'     => '0',
        'reward_points_delta'       => '',
        'store_labels'              => array (
            0 => '',
            1 => '',
            2 => '',
            3 => '',
            4 => '',
            5 => '',
            6 => '',
            7 => '',
            8 => '',
            9 => '',
            10 => '',
            11 => '',
        ),
        'page'                      => '1',
        'limit'                     => '20',
        'in_banners'                => '',
        'banner_id'                 => array (
            'from'  => '',
            'to'    => '',
        ),
        'banner_name'               => '',
        'visible_in'                => '',
        'banner_is_enabled'         => '',
        'related_banners'           => array (),
    );
    if (isset($data['simple_action']) && $data['simple_action'] == 'by_percent'
        && isset($data['discount_amount'])
    ) {
        $data['discount_amount'] = min(100, $data['discount_amount']);
    }
    if (isset($data['rule']['conditions'])) {
        $data['conditions'] = $data['rule']['conditions'];
    }
    if (isset($data['rule']['actions'])) {
        $data['actions'] = $data['rule']['actions'];
    }
    unset($data['rule']);

    $model->loadPost($data);
    $useAutoGeneration = (int)!empty($data['use_auto_generation']);
    $model->setUseAutoGeneration($useAutoGeneration);
    $model->save();
}
