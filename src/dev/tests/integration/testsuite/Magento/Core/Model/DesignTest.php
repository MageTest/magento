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
namespace Magento\Core\Model;

class DesignTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Design
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Core\Model\Design'
        );
    }

    public function testLoadChange()
    {
        $this->_model->loadChange(1);
        $this->assertNull($this->_model->getId());
    }

    /**
     * @magentoDataFixture Magento/Core/_files/design_change.php
     */
    public function testChangeDesign()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\App\State')
            ->setAreaCode('frontend');
        $design = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\View\DesignInterface'
        );
        $storeId = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\StoreManagerInterface'
        )->getDefaultStoreView()->getId();
        // fixture design_change
        $designChange = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Core\Model\Design'
        );
        $designChange->loadChange($storeId)->changeDesign($design);
        $this->assertEquals('Magento/plushe', $design->getDesignTheme()->getThemePath());
    }

    public function testCRUD()
    {
        $this->_model->setData(
            array(
                'store_id' => 1,
                'design' => 'Magento/blank',
                'date_from' => date('Y-m-d', strtotime('-1 day')),
                'date_to' => date('Y-m-d', strtotime('+1 day'))
            )
        );
        $this->_model->save();
        $this->assertNotEmpty($this->_model->getId());

        try {
            $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Core\Model\Design');
            $model->loadChange(1);
            $this->assertEquals($this->_model->getId(), $model->getId());

            /* Design change that intersects with existing ones should not be saved, so exception is expected */
            try {
                $model->setId(null);
                $model->save();
                $this->fail('A validation failure is expected.');
            } catch (\Magento\Framework\Model\Exception $e) {
            }

            $this->_model->delete();
        } catch (\Exception $e) {
            $this->_model->delete();
            throw $e;
        }

        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Core\Model\Design');
        $model->loadChange(1);
        $this->assertEmpty($model->getId());
    }

    public function testCollection()
    {
        $collection = $this->_model->getCollection()->joinStore()->addDateFilter();
        /**
         * @todo fix and add addStoreFilter method
         */
        $this->assertEmpty($collection->getItems());
    }

    /**
     * @magentoDataFixture Magento/Core/_files/design_change.php
     * @magentoConfigFixture current_store general/locale/timezone UTC
     */
    public function testLoadChangeCache()
    {
        /** @var \Magento\Framework\Stdlib\DateTime $dateTime */
        $dateTime = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Framework\Stdlib\DateTime');
        $date = $dateTime->now(true);
        $storeId = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\StoreManagerInterface'
        )->getDefaultStoreView()->getId();
        // fixture design_change

        $cacheId = 'design_change_' . md5($storeId . $date);

        /** @var \Magento\Core\Model\Design $design */
        $design = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Core\Model\Design');
        $design->loadChange($storeId, $date);

        $cachedDesign = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\App\CacheInterface'
        )->load(
            $cacheId
        );
        $cachedDesign = unserialize($cachedDesign);

        $this->assertInternalType('array', $cachedDesign);
        $this->assertArrayHasKey('design', $cachedDesign);
        $this->assertEquals($cachedDesign['design'], $design->getDesign());

        $design->setDesign('Magento/blank')->save();

        $design = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Core\Model\Design');
        $design->loadChange($storeId, $date);

        $cachedDesign = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\App\CacheInterface'
        )->load(
            $cacheId
        );
        $cachedDesign = unserialize($cachedDesign);

        $this->assertTrue(is_array($cachedDesign));
        $this->assertEquals($cachedDesign['design'], $design->getDesign());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Core/_files/design_change_timezone.php
     * @dataProvider loadChangeTimezoneDataProvider
     */
    public function testLoadChangeTimezone($storeCode, $storeTimezone, $storeUtcOffset)
    {
        if (date_default_timezone_get() != 'UTC') {
            $this->markTestSkipped('Test requires UTC to be the default timezone.');
        }
        $utcDatetime = time();
        $utcDate = date('Y-m-d', $utcDatetime);
        $storeDatetime = strtotime($storeUtcOffset, $utcDatetime);
        $storeDate = date('Y-m-d', $storeDatetime);

        if ($storeDate == $utcDate) {
            $expectedDesign = "{$storeCode}_today_design";
        } else {
            if ($storeDatetime > $utcDatetime) {
                $expectedDesign = "{$storeCode}_tomorrow_design";
            } else {
                $expectedDesign = "{$storeCode}_yesterday_design";
            }
        }

        $store = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\StoreManagerInterface'
        )->getStore(
            $storeCode
        );
        $defaultTimeZonePath = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\Stdlib\DateTime\TimezoneInterface'
        )->getDefaultTimezonePath();
        $store->setConfig($defaultTimeZonePath, $storeTimezone);
        $storeId = $store->getId();

        /** @var $locale \Magento\Framework\Stdlib\DateTime\TimezoneInterface */
        $locale = $this->getMock('Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $locale->expects(
            $this->once()
        )->method(
            'scopeTimeStamp'
        )->with(
            $storeId
        )->will(
            $this->returnValue($storeDatetime)
        );
        // store time must stay unchanged during test execution
        $design = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Core\Model\Design',
            array('localeDate' => $locale)
        );
        $design->loadChange($storeId);
        $actualDesign = $design->getDesign();

        $this->assertEquals($expectedDesign, $actualDesign);
    }

    public function loadChangeTimezoneDataProvider()
    {
        /**
         * Depending on the current UTC time, either UTC-12:00, or UTC+12:00 timezone points to the different date.
         * If UTC time is between 00:00 and 12:00, UTC+12:00 points to the same day, and UTC-12:00 to the previous day.
         * If UTC time is between 12:00 and 24:00, UTC-12:00 points to the same day, and UTC+12:00 to the next day.
         * Testing the design change with both UTC-12:00 and UTC+12:00 store timezones guarantees
         * that the proper design change is chosen for the timezone with the date different from the UTC.
         */
        return array(
            'default store - UTC+12:00' => array('default', 'Etc/GMT-12', '+12 hours'),
            'default store - UTC-12:00' => array('default', 'Etc/GMT+12', '-12 hours'),
            'admin store - UTC+12:00' => array('admin', 'Etc/GMT-12', '+12 hours'),
            'admin store - UTC-12:00' => array('admin', 'Etc/GMT+12', '-12 hours')
        );
    }
}
