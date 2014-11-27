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

namespace Magento\Wishlist\Test\Constraint;

use Mtf\Constraint\AbstractAssertForm;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Wishlist\Test\Page\WishlistIndex;
use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\InjectableFixture;

/**
 * Class AssertBundleProductDetailsInWishlist
 * Assert that the correct option details are displayed on the "View Details" tool tip
 */
class AssertProductDetailsInWishlist extends AbstractAssertForm
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert that the correct option details are displayed on the "View Details" tool tip
     *
     * @param CmsIndex $cmsIndex
     * @param WishlistIndex $wishlistIndex
     * @param InjectableFixture $product
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        WishlistIndex $wishlistIndex,
        InjectableFixture $product,
        FixtureFactory $fixtureFactory
    ) {
        $cmsIndex->getLinksBlock()->openLink('My Wish List');
        $actualOptions = $wishlistIndex->getItemsBlock()->getItemProduct($product)->getOptions();
        $cartFixture = $fixtureFactory->createByCode('cart', ['data' => ['items' => ['products' => [$product]]]]);
        $expectedOptions = $cartFixture->getItems()[0]->getData()['options'];

        $errors = $this->verifyData($expectedOptions, $actualOptions);
        \PHPUnit_Framework_Assert::assertEmpty($errors, $errors);
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return "Expected product options are equal to actual.";
    }
}
