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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/** @var $this \Magento\Setup\Module\SetupModule */
$connection = $this->getConnection();

$connection->dropForeignKey(
    $this->getTable('catalog_category_product_index'),
    $this->getFkName('catalog_category_product_index', 'category_id', 'catalog_category_entity', 'entity_id')
)->dropForeignKey(
    $this->getTable('catalog_category_product_index'),
    $this->getFkName('catalog_category_product_index', 'product_id', 'catalog_product_entity', 'entity_id')
)->dropForeignKey(
    $this->getTable('catalog_category_product_index'),
    $this->getFkName('catalog_category_product_index', 'store_id', 'store', 'store_id')
);

$connection->dropTable($this->getTable('catalog_product_enabled_index'));
$connection->dropTable($this->getTable('catalog_category_product_index_idx'));
$connection->dropTable($this->getTable('catalog_category_product_index_enbl_idx'));
$connection->dropTable($this->getTable('catalog_category_product_index_enbl_tmp'));
$connection->dropTable($this->getTable('catalog_category_anc_categs_index_idx'));
$connection->dropTable($this->getTable('catalog_category_anc_categs_index_tmp'));
$connection->dropTable($this->getTable('catalog_category_anc_products_index_idx'));
$connection->dropTable($this->getTable('catalog_category_anc_products_index_tmp'));
