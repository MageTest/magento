<?php
/**
 * \Magento\Wishlist\Block\Item\Configure
 *
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
namespace Magento\Wishlist\Block\Item;

class ConfigureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Wishlist\Block\Item\Configure
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_mockRegistry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_mockContext;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_mockWishlistData;

    public function setUp()
    {
        $this->_mockWishlistData = $this->getMockBuilder(
            'Magento\Wishlist\Helper\Data'
        )->disableOriginalConstructor()->getMock();
        $this->_mockContext = $this->getMockBuilder(
            'Magento\Framework\View\Element\Template\Context'
        )->disableOriginalConstructor()->getMock();
        $this->_mockRegistry = $this->getMockBuilder('Magento\Framework\Registry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_model = new \Magento\Wishlist\Block\Item\Configure(
            $this->_mockContext,
            $this->_mockWishlistData,
            $this->_mockRegistry
        );
    }

    public function testGetWishlistOptions()
    {
        $typeId = 'simple';
        $product = $this->getMockBuilder('Magento\Catalog\Model\Product')->disableOriginalConstructor()->getMock();
        $product->expects($this->once())->method('getTypeId')->willReturn($typeId);
        $this->_mockRegistry->expects($this->once())
            ->method('registry')
            ->with($this->equalTo('product'))
            ->willReturn($product);

        $this->assertEquals(['productType' => $typeId], $this->_model->getWishlistOptions());
    }

    public function testGetProduct()
    {
        $product = 'some test product';
        $this->_mockRegistry->expects(
            $this->once()
        )->method(
            'registry'
        )->with(
            $this->equalTo('product')
        )->willReturn(
            $product
        );

        $this->assertEquals($product, $this->_model->getProduct());
    }

    public function testSetLayout()
    {
        $layoutMock = $this->getMock(
            'Magento\Framework\View\LayoutInterface',
            [],
            [],
            '',
            false
        );

        $blockMock = $this->getMock(
            'Magento\Framework\View\Element\AbstractBlock',
            ['setCustomAddToCartUrl'],
            [],
            '',
            false
        );
        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('product.info')
            ->will($this->returnValue($blockMock));

        $itemMock = $this->getMock(
            'Magento\Wishlist\Model\Item',
            [],
            [],
            '',
            false
        );

        $this->_mockRegistry->expects($this->exactly(2))
            ->method('registry')
            ->with('wishlist_item')
            ->willReturn($itemMock);

        $this->_mockWishlistData->expects($this->once())
            ->method('getAddToCartUrl')
            ->with($itemMock)
            ->willReturn('some_url');

        $blockMock->expects($this->once())
            ->method('setCustomAddToCartUrl')
            ->with('some_url');

        $this->assertEquals($this->_model, $this->_model->setLayout($layoutMock));
        $this->assertEquals($layoutMock, $this->_model->getLayout());
    }

    public function testSetLayoutWithNoItem()
    {
        $layoutMock = $this->getMock(
            'Magento\Framework\View\LayoutInterface',
            [],
            [],
            '',
            false
        );

        $blockMock = $this->getMock(
            'Magento\Framework\View\Element\AbstractBlock',
            ['setCustomAddToCartUrl'],
            [],
            '',
            false
        );
        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('product.info')
            ->willReturn($blockMock);

        $this->_mockRegistry->expects($this->exactly(1))
            ->method('registry')
            ->with('wishlist_item')
            ->willReturn(null);

        $this->_mockWishlistData->expects($this->never())
            ->method('getAddToCartUrl');

        $blockMock->expects($this->never())
            ->method('setCustomAddToCartUrl');

        $this->assertEquals($this->_model, $this->_model->setLayout($layoutMock));
        $this->assertEquals($layoutMock, $this->_model->getLayout());
    }


    public function testSetLayoutWithNoBlockAndItem()
    {
        $layoutMock = $this->getMock(
            'Magento\Framework\View\LayoutInterface',
            [],
            [],
            '',
            false
        );

        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('product.info')
            ->willReturn(null);

        $this->_mockRegistry->expects($this->never())
            ->method('registry');

        $this->_mockWishlistData->expects($this->never())
            ->method('getAddToCartUrl');

        $this->assertEquals($this->_model, $this->_model->setLayout($layoutMock));
        $this->assertEquals($layoutMock, $this->_model->getLayout());
    }
}
