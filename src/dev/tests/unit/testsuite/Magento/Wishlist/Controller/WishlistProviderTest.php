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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Wishlist\Controller;

class WishlistProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Wishlist\Controller\WishlistProvider
     */
    protected $wishlistProvider;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Wishlist\Model\WishlistFactory
     */
    protected $wishlistFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * Set up mock objects for tested class
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->request = $this->getMock(
            '\Magento\Framework\App\RequestInterface',
            ['getModuleName', 'setModuleName', 'getActionName', 'setActionName', 'getCookie', 'getParam'],
            [],
            '',
            false
        );

        $this->wishlistFactory = $this->getMock(
            '\Magento\Wishlist\Model\WishlistFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->customerSession = $this->getMock(
            '\Magento\Customer\Model\Session',
            ['getCustomerId'],
            [],
            '',
            false
        );

        $this->messageManager = $this->getMock(
            '\Magento\Framework\Message\ManagerInterface',
            [],
            [],
            '',
            false
        );

        $this->wishlistProvider = $objectManager->getObject(
            '\Magento\Wishlist\Controller\WishlistProvider',
            [
                'request' => $this->request,
                'wishlistFactory' => $this->wishlistFactory,
                'customerSession' => $this->customerSession,
                'messageManager' => $this->messageManager
            ]
        );
    }

    public function testGetWishlist()
    {
        $wishlist = $this->getMock('\Magento\Wishlist\Model\Wishlist', [], [], '', false);

        $this->wishlistFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($wishlist));

        $this->assertEquals($wishlist, $this->wishlistProvider->getWishlist());
    }

    public function testGetWishlistWithCustomer()
    {
        $wishlist = $this->getMock(
            '\Magento\Wishlist\Model\Wishlist',
            ['loadByCustomerId', 'getId', 'getCustomerId', '__wakeup'],
            [],
            '',
            false
        );
        $wishlist->expects($this->once())
            ->method('loadByCustomerId')
            ->will($this->returnSelf());
        $wishlist->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(1));
        $wishlist->expects($this->once())
            ->method('getCustomerId')
            ->will($this->returnValue(1));

        $this->wishlistFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($wishlist));

        $this->customerSession->expects($this->once())
            ->method('getCustomerId')
            ->will($this->returnValue(1));

        $this->assertEquals($wishlist, $this->wishlistProvider->getWishlist());
    }

    public function testGetWishlistWithIdAndCustomer()
    {
        $wishlist = $this->getMock(
            '\Magento\Wishlist\Model\Wishlist',
            ['loadByCustomerId', 'load', 'getId', 'getCustomerId', '__wakeup'],
            [],
            '',
            false
        );

        $wishlist->expects($this->once())
            ->method('load')
            ->will($this->returnSelf());
        $wishlist->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $wishlist->expects($this->once())
            ->method('getCustomerId')
            ->will($this->returnValue(1));

        $this->wishlistFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($wishlist));

        $this->request->expects($this->once())
            ->method('getParam')
            ->will($this->returnValue(1));

        $this->customerSession->expects($this->once())
            ->method('getCustomerId')
            ->will($this->returnValue(1));

        $this->assertEquals($wishlist, $this->wishlistProvider->getWishlist());
    }

    public function testGetWishlistWithIdWithoutCustomer()
    {
        $wishlist = $this->getMock(
            '\Magento\Wishlist\Model\Wishlist',
            ['loadByCustomerId', 'load', 'getId', 'getCustomerId', '__wakeup'],
            [],
            '',
            false
        );

        $wishlist->expects($this->once())
            ->method('load')
            ->will($this->returnSelf());
        $wishlist->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $wishlist->expects($this->once())
            ->method('getCustomerId')
            ->will($this->returnValue(1));

        $this->wishlistFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($wishlist));

        $this->request->expects($this->once())
            ->method('getParam')
            ->will($this->returnValue(1));

        $this->assertEquals(false, $this->wishlistProvider->getWishlist());
    }
}
