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

namespace Magento\Review\Test\TestCase;

use Mtf\Client\Browser;
use Mtf\TestCase\Injectable;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Review\Test\Fixture\ReviewInjectable;
use Magento\Review\Test\Page\Adminhtml\ReviewEdit;
use Magento\Review\Test\Page\Adminhtml\RatingEdit;
use Magento\Review\Test\Page\Adminhtml\RatingIndex;
use Magento\Customer\Test\Page\CustomerAccountLogin;
use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;

/**
 * Test Creation for ManageProductReviewFromCustomerPage
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Create Customer
 * 2. Create simple product
 * 3. Create Product review on the front
 *
 * Steps:
 * 1. Open backend
 * 2. Go to Customers->All Customers
 * 3. Open customer from preconditions
 * 4. Open Product Review tab
 * 5. Open Review created in preconditions
 * 6. Fill data according to dataset
 * 7. Click "Submit review"
 * 8. Perform all assertions
 *
 * @group Reviews_and_Ratings_(MX)
 * @ZephyrId MAGETWO-27625
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ManageProductReviewFromCustomerPageTest extends Injectable
{
    /**
     * Customer index page
     *
     * @var CustomerIndex
     */
    protected $customerIndex;

    /**
     * Customer edit page
     *
     * @var CustomerIndexEdit
     */
    protected $customerIndexEdit;

    /**
     * Customer login page
     *
     * @var CustomerAccountLogin
     */
    protected $customerAccountLogin;

    /**
     * Catalog product view page
     *
     * @var CatalogProductView
     */
    protected $catalogProductView;

    /**
     * Browser
     *
     * @var Browser
     */
    protected $browser;

    /**
     * Cms index page
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * Backend rating grid page
     *
     * @var RatingIndex
     */
    protected $ratingIndex;

    /**
     * Backend rating edit page
     *
     * @var RatingEdit
     */
    protected $ratingEdit;

    /**
     * Review fixture
     *
     * @var ReviewInjectable
     */
    protected $reviewInitial;

    /**
     * Review edit page
     *
     * @var ReviewEdit
     */
    protected $reviewEdit;

    /**
     * Prepare data
     *
     * @param CustomerInjectable $customer
     * @return array
     */
    public function __prepare(CustomerInjectable $customer)
    {
        $customer->persist();
        return ['customer' => $customer];
    }

    /**
     * Injection data
     *
     * @param CustomerIndexEdit $customerIndexEdit
     * @param CustomerIndex $customerIndex
     * @param CmsIndex $cmsIndex
     * @param CustomerAccountLogin $customerAccountLogin
     * @param CatalogProductView $catalogProductView
     * @param Browser $browser
     * @param RatingIndex $ratingIndex
     * @param RatingEdit $ratingEdit
     * @param ReviewEdit $reviewEdit
     * @return void
     */
    public function __inject(
        CustomerIndexEdit $customerIndexEdit,
        CustomerIndex $customerIndex,
        CmsIndex $cmsIndex,
        CustomerAccountLogin $customerAccountLogin,
        CatalogProductView $catalogProductView,
        Browser $browser,
        RatingIndex $ratingIndex,
        RatingEdit $ratingEdit,
        ReviewEdit $reviewEdit
    ) {
        $this->customerIndexEdit = $customerIndexEdit;
        $this->customerIndex = $customerIndex;
        $this->cmsIndex = $cmsIndex;
        $this->customerAccountLogin = $customerAccountLogin;
        $this->catalogProductView = $catalogProductView;
        $this->browser = $browser;
        $this->ratingIndex = $ratingIndex;
        $this->ratingEdit = $ratingEdit;
        $this->reviewEdit = $reviewEdit;
    }

    /**
     * Run manage product review test
     *
     * @param ReviewInjectable $reviewInitial
     * @param ReviewInjectable $review
     * @param CustomerInjectable $customer
     * @return array
     */
    public function test(
        ReviewInjectable $reviewInitial,
        ReviewInjectable $review,
        CustomerInjectable $customer
    ) {
        // Preconditions
        $this->login($customer);
        /** @var CatalogProductSimple $product */
        $product = $reviewInitial->getDataFieldConfig('entity_id')['source']->getEntity();
        $this->browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $this->catalogProductView->getReviewSummary()->getAddReviewLink()->click();
        $this->catalogProductView->getReviewFormBlock()->fill($reviewInitial);
        $this->catalogProductView->getReviewFormBlock()->submit();
        $this->reviewInitial = $reviewInitial;
        // Steps
        $this->customerIndex->open();
        $this->customerIndex->getCustomerGridBlock()->searchAndOpen(['email' => $customer->getEmail()]);
        $this->customerIndexEdit->getCustomerForm()->openTab('product_reviews');
        $filter = [
            'title' => $reviewInitial->getTitle(),
            'sku' => $product->getSku()
        ];
        $this->customerIndexEdit->getCustomerForm()->getTabElement('product_reviews')->getReviewsGrid()
            ->searchAndOpen($filter);
        $this->reviewEdit->getReviewForm()->fill($review);
        $this->reviewEdit->getPageActions()->save();

        return ['reviewInitial' => $reviewInitial, 'product' => $product];
    }

    /**
     * Login customer on frontend
     *
     * @param CustomerInjectable $customer
     * @return void
     */
    protected function login(CustomerInjectable $customer)
    {
        $this->cmsIndex->open();
        if (!$this->cmsIndex->getLinksBlock()->isLinkVisible('Log Out')) {
            $this->cmsIndex->getLinksBlock()->openLink("Log In");
            $this->customerAccountLogin->getLoginBlock()->login($customer);
        }
    }

    /**
     * Clear data after test
     *
     * @return void
     */
    public function tearDown()
    {
        $this->ratingIndex->open();
        if ($this->reviewInitial instanceof ReviewInjectable) {
            foreach ($this->reviewInitial->getRatings() as $rating) {
                $this->ratingIndex->getRatingGrid()->searchAndOpen(['rating_code' => $rating['title']]);
                $this->ratingEdit->getPageActions()->delete();
            }
        }
    }
}
