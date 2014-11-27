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
namespace Magento\Shipping\Controller\Adminhtml\Order\Shipment;

/**
 * Class RemoveTrackTest
 */
class RemoveTrackTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentLoaderMock;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\Track|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentTrackMock;

    /**
     * @var \Magento\Framework\App\Action\Title|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $titleMock;

    /**
     * @var \Magento\Sales\Model\Order\Shipment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentMock;

    /**
     * @var \Magento\Backend\Model\View|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewMock;

    /**
     * @var \Magento\Framework\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\Shipment\RemoveTrack
     */
    protected $controller;

    protected function setUp()
    {
        $this->requestMock = $this->getMock('Magento\Framework\App\Request\Http', ['getParam'], [], '', false);
        $this->objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->shipmentTrackMock = $this->getMock(
            'Magento\Sales\Model\Order\Shipment\Track',
            ['load', 'getId', 'delete', '__wakeup'],
            [],
            '',
            false
        );
        $this->titleMock = $this->getMock(
            'Magento\Framework\App\Action\Title',
            ['add'],
            [],
            '',
            false
        );
        $this->shipmentMock = $this->getMock(
            'Magento\Sales\Model\Order\Shipment',
            ['getIncrementId', '__wakeup'],
            [],
            '',
            false
        );
        $this->viewMock = $this->getMock(
            'Magento\Backend\Model\View',
            ['loadLayout', 'getLayout'],
            [],
            '',
            false
        );
        $this->responseMock = $this->getMock(
            'Magento\Framework\App\Response\Http',
            [],
            [],
            '',
            false
        );
        $this->shipmentLoaderMock = $this->getMock(
            'Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader',
            ['setOrderId', 'setShipmentId', 'setShipment', 'setTracking', 'load'],
            [],
            '',
            false
        );

        $contextMock = $this->getMock(
            'Magento\Backend\App\Action\Context',
            ['getRequest', 'getObjectManager', 'getTitle', 'getView', 'getResponse'],
            [],
            '',
            false
        );

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with('Magento\Sales\Model\Order\Shipment\Track')
            ->will($this->returnValue($this->shipmentTrackMock));

        $contextMock->expects($this->any())->method('getRequest')->will($this->returnValue($this->requestMock));
        $contextMock->expects($this->any())
            ->method('getObjectManager')
            ->will($this->returnValue($this->objectManagerMock));
        $contextMock->expects($this->any())->method('getTitle')->will($this->returnValue($this->titleMock));
        $contextMock->expects($this->any())->method('getView')->will($this->returnValue($this->viewMock));
        $contextMock->expects($this->any())->method('getResponse')->will($this->returnValue($this->responseMock));

        $this->controller = new \Magento\Shipping\Controller\Adminhtml\Order\Shipment\RemoveTrack(
            $contextMock,
            $this->shipmentLoaderMock
        );
    }

    /**
     * Shipment load sections
     *
     * @return void
     */
    protected function shipmentLoad()
    {
        $orderId = 1;
        $shipmentId = 1;
        $trackId = 1;
        $shipment = [];
        $tracking = [];

        $this->shipmentTrackMock->expects($this->once())
            ->method('load')
            ->with($trackId)
            ->will($this->returnSelf());
        $this->shipmentTrackMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($trackId));
        $this->titleMock->expects($this->once())
            ->method('add')
            ->with('Shipments')
            ->will($this->returnSelf());
        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('track_id')
            ->will($this->returnValue($trackId));
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('order_id')
            ->will($this->returnValue($orderId));
        $this->requestMock->expects($this->at(2))
            ->method('getParam')
            ->with('shipment_id')
            ->will($this->returnValue($shipmentId));
        $this->requestMock->expects($this->at(3))
            ->method('getParam')
            ->with('shipment')
            ->will($this->returnValue($shipment));
        $this->requestMock->expects($this->at(4))
            ->method('getParam')
            ->with('tracking')
            ->will($this->returnValue($tracking));
        $this->shipmentLoaderMock->expects($this->once())->method('setOrderId')->with($orderId);
        $this->shipmentLoaderMock->expects($this->once())->method('setShipmentId')->with($shipmentId);
        $this->shipmentLoaderMock->expects($this->once())->method('setShipment')->with($shipment);
        $this->shipmentLoaderMock->expects($this->once())->method('setTracking')->with($tracking);
    }

    /**
     * Represent json json section
     *
     * @param array $errors
     * @return void
     */
    protected function representJson(array $errors)
    {
        $dataHelper = $this->getMock('Magento\Core\Helper\Data', ['jsonEncode'], [], '', false);
        $dataHelper->expects($this->once())
            ->method('jsonEncode')
            ->with($errors)
            ->will($this->returnValue('{json}'));
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with('Magento\Core\Helper\Data')
            ->will($this->returnValue($dataHelper));
        $this->responseMock->expects($this->once())
            ->method('representJson')
            ->with('{json}');
    }

    /**
     * Run test execute method
     */
    public function testExecute()
    {
        $response = 'html-data';
        $this->shipmentLoad();

        $this->shipmentLoaderMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue($this->shipmentMock));
        $this->shipmentTrackMock->expects($this->once())
            ->method('delete')
            ->will($this->returnSelf());

        $layoutMock = $this->getMock('Magento\Framework\View\Layout', ['getBlock'], [], '', false);
        $trackingBlockMock = $this->getMock(
            'Magento\Shipping\Block\Adminhtml\Order\Tracking',
            ['toHtml'],
            [],
            '',
            false
        );

        $trackingBlockMock->expects($this->once())
            ->method('toHtml')
            ->will($this->returnValue($response));
        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('shipment_tracking')
            ->will($this->returnValue($trackingBlockMock));
        $this->viewMock->expects($this->once())->method('loadLayout')->will($this->returnSelf());
        $this->viewMock->expects($this->any())->method('getLayout')->will($this->returnValue($layoutMock));
        $this->responseMock->expects($this->once())
            ->method('setBody')
            ->with($response);

        $this->assertNull($this->controller->execute());
    }

    /**
     * Run test execute method (fail track load)
     */
    public function testExecuteTrackIdFail()
    {
        $trackId = null;
        $errors = ['error' => true, 'message' => 'Cannot load track with retrieving identifier.'];

        $this->shipmentTrackMock->expects($this->once())
            ->method('load')
            ->with($trackId)
            ->will($this->returnSelf());
        $this->shipmentTrackMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($trackId));
        $this->representJson($errors);

        $this->assertNull($this->controller->execute());
    }

    /**
     * Run test execute method (fail load shipment)
     */
    public function testExecuteShipmentLoadFail()
    {
        $errors = [
            'error' => true,
            'message' => 'Cannot initialize shipment for delete tracking number.'
        ];
        $this->shipmentLoad();

        $this->shipmentLoaderMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue(null));
        $this->representJson($errors);

        $this->assertNull($this->controller->execute());
    }

    /**
     * Run test execute method (delete exception)
     */
    public function testExecuteDeleteFail()
    {
        $errors = ['error' => true, 'message' => 'Cannot delete tracking number.'];
        $this->shipmentLoad();

        $this->shipmentLoaderMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue($this->shipmentMock));
        $this->shipmentTrackMock->expects($this->once())
            ->method('delete')
            ->will($this->throwException(new \Exception()));
        $this->representJson($errors);

        $this->assertNull($this->controller->execute());
    }
}
