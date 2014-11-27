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
namespace Magento\Backend\Controller\Adminhtml\Cache;

class CleanMediaTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        // Wire object with mocks
        $response = $this->getMock('Magento\Framework\App\Response\Http', array(), array(), '', false);
        $request = $this->getMock('Magento\Framework\App\Request\Http', array(), array(), '', false);

        $objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $backendHelper = $this->getMock('Magento\Backend\Helper\Data', array(), array(), '', false);
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $session = $this->getMock(
            'Magento\Backend\Model\Session',
            array('setIsUrlNotice'),
            $helper->getConstructArguments('Magento\Backend\Model\Session')
        );
        $messageManager = $this->getMock(
            'Magento\Framework\Message\Manager',
            array('addSuccess'),
            $helper->getConstructArguments('Magento\Framework\Message\Manager')
        );
        $context = $this->getMock(
            'Magento\Backend\App\Action\Context',
            array('getRequest', 'getResponse', 'getMessageManager', 'getSession'),
            $helper->getConstructArguments(
                'Magento\Backend\App\Action\Context',
                array(
                    'session' => $session,
                    'response' => $response,
                    'objectManager' => $objectManager,
                    'helper' => $backendHelper,
                    'request' => $request,
                    'messageManager' => $messageManager
                )
            )
        );
        $context->expects($this->once())->method('getRequest')->will($this->returnValue($request));
        $context->expects($this->once())->method('getResponse')->will($this->returnValue($response));
        $context->expects($this->once())->method('getSession')->will($this->returnValue($session));
        $context->expects($this->once())->method('getMessageManager')->will($this->returnValue($messageManager));

        $resultRedirect = $this->getMockBuilder('Magento\Backend\Model\View\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();

        $resultRedirectFactory = $this->getMockBuilder('Magento\Backend\Model\View\Result\RedirectFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultRedirectFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($resultRedirect);

        $controller = $helper->getObject(
            'Magento\Backend\Controller\Adminhtml\Cache\CleanMedia',
            [
                'context' => $context,
                'resultRedirectFactory' => $resultRedirectFactory
            ]
        );

        // Setup expectations
        $mergeService = $this->getMock('Magento\Framework\View\Asset\MergeService', array(), array(), '', false);
        $mergeService->expects($this->once())->method('cleanMergedJsCss');

        $messageManager->expects($this->once())
            ->method('addSuccess')
            ->with('The JavaScript/CSS cache has been cleaned.'
        );

        $valueMap = array(
            array('Magento\Framework\View\Asset\MergeService', $mergeService),
            array('Magento\Framework\Session\SessionManager', $session)
        );
        $objectManager->expects($this->any())->method('get')->will($this->returnValueMap($valueMap));

        $resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('adminhtml/*')
            ->willReturnSelf();

        // Run
        $controller->execute();
    }
}
