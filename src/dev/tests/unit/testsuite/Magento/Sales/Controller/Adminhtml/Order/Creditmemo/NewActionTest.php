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
namespace Magento\Sales\Controller\Adminhtml\Order\Creditmemo;

/**
 * Class NewActionTest
 */
class NewActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\Creditmemo\NewAction
     */
    protected $controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Backend\App\Action\Context
     */
    protected $contextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader
     */
    protected $creditmemoLoaderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\RequestInterface
     */
    protected $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\ResponseInterface
     */
    protected $responseMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Model\Order\Creditmemo
     */
    protected $creditmemoMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Model\Order\Invoice
     */
    protected $invoiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Action\Title
     */
    protected $titleMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\ObjectManagerInterface
     */
    protected $objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Backend\Model\Session
     */
    protected $backendSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\ViewInterface
     */
    protected $viewMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\LayoutInterface
     */
    protected $layoutMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Element\BlockInterface
     */
    protected $blockMenuMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Backend\Model\Menu
     */
    protected $modelMenuMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Backend\Model\Menu\Item
     */
    protected $modelMenuItem;

    public function setUp()
    {
        $this->contextMock = $this->getMock('Magento\Backend\App\Action\Context', [], [], '', false);
        $this->creditmemoLoaderMock = $this->getMock(
            'Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader',
            ['setOrderId', 'setCreditmemoId', 'setCreditmemo', 'setInvoiceId', 'load'],
            [],
            '',
            false
        );
        $this->creditmemoMock = $this->getMock(
            'Magento\Sales\Model\Order\Creditmemo',
            ['getInvoice', '__wakeup', 'setCommentText'],
            [],
            '',
            false
        );
        $this->invoiceMock = $this->getMock(
            'Magento\Sales\Model\Order\Invoice',
            ['getIncrementId', '__wakeup'],
            [],
            '',
            false
        );
        $this->objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->requestMock = $this->getMockForAbstractClass(
            'Magento\Framework\App\RequestInterface',
            [],
            '',
            false,
            false,
            true,
            []
        );
        $this->responseMock = $this->getMockForAbstractClass(
            'Magento\Framework\App\ResponseInterface',
            [],
            '',
            false,
            false,
            true,
            []
        );
        $this->titleMock = $this->getMock('Magento\Framework\App\Action\Title', [], [], '', false);
        $this->backendSessionMock = $this->getMock('Magento\Backend\Model\Session', ['getCommentText'], [], '', false);
        $this->viewMock = $this->getMockForAbstractClass(
            'Magento\Framework\App\ViewInterface',
            [],
            '',
            false,
            false,
            true,
            []
        );
        $this->layoutMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\LayoutInterface',
            [],
            '',
            false,
            false,
            true,
            []
        );
        $this->blockMenuMock = $this->getMock(
            'Magento\Backend\Block\Menu',
            ['setActive', 'getMenuModel'],
            [],
            '',
            false
        );
        $this->modelMenuMock = $this->getMockBuilder('Magento\Backend\Model\Menu')
            ->disableOriginalConstructor()->getMock();
        $this->modelMenuItem = $this->getMock('Magento\Backend\Model\Menu\Item', [], [], '', false);
        $this->contextMock->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($this->requestMock));
        $this->contextMock->expects($this->once())
            ->method('getResponse')
            ->will($this->returnValue($this->responseMock));
        $this->contextMock->expects($this->once())
            ->method('getTitle')
            ->will($this->returnValue($this->titleMock));
        $this->contextMock->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($this->viewMock));
        $this->contextMock->expects($this->once())
            ->method('getObjectManager')
            ->will($this->returnValue($this->objectManagerMock));
        $this->controller = new NewAction($this->contextMock, $this->creditmemoLoaderMock);
    }

    /**
     *  test execute method
     */
    public function testExecute()
    {
        $this->requestMock->expects($this->exactly(4))
            ->method('getParam')
            ->will($this->returnValueMap([
                ['order_id', null, 'order_id'],
                ['creditmemo_id', null, 'creditmemo_id'],
                ['creditmemo', null, 'creditmemo'],
                ['invoice_id', null, 'invoice_id'],
            ]));
        $this->creditmemoLoaderMock->expects($this->once())
            ->method('setOrderId')
            ->with($this->equalTo('order_id'));
        $this->creditmemoLoaderMock->expects($this->once())
            ->method('setCreditmemoId')
            ->with($this->equalTo('creditmemo_id'));
        $this->creditmemoLoaderMock->expects($this->once())
            ->method('setCreditmemo')
            ->with($this->equalTo('creditmemo'));
        $this->creditmemoLoaderMock->expects($this->once())
            ->method('setInvoiceId')
            ->with($this->equalTo('invoice_id'));
        $this->creditmemoLoaderMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue($this->creditmemoMock));
        $this->creditmemoMock->expects($this->exactly(2))
            ->method('getInvoice')
            ->will($this->returnValue($this->invoiceMock));
        $this->invoiceMock->expects($this->once())
            ->method('getIncrementId')
            ->will($this->returnValue('invoice-increment-id'));
        $this->titleMock->expects($this->exactly(3))
            ->method('add')
            ->will($this->returnValueMap([
                ['Credit Memos', null],
                ['New Memo for #invoice-increment-id', null],
                ['item-title', null],
            ]));
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with($this->equalTo('Magento\Backend\Model\Session'))
            ->will($this->returnValue($this->backendSessionMock));
        $this->backendSessionMock->expects($this->once())
            ->method('getCommentText')
            ->with($this->equalTo(true))
            ->will($this->returnValue('comment'));
        $this->creditmemoMock->expects($this->once())
            ->method('setCommentText')
            ->with($this->equalTo('comment'));
        $this->viewMock->expects($this->once())
            ->method('loadLayout');
        $this->viewMock->expects($this->once())
            ->method('renderLayout');
        $this->viewMock->expects($this->once())
            ->method('getLayout')
            ->will($this->returnValue($this->layoutMock));
        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with($this->equalTo('menu'))
            ->will($this->returnValue($this->blockMenuMock));
        $this->blockMenuMock->expects($this->once())
            ->method('setActive')
            ->with($this->equalTo('Magento_Sales::sales_order'));
        $this->blockMenuMock->expects($this->once())
            ->method('getMenuModel')
            ->will($this->returnValue($this->modelMenuMock));
        $this->modelMenuMock->expects($this->once())
            ->method('getParentItems')
            ->will($this->returnValue([$this->modelMenuItem]));
        $this->modelMenuItem->expects($this->once())
            ->method('getTitle')
            ->will($this->returnValue('item-title'));
        $this->assertNull($this->controller->execute());
    }
}
