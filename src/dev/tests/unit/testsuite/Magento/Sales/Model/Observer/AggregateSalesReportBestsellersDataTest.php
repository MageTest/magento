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
namespace Magento\Sales\Model\Observer;

/**
 * Tests Magento\Sales\Model\Observer\AggregateSalesReportBestsellersDataTest
 */
class AggregateSalesReportBestsellersDataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Locale\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeResolverMock;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeDateMock;

    /**
     * @var \Magento\Sales\Model\Resource\Report\BestsellersFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bestsellersFactoryMock;

    /**
     * @var \Magento\Sales\Model\Observer\AggregateSalesReportBestsellersData
     */
    protected $observer;

    protected function setUp()
    {
        $this->localeResolverMock = $this->getMockBuilder('Magento\Framework\Locale\ResolverInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->bestsellersFactoryMock = $this->getMockBuilder('Magento\Sales\Model\Resource\Report\BestsellersFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->localeDateMock = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime\TimezoneInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->observer = new AggregateSalesReportBestsellersData(
            $this->localeResolverMock,
            $this->localeDateMock,
            $this->bestsellersFactoryMock
        );
    }


    public function testExecute()
    {
        $date = $this->setupAggregate();
        $bestsellersMock = $this->getMockBuilder('Magento\Sales\Model\Resource\Report\Bestsellers')
            ->disableOriginalConstructor()
            ->getMock();
        $bestsellersMock->expects($this->once())
            ->method('aggregate')
            ->with($date);
        $this->bestsellersFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($bestsellersMock));
        $this->observer->execute();
    }

    /**
     * Set up aggregate
     *
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface
     */
    protected function setupAggregate()
    {
        $date = $this->getMock('Magento\Framework\Stdlib\DateTime\Date', ['emulate', 'revert'], [], '', false);
        $this->localeResolverMock->expects($this->once())
            ->method('emulate')
            ->with(0);
        $this->localeResolverMock->expects($this->once())
            ->method('revert');
        $dateMock = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime\DateInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $dateMock->expects($this->once())
            ->method('subHour')
            ->with(25)
            ->will($this->returnValue($date));
        $this->localeDateMock->expects($this->once())
            ->method('date')
            ->will($this->returnValue($dateMock));
        return $date;
    }
}
