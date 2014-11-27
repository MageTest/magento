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

namespace Magento\Tax\Test\Constraint;

/**
 * Class AssertTaxRuleIsApplied
 */
class AssertTaxRuleIsApplied extends AssertTaxRuleApplying
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'high';

    /**
     * Assert that tax rule is applied on product in shopping cart.
     *
     * @return void
     */
    protected function assert()
    {
        $errorMessages = [];

        // Preparing data to compare
        $taxRate = $this->taxRule->getDataFieldConfig('tax_rate')['source']->getFixture()[0]->getRate();
        $expectedGrandTotal = $this->productSimple->getPrice() + $taxRate + $this->shipping['price'];
        $expectedGrandTotal = number_format($expectedGrandTotal, 2);
        $actualGrandTotal = $this->checkoutCart->getTotalsBlock()->getGrandTotal();

        if ($this->checkoutCart->getTotalsBlock()->isTaxVisible()) {
            $expectedTax = number_format($taxRate, 2);
            $actualTax = $this->checkoutCart->getTotalsBlock()->getTax();
            if ($expectedTax !== $actualTax) {
                $errorMessages[] = 'Tax Rule \'' . $this->taxRuleCode . '\' is applied wrong.'
                    . "\nExpected: " . $expectedTax
                    . "\nActual: " . $actualTax;
            }
        }

        if ($expectedGrandTotal !== $actualGrandTotal) {
            $errorMessages[] = 'Grand Total is not correct.'
                . "\nExpected: " . $expectedGrandTotal
                . "\nActual: " . $actualGrandTotal;
        }

        \PHPUnit_Framework_Assert::assertTrue(empty($errorMessages), implode(";\n", $errorMessages));
    }

    /**
     * Text of Tax Rule is applied on product in shopping cart.
     *
     * @return string
     */
    public function toString()
    {
        return "Tax rule applied on product in shopping cart.";
    }
}
