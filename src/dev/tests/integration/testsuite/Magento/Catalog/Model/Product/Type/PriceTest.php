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
namespace Magento\Catalog\Model\Product\Type;

/**
 * @magentoDataFixture Magento/Catalog/_files/product_simple.php
 */
class PriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Type\Price
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product\Type\Price'
        );
    }

    public function testGetPrice()
    {
        $this->assertEquals('test', $this->_model->getPrice(new \Magento\Framework\Object(array('price' => 'test'))));
    }

    public function testGetFinalPrice()
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->load(1);
        // fixture

        // regular & tier prices
        $this->assertEquals(10.0, $this->_model->getFinalPrice(1, $product));
        $this->assertEquals(8.0, $this->_model->getFinalPrice(2, $product));
        $this->assertEquals(5.0, $this->_model->getFinalPrice(5, $product));

        // with options
        $product->addCustomOption('option_ids', implode(',', array_keys($product->getOptions())));

        foreach ($product->getOptions() as $id => $option) {
            $product->addCustomOption("option_{$id}", $option->getValue());
        }
        $this->assertEquals(13.0, $this->_model->getFinalPrice(1, $product));
    }

    /**
     * Warning: this is a copy-paste from testGetFinalPrice(), but the method has different interface
     */
    public function testGetChildFinalPrice()
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->load(1);
        // fixture

        // regular & tier prices
        $this->assertEquals(10.0, $this->_model->getChildFinalPrice('', '', $product, 1));
        $this->assertEquals(8.0, $this->_model->getChildFinalPrice('', '', $product, 2));
        $this->assertEquals(5.0, $this->_model->getChildFinalPrice('', '', $product, 5));

        // with options
        $product->addCustomOption('option_ids', implode(',', array_keys($product->getOptions())));
        foreach ($product->getOptions() as $id => $option) {
            $product->addCustomOption("option_{$id}", $option->getValue());
        }
        $this->assertEquals(13.0, $this->_model->getChildFinalPrice('', '', $product, 1));
    }

    public function testGetTierPrice()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->load(1);
        // fixture
        $this->assertEquals(8.0, $this->_model->getTierPrice(2, $product));
        $this->assertEquals(5.0, $this->_model->getTierPrice(5, $product));
    }

    public function testGetTierPriceCount()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->load(1);
        // fixture
        $this->assertEquals(3, $this->_model->getTierPriceCount($product));
    }

    public function testGetFormatedTierPrice()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->load(1);
        // fixture
        $this->assertEquals('<span class="price">$8.00</span>', $this->_model->getFormatedTierPrice(2, $product));
    }

    public function testGetFormatedPrice()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->load(1);
        // fixture
        $this->assertEquals('<span class="price">$10.00</span>', $this->_model->getFormatedPrice($product));
    }

    public function testCalculatePrice()
    {
        $this->assertEquals(10, $this->_model->calculatePrice(10, 8, '1970-12-12 23:59:59', '1971-01-01 01:01:01'));
        $this->assertEquals(8, $this->_model->calculatePrice(10, 8, '1970-12-12 23:59:59', '2034-01-01 01:01:01'));
    }

    public function testCalculateSpecialPrice()
    {
        $this->assertEquals(
            10,
            $this->_model->calculateSpecialPrice(10, 8, '1970-12-12 23:59:59', '1971-01-01 01:01:01')
        );
        $this->assertEquals(
            8,
            $this->_model->calculateSpecialPrice(10, 8, '1970-12-12 23:59:59', '2034-01-01 01:01:01')
        );
    }

    public function testIsTierPriceFixed()
    {
        $this->assertTrue($this->_model->isTierPriceFixed());
    }
}
