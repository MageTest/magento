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

namespace Magento\Framework\Controller\Result;

class RedirectTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Controller\Result\Redirect */
    protected $redirect;

    /** @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $redirectInterface;

    /** @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlBuilder;

    /** @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlInterface;

    /** @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $response;

    protected function setUp()
    {
        $this->redirectInterface = $this->getMock(
            'Magento\Framework\App\Response\RedirectInterface',
            [],
            [],
            '',
            false
        );
        $this->urlBuilder = $this->getMock(
            'Magento\Framework\UrlInterface',
            [],
            [],
            '',
            false
        );
        $this->urlInterface = $this->getMock(
            'Magento\Framework\UrlInterface',
            [],
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
        $this->redirect = new Redirect($this->redirectInterface, $this->urlInterface);
    }

    public function testSetRefererUrl()
    {
        $this->redirectInterface->expects($this->once())->method('getRefererUrl');
        $this->assertInstanceOf('Magento\Framework\Controller\Result\Redirect', $this->redirect->setRefererUrl());
    }

    public function testSetRefererOrBaseUrl()
    {
        $this->redirectInterface->expects($this->once())->method('getRedirectUrl');
        $this->assertInstanceOf('Magento\Framework\Controller\Result\Redirect', $this->redirect->setRefererOrBaseUrl());
    }

    public function testSetUrl()
    {
        $url = 'http://test.com';
        $this->assertInstanceOf('Magento\Framework\Controller\Result\Redirect', $this->redirect->setUrl($url));
    }

    public function testSetPath()
    {
        $path = 'test/path';
        $params = ['one' => 1, 'two' => 2];
        $this->redirectInterface->expects($this->once())->method('updatePathParams')->with($params)->will(
            $this->returnValue($params)
        );
        $this->assertInstanceOf(
            'Magento\Framework\Controller\Result\Redirect',
            $this->redirect->setPath($path, $params)
        );
    }

    public function testRender()
    {
        $this->response->expects($this->once())->method('setRedirect');
        $this->assertInstanceOf(
            'Magento\Framework\Controller\Result\Redirect',
            $this->redirect->renderResult($this->response)
        );
    }
}
