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

namespace Magento\Wishlist\Test\TestCase;

use Mtf\Client\Browser;
use Mtf\TestCase\Injectable;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Customer\Test\Page\CustomerAccountLogin;
use Magento\Customer\Test\Page\CustomerAccountIndex;
use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Customer\Test\Page\CustomerAccountLogout;
use Magento\Wishlist\Test\Page\WishlistIndex;
use Magento\Wishlist\Test\Page\WishlistShare;

/**
 * Test Creation for ShareWishlistEntity
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Create Customer Account
 * 2. Create product
 *
 * Steps:
 * 1. Login to frontend as a Customer
 * 2. Add product to Wish List
 * 3. Click "Share Wish List" button
 * 4. Fill in all data according to data set
 * 5. Click "Share Wishlist" button
 * 6. Perform all assertions
 *
 * @group Wishlist_(CS)
 * @ZephyrId MAGETWO-23394
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShareWishlistEntityTest extends Injectable
{
    /**
     * Cms index page
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * Customer login page
     *
     * @var CustomerAccountLogin
     */
    protected $customerAccountLogin;

    /**
     * Customer account index page
     *
     * @var CustomerAccountIndex
     */
    protected $customerAccountIndex;

    /**
     * Product view page
     *
     * @var CatalogProductView
     */
    protected $catalogProductView;

    /**
     * Page CustomerAccountLogout
     *
     * @var CustomerAccountLogout
     */
    protected $customerAccountLogout;

    /**
     * Wishlist index page
     *
     * @var WishlistIndex
     */
    protected $wishlistIndex;

    /**
     * Wishlist share page
     *
     * @var WishlistShare
     */
    protected $wishlistShare;

    /**
     * Prepare data
     *
     * @param CustomerInjectable $customer
     * @param CatalogProductSimple $product
     * @return array
     */
    public function __prepare(
        CustomerInjectable $customer,
        CatalogProductSimple $product
    ) {
        $customer->persist();
        $product->persist();

        return [
            'customer' => $customer,
            'product' => $product
        ];
    }

    /**
     * Injection data
     *
     * @param CmsIndex $cmsIndex
     * @param CustomerAccountLogin $customerAccountLogin
     * @param CustomerAccountIndex $customerAccountIndex
     * @param CustomerAccountLogout $customerAccountLogout
     * @param CatalogProductView $catalogProductView
     * @param WishlistIndex $wishlistIndex
     * @param WishlistShare $wishlistShare
     * @return void
     */
    public function __inject(
        CmsIndex $cmsIndex,
        CustomerAccountLogin $customerAccountLogin,
        CustomerAccountIndex $customerAccountIndex,
        CustomerAccountLogout $customerAccountLogout,
        CatalogProductView $catalogProductView,
        WishlistIndex $wishlistIndex,
        WishlistShare $wishlistShare
    ) {
        $this->cmsIndex = $cmsIndex;
        $this->customerAccountLogin = $customerAccountLogin;
        $this->customerAccountIndex = $customerAccountIndex;
        $this->customerAccountLogout = $customerAccountLogout;
        $this->catalogProductView = $catalogProductView;
        $this->wishlistIndex = $wishlistIndex;
        $this->wishlistShare = $wishlistShare;
    }

    /**
     * Share wish list
     *
     * @param Browser $browser
     * @param CustomerInjectable $customer
     * @param CatalogProductSimple $product
     * @param array $sharingInfo
     * @return void
     */
    public function test(
        Browser $browser,
        CustomerInjectable $customer,
        CatalogProductSimple $product,
        array $sharingInfo
    ) {
        $this->markTestIncomplete("Bug: MAGETWO-30105");
        //Steps
        $this->loginCustomer($customer);
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $this->catalogProductView->getViewBlock()->clickAddToWishlist();
        $this->wishlistIndex->getWishlistBlock()->clickShareWishList();
        $this->wishlistShare->getSharingInfoForm()->fillForm($sharingInfo);
        $this->wishlistShare->getSharingInfoForm()->shareWishlist();
    }

    /**
     * Login customer
     *
     * @param CustomerInjectable $customer
     * @return void
     */
    protected function loginCustomer(CustomerInjectable $customer)
    {
        $this->cmsIndex->open();
        if (!$this->cmsIndex->getLinksBlock()->isLinkVisible('Log Out')) {
            $this->cmsIndex->getLinksBlock()->openLink("Log In");
            $this->customerAccountLogin->getLoginBlock()->login($customer);
        }
    }

    /**
     * Log out after test
     *
     * @return void
     */
    public function tearDown()
    {
        $this->customerAccountLogout->open();
    }
}
