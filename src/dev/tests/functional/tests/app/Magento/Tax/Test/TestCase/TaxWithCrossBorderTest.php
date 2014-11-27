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
use Magento\SalesRule\Test\Fixture\SalesRuleInjectable;
use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\CatalogRule\Test\Fixture\CatalogRule;
use Mtf\ObjectManager;
use Magento\Core\Test\Fixture\ConfigData;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\SalesRule\Test\Page\Adminhtml\PromoQuoteIndex;
use Magento\SalesRule\Test\Page\Adminhtml\PromoQuoteEdit;
use Magento\CatalogRule\Test\Page\Adminhtml\CatalogRuleIndex;
use Magento\CatalogRule\Test\Page\Adminhtml\CatalogRuleNew;

/**
 * Test TaxWithCrossBorderTest
 *
 * Test Flow:
 * Steps:
 * 1. Log in as default admin user.
 * 2. Go to Stores > Taxes > Tax Rules.
 * 3. Click 'Add New Tax Rule' button.
 * 4. Assign 3 different rates for different addresses
 * 5. Save Tax Rate.
 * 6. Go to Products > Catalog.
 * 7. Add new product.
 * 8. Fill data according to dataset.
 * 9. Save product.
 * 10. Go to Stores > Configuration.
 * 11. Fill Tax configuration according to data set.
 * 12. Save tax configuration.
 * 13. Register two customers on front end that will match two different rates
 * 14. Login with each customer and verify prices
 *
 * @group Tax_(CS)
 * @ZephyrId MAGETWO-29052
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TaxWithCrossBorderTest extends Injectable
{
    /**
     * Fixture SalesRule
     *
     * @var SalesRuleInjectable
     */
    protected $salesRule;

    /**
     * Fixture SalesRule
     *
     * @var CatalogRule
     */
    protected $catalogRule;

    /**
     * Fixture factory
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * PromoQuoteIndex
     *
     * @var PromoQuoteIndex
     */
    protected $promoQuoteIndex;

    /**
     * PromoQuoteEdit
     *
     * @var PromoQuoteEdit
     */
    protected $promoQuoteEdit;

    /**
     * CatalogRuleIndex
     *
     * @var CatalogRuleIndex
     */
    protected $catalogRuleIndex;

    /**
     * CatalogRuleNew
     *
     * @var CatalogRuleNew
     */
    protected $catalogRuleNew;

    /**
     * Prepare data
     *
     * @param FixtureFactory $fixtureFactory
     * @return array
     */
    public function __prepare(FixtureFactory $fixtureFactory)
    {
        $this->fixtureFactory = $fixtureFactory;
        $taxRule = $fixtureFactory->createByCode('taxRule', ['dataSet' => 'cross_border_tax_rule']);
        $taxRule->persist();
        return ['customers' => $this->createCustomers()];
    }

    /**
     * Injection data
     *
     * @param PromoQuoteIndex $promoQuoteIndex
     * @param PromoQuoteEdit $promoQuoteEdit
     * @param CatalogRuleIndex $catalogRuleIndex
     * @param CatalogRuleNew $catalogRuleNew
     * @return void
     */
    public function __inject(
        PromoQuoteIndex $promoQuoteIndex,
        PromoQuoteEdit $promoQuoteEdit,
        CatalogRuleIndex $catalogRuleIndex,
        CatalogRuleNew $catalogRuleNew
    ) {
        $this->promoQuoteIndex = $promoQuoteIndex;
        $this->promoQuoteEdit = $promoQuoteEdit;
        $this->catalogRuleIndex = $catalogRuleIndex;
        $this->catalogRuleNew = $catalogRuleNew;
    }

    /**
     * Create customers
     *
     * @return array $customers
     */
    protected function createCustomers()
    {
        $customersData = ['johndoe_unique_TX', 'johndoe_unique'];
        $customers = [];
        foreach ($customersData as $customerData) {
            $customer = $this->fixtureFactory->createByCode('customerInjectable', ['dataSet' => $customerData]);
            $customer->persist();
            $customers[] = $customer;
        }
        return $customers;
    }

    /**
     * Test product prices with tax
     *
     * @param CatalogProductSimple $product
     * @param ConfigData $config
     * @param SalesRuleInjectable $salesRule
     * @param CatalogRule $catalogRule
     * @return void
     */
    public function test(
        CatalogProductSimple $product,
        ConfigData $config,
        SalesRuleInjectable $salesRule,
        CatalogRule $catalogRule
    ) {
        //Preconditions
        if ($this->currentVariation['arguments']['salesRule']['dataSet'] !== "-") {
            $salesRule->persist();
            $this->salesRule = $salesRule;
        }
        if ($this->currentVariation['arguments']['catalogRule']['dataSet'] !== "-") {
            $catalogRule->persist();
            $this->catalogRule = $catalogRule;
        }
        $config->persist();
        $product->persist();
    }

    /**
     * Tear down after test
     *
     * @return void
     */
    public function tearDown()
    {
        if (isset($this->salesRule)) {
            $this->promoQuoteIndex->open();
            $this->promoQuoteIndex->getPromoQuoteGrid()->searchAndOpen(['name' => $this->salesRule->getName()]);
            $this->promoQuoteEdit->getFormPageActions()->delete();
            $this->salesRule = null;
        }
        if (isset($this->catalogRule)) {
            $this->catalogRuleIndex->open();
            $this->catalogRuleIndex->getCatalogRuleGrid()->searchAndOpen(['name' => $this->catalogRule->getName()]);
            $this->catalogRuleNew->getFormPageActions()->delete();
            $this->catalogRule = null;
        }
    }

    /**
     * Tear down after tests
     *
     * @return void
     */
    public static function tearDownAfterClass()
    {
        ObjectManager::getInstance()->create('\Magento\Tax\Test\TestStep\DeleteAllTaxRulesStep')->run();
        ObjectManager::getInstance()->create(
            'Magento\Core\Test\TestStep\SetupConfigurationStep',
            ['configData' => 'default_tax_configuration']
        )->run();
    }
}
