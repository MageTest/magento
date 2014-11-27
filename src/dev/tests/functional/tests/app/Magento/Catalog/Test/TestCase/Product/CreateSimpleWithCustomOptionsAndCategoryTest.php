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

namespace Magento\Catalog\Test\TestCase\Product;

use Mtf\Factory\Factory;
use Mtf\TestCase\Functional;
use Magento\Catalog\Test\Fixture\Product;

/**
 * Class CreateSimpleWithCustomOptionsAndCategoryTest
 * Create simple product with custom options
 */
class CreateSimpleWithCustomOptionsAndCategoryTest extends Functional
{
    /**
     * Login into backend area before test
     *
     * @return void
     */
    protected function setUp()
    {
        Factory::getApp()->magentoBackendLoginUser();
    }

    /**
     * Creating simple product with custom options and assigning it to the category
     *
     * @ZephyrId MAGETWO-12703
     * @return void
     */
    public function testCreateProduct()
    {
        $product = Factory::getFixtureFactory()->getMagentoCatalogSimpleProduct();
        $product->switchData('simple_custom_options');
        //Data
        $createProductPage = Factory::getPageFactory()->getCatalogProductNew();
        $productForm = $createProductPage->getProductForm();
        //Steps
        $createProductPage->open([
                'type' => $product->getDataConfig()['create_url_params']['type'],
                'set' => $product->getDataConfig()['create_url_params']['set']
            ]);
        $category = $product->getCategories()['category'];
        $productForm->fill($product, null, $category);
        $createProductPage->getFormPageActions()->save();
        //Verifying
        $createProductPage->getMessagesBlock()->waitSuccessMessage();
        // Flush cache
        $cachePage = Factory::getPageFactory()->getAdminCache();
        $cachePage->open();
        $cachePage->getActionsBlock()->flushMagentoCache();
        //Verifying
        $this->assertOnGrid($product);
        $this->assertOnCategory($product);
    }

    /**
     * Assert existing product on admin product grid
     *
     * @param Product $product
     * @return void
     */
    protected function assertOnGrid($product)
    {
        $productGridPage = Factory::getPageFactory()->getCatalogProductIndex();
        $productGridPage->open();
        /** @var \Magento\Catalog\Test\Block\Adminhtml\Product\Grid $gridBlock */
        $gridBlock = $productGridPage->getProductGrid();
        $this->assertTrue($gridBlock->isRowVisible(['sku' => $product->getSku()]));
    }

    /**
     * Assert product data on category and product pages
     *
     * @param Product $product
     * @return void
     */
    protected function assertOnCategory($product)
    {
        //Pages
        $frontendHomePage = Factory::getPageFactory()->getCmsIndexIndex();
        $categoryPage = Factory::getPageFactory()->getCatalogCategoryView();
        $productPage = Factory::getPageFactory()->getCatalogProductView();
        //Steps
        $frontendHomePage->open();
        $frontendHomePage->getTopmenu()->selectCategoryByName($product->getCategoryName());
        //Verification on category product list
        $productListBlock = $categoryPage->getListProductBlock();
        $this->assertTrue($productListBlock->isProductVisible($product->getName()));
        $productListBlock->openProductViewPage($product->getName());
        //Verification on product detail page
        $productViewBlock = $productPage->getViewBlock();
        $this->assertEquals($product->getName(), $productViewBlock->getProductName());
        $price = $productViewBlock->getPriceBlock()->getPrice();
        $this->assertEquals(number_format($product->getProductPrice(), 2), $price);

        $productOptionsBlock = $productPage->getViewBlock()->getCustomOptionsBlock();
        $fixture = $product->getData('fields/custom_options/value');
        $actualOptions = $productOptionsBlock->getOptions($product);
        $this->assertCount(count($fixture), $actualOptions);
        $this->assertTrue(isset($actualOptions['custom_options'][$fixture[0]['title']]['options'][0]['title']));
        $this->assertEquals($fixture[0]['title'], $actualOptions['custom_options'][$fixture[0]['title']]['title']);
    }
}
