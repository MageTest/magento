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
namespace Magento\Catalog\Model\Indexer\Product\Flat\Action;

/**
 * Class RowsTest
 */
class RowsTest extends \Magento\TestFramework\Indexer\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Processor
     */
    protected $_processor;

    protected function setUp()
    {
        $this->_product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $this->_processor = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Catalog\Model\Indexer\Product\Flat\Processor'
        );
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_product 1
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testProductsUpdate()
    {
        $this->markTestSkipped('Should be fixed in MAGETWO-25641');
        $this->_product->load(1);

        $this->_processor->reindexList(array($this->_product->getId()));

        $categoryFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Catalog\Model\CategoryFactory'
        );
        $listProduct = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Catalog\Block\Product\ListProduct'
        );

        $category = $categoryFactory->create()->load(2);
        $layer = $listProduct->getLayer();
        $layer->setCurrentCategory($category);
        $productCollection = $layer->getProductCollection();

        $this->assertCount(1, $productCollection);

        /** @var $product \Magento\Catalog\Model\Product */
        foreach ($productCollection as $product) {
            $this->assertEquals($this->_product->getName(), $product->getName());
            $this->assertEquals($this->_product->getShortDescription(), $product->getShortDescription());
        }
    }
}
