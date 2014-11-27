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

namespace Magento\Shipping\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;

/**
 * Class ShipmentLoaderTest
 *
 * @package Magento\Shipping\Controller\Adminhtml\Order
 */
class ShipmentLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderServiceFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $trackFactoryMock;

    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader
     */
    protected $loader;

    public function setUp()
    {
        $this->shipmentFactoryMock = $this->getMockBuilder('Magento\Sales\Model\Order\ShipmentFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->registryMock = $this->getMockBuilder('Magento\Framework\Registry')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->orderFactoryMock = $this->getMockBuilder('Magento\Sales\Model\OrderFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->orderServiceFactoryMock = $this->getMockBuilder('Magento\Sales\Model\Service\OrderFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->trackFactoryMock = $this->getMockBuilder('Magento\Sales\Model\Order\Shipment\TrackFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder('Magento\Framework\Message\Manager')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $data = [
            'order_id' => 100032,
            'shipment_id' => 1000065,
            'shipment' =>
                ['items' => [1 => 1, 2 => 2]],
            'tracking' => [
                ['number' => 'jds0395'],
                ['number' => 'lsk984g']
            ]
        ];

        $this->loader = new \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader(
            $this->messageManagerMock,
            $this->registryMock,
            $this->shipmentFactoryMock,
            $this->orderFactoryMock,
            $this->orderServiceFactoryMock,
            $this->trackFactoryMock,
            $data
        );
    }

    public function testLoadShipmentId()
    {
        $shipmentModelMock = $this->getMockBuilder('Magento\Sales\Model\Order\Shipment')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->shipmentFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($shipmentModelMock));
        $shipmentModelMock->expects($this->once())
            ->method('load')
            ->with($this->loader->getShipmentId())
            ->will($this->returnSelf());
        $this->registryMock->expects($this->once())
            ->method('register')
            ->with('current_shipment', $shipmentModelMock);
        $this->assertEquals($shipmentModelMock, $this->loader->load());
    }

    public function testLoadOrderId()
    {
        $this->loader->unsetData('shipment_id');
        $orderMock = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->orderFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($orderMock));
        $orderMock->expects($this->once())
            ->method('load')
            ->with($this->loader->getOrderId())
            ->will($this->returnSelf());
        $orderMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($this->loader->getOrderId()));
        $orderMock->expects($this->any())
            ->method('getForcedShipmentWithInvoice')
            ->will($this->returnValue(true));
        $orderMock->expects($this->once())
            ->method('canShip')
            ->will($this->returnValue(true));
        $orderServiceMock = $this->getMockBuilder('Magento\Sales\Model\Service\Order')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $shipmentModelMock = $this->getMockBuilder('Magento\Sales\Model\Order\Shipment')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->orderServiceFactoryMock->expects($this->once())
            ->method('create')
            ->with(['order' => $orderMock])
            ->will($this->returnValue($orderServiceMock));
        $orderServiceMock->expects($this->once())
            ->method('prepareShipment')
            ->with($this->loader->getShipment()['items'])
            ->will($this->returnValue($shipmentModelMock));
        $trackMock = $this->getMockBuilder('Magento\Sales\Model\Order\Shipment\Track')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->trackFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($trackMock));
        $trackMock->expects($this->any())
            ->method('addData')
            ->will(
                $this->returnValueMap(
                    [
                        [$this->loader->getTracking()[0], $trackMock],
                        [$this->loader->getTracking()[1], $trackMock]
                    ]
                )
            );
        $shipmentModelMock->expects($this->any())
            ->method('addTrack')
            ->with($this->equalTo($trackMock))
            ->will($this->returnSelf());
        $this->registryMock->expects($this->once())
            ->method('register')
            ->with('current_shipment', $shipmentModelMock);

        $this->assertEquals($shipmentModelMock, $this->loader->load());
    }
}
 