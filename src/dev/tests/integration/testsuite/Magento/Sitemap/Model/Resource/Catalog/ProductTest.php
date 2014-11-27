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
namespace Magento\Sitemap\Model\Resource\Catalog;

/**
 * Test class for \Magento\Sitemap\Model\Resource\Catalog\Product.
 * - test products collection generation for sitemap
 *
 * @magentoDataFixture Magento/Sitemap/_files/sitemap_products.php
 */
class ProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test getCollection None images
     * 1) Check that image attributes were not loaded
     * 2) Check no images were loaded
     *
     * @magentoConfigFixture default_store sitemap/product/image_include none
     */
    public function testGetCollectionNone()
    {
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Sitemap\Model\Resource\Catalog\Product'
        );
        $products = $model->getCollection(\Magento\Store\Model\Store::DISTRO_STORE_ID);

        $this->_checkProductCollection($products, 3, array(1, 4, 5));

        // Check that no image attributes were loaded
        foreach ($products as $product) {
            $this->assertEmpty($product->getName(), 'Attribute name is not empty');
            $this->assertEmpty($product->getImage(), 'Attribute image is not empty');
            $this->assertEmpty($product->getThumbnail(), 'Attribute thumbnail is not empty');
        }

        $this->assertEmpty($products[4]->getImages(), 'Images were loaded');
    }

    /**
     * Test getCollection All images
     * 1) Check thumbnails
     * 2) Check images loading
     * 3) Check thumbnails when no thumbnail selected
     *
     * @magentoConfigFixture default_store sitemap/product/image_include all
     */
    public function testGetCollectionAll()
    {
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Sitemap\Model\Resource\Catalog\Product'
        );
        $products = $model->getCollection(\Magento\Store\Model\Store::DISTRO_STORE_ID);

        $this->_checkProductCollection($products, 3, array(1, 4, 5));

        // Check name attribute was loaded
        foreach ($products as $product) {
            $this->assertNotEmpty($product->getName(), 'name attribute was not loaded');
        }

        // Check thumbnail attribute
        $this->assertEmpty($products[1]->getThumbnail(), 'Thumbnail attribute was loaded');
        $this->assertEmpty($products[4]->getImage(), 'Image attribute was loaded');
        $this->assertEquals('/m/a/magento_image_sitemap.png', $products[4]->getThumbnail(), 'Incorrect thumbnail');

        // Check images loading
        $this->assertEmpty($products[1]->getImages(), 'Images were loaded');
        $this->assertNotEmpty($products[4]->getImages(), 'Images were not loaded');
        $this->assertEquals('Simple Images', $products[4]->getImages()->getTitle(), 'Incorrect title');
        $this->assertEquals(
            'catalog/product/m/a/magento_image_sitemap.png',
            $products[4]->getImages()->getThumbnail(),
            'Incorrect thumbnail'
        );
        $this->assertCount(2, $products[4]->getImages()->getCollection(), 'Not all images were loaded');

        $imagesCollection = $products[4]->getImages()->getCollection();
        $this->assertEquals(
            'catalog/product/m/a/magento_image_sitemap.png',
            $imagesCollection[0]->getUrl(),
            'Incorrect image url'
        );
        $this->assertEquals(
            'catalog/product/s/e/second_image.png',
            $imagesCollection[1]->getUrl(),
            'Incorrect image url'
        );
        $this->assertEmpty($imagesCollection[0]->getCaption(), 'Caption not empty');

        // Check no selection
        $this->assertEmpty($products[5]->getImage(), 'image is not empty');
        $this->assertEquals('no_selection', $products[5]->getThumbnail(), 'thumbnail is incorrect');
        $imagesCollection = $products[5]->getImages()->getCollection();
        $this->assertCount(1, $imagesCollection);
        $this->assertEquals(
            'catalog/product/s/e/second_image_1.png',
            $imagesCollection[0]->getUrl(),
            'Image url is incorrect'
        );
        $this->assertEquals(
            'catalog/product/s/e/second_image_1.png',
            $products[5]->getImages()->getThumbnail(),
            'Product thumbnail is incorrect'
        );
    }

    /**
     * Test getCollection None images
     * 1) Check that image attributes were not loaded
     * 2) Check no images were loaded
     * 3) Check thumbnails when no thumbnail selected
     *
     * @magentoConfigFixture default_store sitemap/product/image_include base
     */
    public function testGetCollectionBase()
    {
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Sitemap\Model\Resource\Catalog\Product'
        );
        $products = $model->getCollection(\Magento\Store\Model\Store::DISTRO_STORE_ID);

        $this->_checkProductCollection($products, 3, array(1, 4, 5));

        // Check name attribute was loaded
        foreach ($products as $product) {
            $this->assertNotEmpty($product->getName(), 'name attribute was not loaded');
        }

        // Check thumbnail attribute
        $this->assertEmpty($products[1]->getImage(), 'image attribute was loaded');
        $this->assertEmpty($products[4]->getThumbnail(), 'thumbnail attribute was loaded');
        $this->assertEquals('/s/e/second_image.png', $products[4]->getImage(), 'Incorrect image attribute');

        // Check images loading
        $this->assertEmpty($products[1]->getImages(), 'Images were loaded');
        $this->assertNotEmpty($products[4]->getImages(), 'Images were not loaded');
        $this->assertEquals('Simple Images', $products[4]->getImages()->getTitle(), 'Incorrect title');
        $this->assertEquals(
            'catalog/product/s/e/second_image.png',
            $products[4]->getImages()->getThumbnail(),
            'Incorrect thumbnail'
        );
        $this->assertCount(1, $products[4]->getImages()->getCollection(), 'Number of loaded images is incorrect');

        $imagesCollection = $products[4]->getImages()->getCollection();
        $this->assertEquals(
            'catalog/product/s/e/second_image.png',
            $imagesCollection[0]->getUrl(),
            'Incorrect image url'
        );
        $this->assertEmpty($imagesCollection[0]->getCaption(), 'Caption not empty');

        // Check no selection
        $this->assertEmpty($products[5]->getThumbnail(), 'thumbnail is not empty');
        $this->assertEquals('no_selection', $products[5]->getImage(), 'image is incorrect');
        $this->assertEmpty($products[5]->getImages(), 'Product images were loaded');
    }

    /**
     * Check product collection
     * 1) Check that all products are loaded
     * 2) Check that products are loaded correctly and all required attributes present
     *
     * @param array $products
     * @param int $expectedCount
     * @param array $expectedKeys
     */
    protected function _checkProductCollection(array $products, $expectedCount, array $expectedKeys)
    {
        // Check all expected products were added into collection
        $this->assertCount($expectedCount, $products, 'Number of loaded products is incorrect');
        foreach ($expectedKeys as $expectedKey) {
            $this->assertArrayHasKey($expectedKey, $products);
        }

        // Check all expected attributes are present
        foreach ($products as $product) {
            $this->assertNotEmpty($product->getUpdatedAt());
            $this->assertNotEmpty($product->getId());
            $this->assertNotEmpty($product->getUrl());
        }
    }
}
