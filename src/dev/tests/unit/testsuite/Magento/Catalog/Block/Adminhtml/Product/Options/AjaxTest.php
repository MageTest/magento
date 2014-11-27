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

namespace Magento\Catalog\Block\Adminhtml\Product\Options;

use \Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class AjaxTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Catalog\Block\Adminhtml\Product\Options\Ajax */
    protected $block;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Backend\Block\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var \Magento\Framework\Json\EncoderInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $encoderInterface;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $productFactory;

    /** @var \Magento\Core\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $coreHelper;

    /** @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject */
    protected $registry;

    protected function setUp()
    {
        $this->context = $this->getMockBuilder('Magento\Backend\Block\Context')
            ->setMethods(['getEventManager', 'getScopeConfig', 'getLayout', 'getRequest'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->encoderInterface= $this->getMock('Magento\Framework\Json\EncoderInterface');
        $this->productFactory= $this->getMock('Magento\Catalog\Model\ProductFactory', ['create']);
        $this->coreHelper= $this->getMock('Magento\Core\Helper\Data', [], [], '', false);
        $this->registry= $this->getMock('Magento\Framework\Registry');

        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }

    /**
     *  Test protected `_toHtml` method via public `toHtml` method.
     */
    public function testToHtml()
    {
        $eventManager= $this->getMockBuilder('Magento\Framework\Event\Manager')
            ->disableOriginalConstructor()
            ->setMethods(['dispatch'])
            ->getMock();
        $eventManager->expects($this->once())->method('dispatch')->will($this->returnValue(true));

        $scopeConfig= $this->getMockBuilder('\Magento\Framework\App\Config')
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()->getMock();
        $scopeConfig->expects($this->once())->method('getValue')->withAnyParameters()
            ->will($this->returnValue(false));

        $product= $this->getMockBuilder('Magento\Catalog\Model\Product')->disableOriginalConstructor()
            ->setMethods(['setStoreId', 'load', 'getId', '__wakeup', '__sleep'])
            ->getMock();
        $product->expects($this->once())->method('setStoreId')->will($this->returnSelf());
        $product->expects($this->once())->method('load')->will($this->returnSelf());
        $product->expects($this->once())->method('getId')->will($this->returnValue(1));

        $optionsBlock = $this->getMockBuilder('Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Option')
            ->setMethods(['setIgnoreCaching', 'setProduct', 'getOptionValues'])
            ->disableOriginalConstructor()
            ->getMock();
        $optionsBlock->expects($this->once())->method('setIgnoreCaching')->with(true)->will($this->returnSelf());
        $optionsBlock->expects($this->once())->method('setProduct')->with($product)->will($this->returnSelf());
        $optionsBlock->expects($this->once())->method('getOptionValues')->will($this->returnValue([]));

        $layout= $this->getMockBuilder('Magento\Framework\View\Layout\Element\Layout')
            ->disableOriginalConstructor()
            ->setMethods(['createBlock'])
            ->getMock();
        $layout->expects($this->once())->method('createBlock')
            ->with('Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Option')
            ->will($this->returnValue($optionsBlock));

        $request= $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->setMethods(['getParam'])
            ->disableOriginalConstructor()
            ->getMock();
        $request->expects($this->once())->method('getParam')->with('store')
            ->will($this->returnValue(0));

        $this->context->expects($this->once())->method('getEventManager')
            ->will($this->returnValue($eventManager));
        $this->context->expects($this->once())->method('getScopeConfig')
            ->will($this->returnValue($scopeConfig));
        $this->context->expects($this->once())->method('getLayout')
            ->will($this->returnValue($layout));
        $this->context->expects($this->once())->method('getRequest')
            ->will($this->returnValue($request));
        $this->registry->expects($this->once())->method('registry')
            ->with('import_option_products')
            ->will($this->returnValue([1]));
        $this->productFactory->expects($this->once())->method('create')->will($this->returnValue($product));

        $this->block = $this->objectManagerHelper->getObject(
            'Magento\Catalog\Block\Adminhtml\Product\Options\Ajax',
            [
                'context' => $this->context,
                'jsonEncoder' => $this->encoderInterface,
                'productFactory' => $this->productFactory,
                'coreData' => $this->coreHelper,
                'registry' => $this->registry
            ]
        );
        $this->block->toHtml();
    }
}
