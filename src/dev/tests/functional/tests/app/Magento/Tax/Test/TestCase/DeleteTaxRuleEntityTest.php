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

use Magento\Customer\Test\Fixture\AddressInjectable;
use Magento\Tax\Test\Fixture\TaxRule;
use Magento\Tax\Test\Page\Adminhtml\TaxRuleIndex;
use Magento\Tax\Test\Page\Adminhtml\TaxRuleNew;
use Mtf\Fixture\FixtureFactory;
use Mtf\TestCase\Injectable;

/**
 * Test Creation for Delete TaxRuleEntity
 *
 * Test Flow:
 * Preconditions:
 * 1. Tax Rule is created
 *
 * Steps:
 * 1. Log in as default admin user.
 * 2. Go to Sales > Tax Rules
 * 3. Select required tax rule from preconditions
 * 4. Click on the "Delete Rule" button
 * 5. Perform all assertions
 *
 * @group Tax_(CS)
 * @ZephyrId MAGETWO-20924
 */
class DeleteTaxRuleEntityTest extends Injectable
{
    /**
     * Tax Rule grid page
     *
     * @var TaxRuleIndex
     */
    protected $taxRuleIndexPage;

    /**
     * Tax Rule new and edit page
     *
     * @var TaxRuleNew
     */
    protected $taxRuleNewPage;

    /**
     * Preparing data
     *
     * @param FixtureFactory $fixtureFactory
     * @return array
     */
    public function __prepare(FixtureFactory $fixtureFactory)
    {
        $customer = $fixtureFactory->createByCode('customerInjectable', ['dataSet' => 'default']);
        $customer->persist();

        return ['customer' => $customer];
    }

    /**
     * Injection data
     *
     * @param TaxRuleIndex $taxRuleIndexPage
     * @param TaxRuleNew $taxRuleNewPage
     */
    public function __inject(
        TaxRuleIndex $taxRuleIndexPage,
        TaxRuleNew $taxRuleNewPage
    ) {
        $this->taxRuleIndexPage = $taxRuleIndexPage;
        $this->taxRuleNewPage = $taxRuleNewPage;
    }

    /**
     * Delete Tax Rule Entity test
     *
     * @param TaxRule $taxRule
     * @param AddressInjectable $address
     * @param array $shipping
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testDeleteTaxRule(
        TaxRule $taxRule,
        AddressInjectable $address,
        array $shipping
    ) {
        // Precondition
        $taxRule->persist();

        // Steps
        $filters = [
            'code' => $taxRule->getCode(),
        ];
        $this->taxRuleIndexPage->open();
        $this->taxRuleIndexPage->getTaxRuleGrid()->searchAndOpen($filters);
        $this->taxRuleNewPage->getFormPageActions()->delete();
    }
}
