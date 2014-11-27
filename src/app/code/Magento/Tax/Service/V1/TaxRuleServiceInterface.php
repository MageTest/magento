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

namespace Magento\Tax\Service\V1;

/**
 * Interface TaxRuleServiceInterface
 */
interface TaxRuleServiceInterface
{
    /**
     * Create TaxRule
     *
     * @param \Magento\Tax\Service\V1\Data\TaxRule $rule
     * @return \Magento\Tax\Service\V1\Data\TaxRule
     * @throws \Magento\Framework\Exception\InputException If input is invalid or required input is missing.
     * @throws \Exception If something went wrong while performing the update.
     */
    public function createTaxRule(\Magento\Tax\Service\V1\Data\TaxRule $rule);

    /**
     * Update TaxRule
     *
     * @param \Magento\Tax\Service\V1\Data\TaxRule $rule
     * @return bool
     * @throws \Magento\Framework\Exception\InputException If input is invalid or required input is missing.
     * @throws \Magento\Framework\Exception\NoSuchEntityException If the TaxRule to update can't be found in the system.
     * @throws \Exception If something went wrong while performing the update.
     */
    public function updateTaxRule(\Magento\Tax\Service\V1\Data\TaxRule $rule);

    /**
     * Delete TaxRule
     *
     * @param int $ruleId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException If no TaxRate with the given ID can be found.
     * @throws \Exception If something went wrong while performing the delete.
     */
    public function deleteTaxRule($ruleId);

    /**
     * Get TaxRule
     *
     * @param int $ruleId
     * @return \Magento\Tax\Service\V1\Data\TaxRule
     */
    public function getTaxRule($ruleId);

    /**
     * Search TaxRules
     *
     * @param \Magento\Framework\Api\SearchCriteria $searchCriteria
     * @return \Magento\Tax\Service\V1\Data\TaxRuleSearchResults containing Data\TaxRule objects
     * @throws \Magento\Framework\Exception\InputException If there is a problem with the input
     */
    public function searchTaxRules(\Magento\Framework\Api\SearchCriteria $searchCriteria);

    /**
     * Get rates by customerTaxClassId and productTaxClassId
     *
     * @param int $customerTaxClassId
     * @param int $productTaxClassId
     * @return \Magento\Tax\Service\V1\Data\TaxRate[]
     */
    public function getRatesByCustomerAndProductTaxClassId($customerTaxClassId, $productTaxClassId);
}
