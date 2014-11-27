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

namespace Magento\Reports\Test\TestCase;

use Magento\Reports\Test\Page\Adminhtml\SalesReport;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Mtf\TestCase\Injectable;

/**
 * Test Creation for SalesOrderReportEntity
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Open Backend
 * 2. Go to Reports > Sales > Orders
 * 3. Refresh statistic
 * 4. Configure filter
 * 5. Click "Show Report"
 * 6. Save/remember report result
 * 7. Create customer
 * 8. Place order
 * 9. Create Invoice
 * 10. Refresh statistic
 *
 * Steps:
 * 1. Open Backend
 * 2. Go to Reports > Sales > Orders
 * 3. Configure filter
 * 4. Click "Show Report"
 * 5. Perform all assertions
 *
 * @group Reports_(MX)
 * @ZephyrId MAGETWO-29136
 */
class SalesOrderReportEntityTest extends Injectable
{
    /**
     * Sales Report page
     *
     * @var SalesReport
     */
    protected $salesReport;

    /**
     * Inject page
     *
     * @param SalesReport $salesReport
     * @return void
     */
    public function __inject(SalesReport $salesReport)
    {
        $this->salesReport = $salesReport;
    }

    /**
     * Sales order report
     *
     * @param OrderInjectable $order
     * @param array $salesReport
     * @return array
     */
    public function test(OrderInjectable $order, array $salesReport)
    {
        // Preconditions
        $this->salesReport->open();
        $this->salesReport->getMessagesBlock()->clickLinkInMessages('notice', 'here');
        $this->salesReport->getFilterBlock()->viewsReport($salesReport);
        $this->salesReport->getActionBlock()->showReport();
        $initialSalesResult = $this->salesReport->getGridBlock()->getLastResult();
        $initialSalesTotalResult = $this->salesReport->getGridBlock()->getTotalResult();

        $order->persist();
        $invoice = $this->objectManager->create('Magento\Sales\Test\TestStep\CreateInvoiceStep', ['order' => $order]);
        $invoice->run();

        return ['initialSalesResult' => $initialSalesResult, 'initialSalesTotalResult' => $initialSalesTotalResult];
    }
}
