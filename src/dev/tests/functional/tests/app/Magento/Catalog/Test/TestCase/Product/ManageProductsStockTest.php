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

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Mtf\Fixture\FixtureFactory;
use Mtf\ObjectManager;
use Mtf\TestCase\Injectable;

/**
 * Test Creation for ManageProductsStock
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Set Configuration:
 *      - Display OutOfStock = Yes
 *      - Backorders - Allow Qty below = 0
 * 2. Create products according to dataSet
 *
 * Steps:
 * 1. Open product on frontend
 * 2. Add product to cart
 * 3. Perform all assertions
 *
 * @group Inventory_(MX)
 * @ZephyrId MAGETWO-29543
 */
class ManageProductsStockTest extends Injectable
{
    /**
     * Fixture factory
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Object manager
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Setup configuration
     *
     * @param ObjectManager $objectManager
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __prepare(ObjectManager $objectManager, FixtureFactory $fixtureFactory)
    {
        $this->objectManager = $objectManager;
        $this->fixtureFactory = $fixtureFactory;
        $setupConfigurationStep = $objectManager->create(
            'Magento\Core\Test\TestStep\SetupConfigurationStep',
            ['configData' => "display_out_of_stock,backorders_allow_qty_below"]
        );
        $setupConfigurationStep->run();
    }

    /**
     * Manage products stock
     *
     * @param CatalogProductSimple $product
     * @return array
     */
    public function test(CatalogProductSimple $product)
    {
        // Preconditions
        $product->persist();

        // Steps
        $addProductsToTheCartStep = $this->objectManager->create(
            'Magento\Checkout\Test\TestStep\AddProductsToTheCartStep',
            ['products' => [$product]]
        );
        $addProductsToTheCartStep->run();

        $cart['data']['items'] = ['products' => [$product]];
        return ['cart' => $this->fixtureFactory->createByCode('cart', $cart)];
    }

    /**
     * Set default configuration
     *
     * @return void
     */
    public static function tearDownAfterClass()
    {
        $setupConfigurationStep = ObjectManager::getInstance()->create(
            'Magento\Core\Test\TestStep\SetupConfigurationStep',
            ['configData' => "display_out_of_stock,backorders_allow_qty_below", 'rollback' => true]
        );
        $setupConfigurationStep->run();
    }
}
