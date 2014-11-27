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
namespace Magento\Sales\Block\Order;

class RecentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Block\Order\|Recent
     */
    protected $model;

    /**
     * @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Sales\Model\Resource\Order\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSession;

    /**
     * @var \Magento\Sales\Model\Order\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderConfig;

    public function setUp()
    {
        $this->context = $this->getMock('Magento\Framework\View\Element\Template\Context', [], [], '', false, false);
        $this->orderCollectionFactory = $this->getMock(
            'Magento\Sales\Model\Resource\Order\CollectionFactory',
            ['create'],
            [],
            '',
            false,
            false
        );
        $this->customerSession = $this->getMock(
            'Magento\Customer\Model\Session',
            ['getCustomerId'],
            [],
            '',
            false,
            false
        );
        $this->orderConfig = $this->getMock(
            'Magento\Sales\Model\Order\Config',
            ['getVisibleOnFrontStatuses'],
            [],
            '',
            false,
            false
        );
    }

    public function testConstructMethod()
    {
        $data = [];
        $attribute = ['customer_id', 'status'];
        $customerId = 25;
        $layout = $this->getMock('Magento\Core\Model\Layout', ['getBlock'], [], '', false, false);
        $this->context->expects($this->once())
            ->method('getLayout')
            ->will($this->returnValue($layout));
        $this->customerSession->expects($this->once())
            ->method('getCustomerId')
            ->will($this->returnValue($customerId));

        $statuses = ['pending', 'processing', 'complete'];
        $this->orderConfig->expects($this->once())
            ->method('getVisibleOnFrontStatuses')
            ->will($this->returnValue($statuses));

        $orderCollection = $this->getMock(
            'Magento\Sales\Model\Resource\Order\Collection',
            [
                'addAttributeToSelect',
                'addFieldToFilter',
                'joinAttribute',
                'addAttributeToFilter',
                'addAttributeToSort',
                'setPageSize',
                'load'
            ],
            [],
            '',
            false,
            false
        );
        $orderCollection->expects($this->at(0))
            ->method('addAttributeToSelect')
            ->with($this->equalTo('*'))
            ->will($this->returnSelf());
        $orderCollection->expects($this->at(1))
            ->method('joinAttribute')
            ->with(
                'shipping_firstname',
                'order_address/firstname',
                'shipping_address_id',
                $this->equalTo(null),
                'left'
            )
            ->will($this->returnSelf());
        $orderCollection->expects($this->at(2))
            ->method('joinAttribute')
            ->with(
                'shipping_lastname',
                'order_address/lastname',
                'shipping_address_id',
                $this->equalTo(null),
                'left'
            )
            ->will($this->returnSelf());

        $orderCollection->expects($this->at(3))
            ->method('addAttributeToFilter')
            ->with(
                $attribute[0],
                $this->equalTo($customerId)
            )
            ->will($this->returnSelf());
        $orderCollection->expects($this->at(4))
            ->method('addAttributeToFilter')
            ->with($attribute[1], $this->equalTo(['in' => $statuses]))
            ->will($this->returnSelf());
        $orderCollection->expects($this->at(5))
            ->method('addAttributeToSort')
            ->with('created_at', 'desc')
            ->will($this->returnSelf());
        $orderCollection->expects($this->at(6))
            ->method('setPageSize')
            ->with('5')
            ->will($this->returnSelf());
        $orderCollection->expects($this->at(7))
            ->method('load')
            ->will($this->returnSelf());

        $this->orderCollectionFactory->expects($this->atLeastOnce())
            ->method('create')
            ->will($this->returnValue($orderCollection));

        $this->model = new \Magento\Sales\Block\Order\Recent(
            $this->context,
            $this->orderCollectionFactory,
            $this->customerSession,
            $this->orderConfig,
            $data
        );
        $this->assertEquals($orderCollection, $this->model->getOrders());
    }
}
