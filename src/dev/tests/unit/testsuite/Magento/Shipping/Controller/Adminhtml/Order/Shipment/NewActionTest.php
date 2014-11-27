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

use \Magento\Backend\App\Action;
use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class NewActionTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NewActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentLoader;

    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\Shipment\NewAction
     */
    protected $newAction;

    /**
     * @var Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $response;

    /**
     * @var \Magento\Framework\Message\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * @var \Magento\Backend\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $session;

    /**
     * @var \Magento\Framework\App\ActionFlag|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $actionFlag;

    /**
     * @var \Magento\Backend\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Action\Title|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $title;

    /**
     * @var  \Magento\Framework\App\ViewInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $view;

    public function setUp()
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->shipmentLoader = $this->getMockBuilder('Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->labelGenerator = $this->getMockBuilder('Magento\Shipping\Model\Shipping\LabelGenerator')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->shipmentSender = $this->getMockBuilder('Magento\Sales\Model\Order\Email\Sender\ShipmentSender')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->context = $this->getMockBuilder('Magento\Backend\App\Action\Context')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->context = $this->getMock(
            'Magento\Backend\App\Action\Context',
            [
                'getRequest', 'getResponse', 'getMessageManager', 'getRedirect', 'getObjectManager',
                'getSession', 'getActionFlag', 'getHelper', 'getTitle', 'getView'
            ],
            [],
            '',
            false
        );
        $this->response = $this->getMock(
            'Magento\Framework\App\ResponseInterface',
            ['setRedirect', 'sendResponse'],
            [],
            '',
            false
        );
        $this->request = $this->getMock(
            'Magento\Framework\App\RequestInterface',
            ['isPost', 'getModuleName', 'setModuleName', 'getActionName', 'setActionName', 'getParam', 'getCookie'],
            [],
            '',
            false
        );
        $this->messageManager = $this->getMock(
            'Magento\Framework\Message\Manager',
            ['addSuccess', 'addError'],
            [],
            '',
            false
        );
        $this->session = $this->getMock(
            'Magento\Backend\Model\Session',
            ['setIsUrlNotice', 'getCommentText'],
            [],
            '',
            false
        );
        $this->actionFlag = $this->getMock('Magento\Framework\App\ActionFlag', ['get'], [], '', false);
        $this->helper = $this->getMock('Magento\Backend\Helper\Data', ['getUrl'], [], '', false);
        $this->title = $this->getMock('Magento\Framework\App\Action\Title', [], [], '', false);
        $this->view = $this->getMock('Magento\Framework\App\ViewInterface', [], [], '', false);
        $this->context->expects($this->once())
            ->method('getMessageManager')
            ->will($this->returnValue($this->messageManager));
        $this->context->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($this->request));
        $this->context->expects($this->once())
            ->method('getResponse')
            ->will($this->returnValue($this->response));
        $this->context->expects($this->once())
            ->method('getObjectManager')
            ->will($this->returnValue($this->objectManager));
        $this->context->expects($this->once())
            ->method('getSession')
            ->will($this->returnValue($this->session));
        $this->context->expects($this->once())
            ->method('getActionFlag')
            ->will($this->returnValue($this->actionFlag));
        $this->context->expects($this->once())
            ->method('getHelper')
            ->will($this->returnValue($this->helper));
        $this->context->expects($this->once())->method('getTitle')->will($this->returnValue($this->title));
        $this->context->expects($this->once())->method('getView')->will($this->returnValue($this->view));
        $this->newAction = $objectManagerHelper->getObject(
            'Magento\Shipping\Controller\Adminhtml\Order\Shipment\NewAction',
            [
                'context' => $this->context, 'shipmentLoader' => $this->shipmentLoader, 'request' => $this->request,
                'response' => $this->response, 'title' => $this->title, 'view' => $this->view
            ]
        );
    }

    public function testExecute()
    {
        $shipmentId = 1000012;
        $orderId = 10003;
        $tracking = [];
        $shipmentData = ['items' => [], 'send_email' => ''];
        $shipment = $this->getMock(
            'Magento\Sales\Model\Order\Shipment',
            ['load', 'save', 'register', 'getOrder', 'getOrderId', '__wakeup'],
            [],
            '',
            false
        );
        $this->request->expects($this->any())
            ->method('getParam')
            ->will(
                $this->returnValueMap(
                    [
                        ['order_id', null, $orderId],
                        ['shipment_id', null, $shipmentId],
                        ['shipment', null, $shipmentData],
                        ['tracking', null, $tracking]
                    ]
                )
            );
        $this->shipmentLoader->expects($this->any())
            ->method('setShipmentId')
            ->with($shipmentId);
        $this->shipmentLoader->expects($this->any())
            ->method('setOrderId')
            ->with($orderId);
        $this->shipmentLoader->expects($this->any())
            ->method('setShipment')
            ->with($shipmentData);
        $this->shipmentLoader->expects($this->any())
            ->method('setTracking')
            ->with($tracking);
        $this->shipmentLoader->expects($this->once())
            ->method('load')
            ->will($this->returnValue($shipment));
        $this->session->expects($this->once())
            ->method('getCommentText')
            ->with(true)
            ->will($this->returnValue(''));
        $this->objectManager->expects($this->atLeastOnce())
            ->method('get')
            ->with('Magento\Backend\Model\Session')
            ->will($this->returnValue($this->session));
        $this->view->expects($this->once())
            ->method('loadLayout')
            ->will($this->returnSelf());
        $this->view->expects($this->once())
            ->method('renderLayout')
            ->will($this->returnSelf());
        $layout = $this->getMock('Magento\Framework\View\Layout\Element\Layout', ['getBlock'], [], '', false);
        $menuBlock = $this->getMock(
            'Magento\Framework\View\Element\BlockInterface',
            ['toHtml', 'setActive', 'getMenuModel'],
            [],
            '',
            false
        );
        $menuModel = $this->getMockBuilder('Magento\Backend\Model\Menu')
            ->disableOriginalConstructor()->getMock();
        $itemId = 'Magento_Sales::sales_order';
        $parents = [
            new \Magento\Framework\Object(['title' => 'title1']),
            new \Magento\Framework\Object(['title' => 'title2']),
            new \Magento\Framework\Object(['title' => 'title3'])
        ];
        $menuModel->expects($this->once())
            ->method('getParentItems')
            ->with($itemId)
            ->will($this->returnValue($parents));
        $menuBlock->expects($this->once())
            ->method('setActive')
            ->with($itemId);
        $menuBlock->expects($this->once())
            ->method('getMenuModel')
            ->will($this->returnValue($menuModel));
        $this->view->expects($this->once())
            ->method('getLayout')
            ->will($this->returnValue($layout));
        $layout->expects($this->once())
            ->method('getBlock')
            ->with('menu')
            ->will($this->returnValue($menuBlock));

        $this->title->expects($this->any())
            ->method('add')
            ->will($this->returnValueMap(
                    ['Shipments', false, $this->title],
                    [$parents[0]->getData('title'), true, $this->title],
                    [$parents[1]->getData('title'), true, $this->title],
                    [$parents[2]->getData('title'), true, $this->title]
                ));

        $this->assertNull($this->newAction->execute());
    }
}
