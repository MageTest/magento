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

namespace Magento\Reports\Test\Block\Adminhtml\Review\Customer;

use Magento\Customer\Test\Fixture\CustomerInjectable;
use Mtf\Client\Element\Locator;
use Magento\Backend\Test\Block\Widget\Grid as AbstractGrid;

/**
 * Class Grid
 * Customer Report Review grid
 */
class Grid extends AbstractGrid
{
    /**
     * Search product reviews report row selector
     *
     * @var string
     */
    protected $searchRow = '//tr[td[contains(.,"%s")]]/td';

    /**
     * Search product reviews report row selector
     *
     * @var string
     */
    protected $colReviewCount = '//tr[td[contains(.,"%s")]]/td[@data-column="review_cnt"]';

    /**
     * Open customer review report
     *
     * @param CustomerInjectable $customer
     * @return void
     */
    public function openReview(CustomerInjectable $customer)
    {
        $customerName = $customer->getFirstName() . ' ' . $customer->getLastName();
        $this->_rootElement->find(sprintf($this->searchRow, $customerName), Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Get qty review from customer review grid
     *
     * @param string $customerName
     * @return int
     */
    public function getQtyReview($customerName)
    {
        return $this->_rootElement
            ->find(sprintf($this->colReviewCount, $customerName), Locator::SELECTOR_XPATH)->getText();
    }
}
