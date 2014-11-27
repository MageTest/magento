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
namespace Magento\SalesRule\Model\Resource\Report;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SalesRule\Model\Resource\Report\Collection
     */
    private $_collection;

    protected function setUp()
    {
        $this->_collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\SalesRule\Model\Resource\Report\Collection'
        );
        $this->_collection->setPeriod('day')->setDateRange(null, null)->addStoreFilter(array(1));
    }

    /**
     * @magentoDataFixture Magento/SalesRule/_files/order_with_coupon.php
     * @magentoDataFixture Magento/SalesRule/_files/report_coupons.php
     */
    public function testGetItems()
    {
        $expectedResult = array(array('coupon_code' => '1234567890', 'coupon_uses' => 1));
        $actualResult = array();
        /** @var \Magento\Reports\Model\Item $reportItem */
        foreach ($this->_collection->getItems() as $reportItem) {
            $actualResult[] = array_intersect_key($reportItem->getData(), $expectedResult[0]);
        }
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @dataProvider periodDataProvider
     * @magentoDataFixture Magento/SalesRule/_files/order_with_coupon.php
     * @magentoDataFixture Magento/SalesRule/_files/report_coupons.php
     *
     * @param $period
     * @param $expectedPeriod
     * @param $dateFrom
     * @param $dateTo
     */
    public function testPeriod($period, $dateFrom, $dateTo, $expectedPeriod)
    {
        $this->_collection->setPeriod($period);
        $this->_collection->setDateRange($dateFrom, $dateTo);
        $items = $this->_collection->getItems();
        $this->assertCount(1, $items);
        $this->assertEquals($expectedPeriod, $items[0]->getPeriod());
    }

    /**
     * Data provider for testTableSelection
     *
     * @return array
     */
    public function periodDataProvider()
    {
        return array(
            [
                'period'    => 'year',
                'date_from' => null,
                'date_to'   => null,
                'expected_period' => date('Y', time())
            ],
            [
                'period'    => 'month',
                'date_from' => null,
                'date_to'   => null,
                'expected_period' => date('Y-m', time())
            ],
            [
                'period'    => 'day',
                'date_from' => null,
                'date_to'   => null,
                'expected_period' => $this->getNow()
            ],
            [
                'period'    => 'undefinedPeriod',
                'date_from' => null,
                'date_to'   => null,
                'expected_period' => $this->getNow()
            ],
            [
                'period'    => null,
                'date_from' => date('Y-m-d', strtotime('-1 year', time())),
                'date_to'   => date('Y-m-d', time()),
                'expected_period' => $this->getNow()
            ]
        );
    }

    /**
     * Retrieve date in MySQL timezone
     *
     * @return string
     */
    protected function getNow()
    {
        /** @var \Magento\Framework\App\Resource $resources */
        $resources = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\App\Resource'
        );
        $connection = $resources->getConnection('salesrule_read');
        $now = $connection->fetchOne("SELECT CURDATE()");
        return $now;
    }
}
