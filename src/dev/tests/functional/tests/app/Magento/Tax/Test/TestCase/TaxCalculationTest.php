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

namespace Magento\Tax\Test\TestCase;

use Mtf\TestCase\Injectable;
use Mtf\Fixture\FixtureFactory;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Customer\Test\Page\CustomerAccountLogin;
use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Core\Test\Fixture\ConfigData;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\SalesRule\Test\Fixture\SalesRuleInjectable;
use Magento\Tax\Test\Fixture\TaxRule;
use Mtf\ObjectManager;

/**
 * Test TaxCalculationTest
 *
 * Test Flow:
 * Steps:
 * 1. Log in as default admin user.
 * 2. Go to Stores > Taxes > Tax Rules.
 * 3. Click 'Add New Tax Rule' button.
 * 4. Assign default rates to rule.
 * 5. Save Tax Rate.
 * 6. Go to Products > Catalog.
 * 7. Add new product.
 * 8. Fill data according to dataset.
 * 9. Save product.
 * 10. Go to Stores > Configuration.
 * 11. Fill Tax configuration according to data set.
 * 12. Save tax configuration.
 * 13. Perform all assertions.
 *
 * @group Tax_(CS)
 * @ZephyrId MAGETWO-27809
 */
class TaxCalculationTest extends Injectable
{
    /**
     * Catalog product page
     *
     * @var CheckoutCart
     */
    protected $checkoutCart;

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
     * Fixture factory
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Fixture customer
     *
     * @var CustomerInjectable
     */
    protected $customer;

    /**
     * Fixture SalesRule
     *
     * @var SalesRuleInjectable
     */
    protected $salesRule;

    /**
     * Sales Rule Id
     *
     * @var array
     */
    public static $salesRuleName;

    /**
     * Tax Rule Id
     *
     * @var array
     */
    public static $taxRuleCode;

    /**
     * Skip failed tests
     *
     * @return void
     */
    public static function setUpBeforeClass()
    {
        self::markTestIncomplete("MAGETWO-23964, MAGETWO-28454");
    }

    /**
     * Prepare data
     *
     * @param FixtureFactory $fixtureFactory
     * @param SalesRuleInjectable $salesRule
     * @return void
     */
    public function __prepare(
        FixtureFactory $fixtureFactory,
        SalesRuleInjectable $salesRule
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $customer = $fixtureFactory->createByCode('customerInjectable', ['dataSet' => 'johndoe_unique']);
        $customer->persist();
        $this->customer = $customer;
        $salesRule->persist();
        $this->salesRule = $salesRule;
        self::$salesRuleName = $salesRule->getName();
    }

    /**
     * Injection data
     *
     * @param CmsIndex $cmsIndex
     * @param CheckoutCart $checkoutCart
     * @param CustomerAccountLogin $customerAccountLogin
     * @return void
     */
    public function __inject(
        CmsIndex $cmsIndex,
        CheckoutCart $checkoutCart,
        CustomerAccountLogin $customerAccountLogin
    ) {
        $this->cmsIndex = $cmsIndex;
        $this->checkoutCart = $checkoutCart;
        $this->customerAccountLogin = $customerAccountLogin;
    }

    /**
     * Login customer
     *
     * @return void
     */
    protected function loginCustomer()
    {
        if (!$this->cmsIndex->getLinksBlock()->isLinkVisible('Log Out')) {
            $this->cmsIndex->getLinksBlock()->openLink("Log In");
            $this->customerAccountLogin->getLoginBlock()->login($this->customer);
        }
    }

    /**
     * Clear shopping cart
     *
     * @return void
     */
    protected function clearShoppingCart()
    {
        $this->checkoutCart->open();
        $this->checkoutCart->getCartBlock()->clearShoppingCart();
    }

    /**
     * Test product prices with tax
     *
     * @param CatalogProductSimple $product
     * @param TaxRule $taxRule
     * @param ConfigData $config
     * @return array
     */
    public function test(CatalogProductSimple $product, TaxRule $taxRule, ConfigData $config)
    {
        //Preconditions
        $config->persist();
        $product->persist();
        $taxRule->persist();
        self::$taxRuleCode = $taxRule->getData()['code'];
        //Steps
        $this->cmsIndex->open();
        $this->loginCustomer($this->customer);
        $this->clearShoppingCart();
    }

    /**
     * Tear down after each test
     *
     * @return void
     */
    public function tearDown()
    {
        $taxRuleIndex = ObjectManager::getInstance()->create('\Magento\Tax\Test\Page\Adminhtml\TaxRuleIndex');
        $taxRuleIndex->open();
        $taxRuleIndex->getTaxRuleGrid()->searchAndOpen(['code' => self::$taxRuleCode]);
        $taxRuleNewPage = ObjectManager::getInstance()->create('Magento\Tax\Test\Page\Adminhtml\TaxRuleNew');
        $taxRuleNewPage->getFormPageActions()->delete();
    }

    /**
     * Tear down after tests
     *
     * @return void
     */
    public static function tearDownAfterClass()
    {
        $promoQuoteIndex = ObjectManager::getInstance()
            ->create('Magento\SalesRule\Test\Page\Adminhtml\PromoQuoteIndex');
        $promoQuoteIndex->open();
        $promoQuoteIndex->getPromoQuoteGrid()->searchAndOpen(['name' => self::$salesRuleName]);
        $promoQuoteEdit = ObjectManager::getInstance()
            ->create('Magento\SalesRule\Test\Page\Adminhtml\PromoQuoteEdit');
        $promoQuoteEdit->getFormPageActions()->delete();
        $fixtureFactory = ObjectManager::getInstance()->create('Mtf\Fixture\FixtureFactory');
        $config = $fixtureFactory->createByCode('configData', ['dataSet' => 'default_tax_configuration']);
        $config->persist();
    }
}
