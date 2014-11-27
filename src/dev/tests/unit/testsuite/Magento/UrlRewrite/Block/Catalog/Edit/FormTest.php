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

namespace Magento\UrlRewrite\Block\Catalog\Edit;

use Magento\TestFramework\Helper\ObjectManager;

class FormTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\UrlRewrite\Block\Edit\Form */
    protected $form;

    /** @var \Magento\Framework\Data\FormFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $formFactory;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $urlRewriteFactory;

    /** @var \Magento\Catalog\Model\ProductFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $productFactory;

    /** @var \Magento\Catalog\Model\CategoryFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $categoryFactory;

    /** @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $layout;

    protected function setUp()
    {
        $this->layout = $this->getMock('Magento\Framework\View\LayoutInterface');
        $this->formFactory = $this->getMock('Magento\Framework\Data\FormFactory', ['create'], [], '', false);
        $this->urlRewriteFactory = $this->getMock('Magento\UrlRewrite\Model\UrlRewriteFactory', ['create']);
        $this->urlRewriteFactory->expects($this->once())->method('create')
            ->willReturn($this->getMock('Magento\UrlRewrite\Model\UrlRewrite', [], [], '', false));
        $this->categoryFactory = $this->getMock('Magento\Catalog\Model\CategoryFactory', ['create'], [], '', false);
        $this->productFactory = $this->getMock('Magento\Catalog\Model\ProductFactory', ['create'], [], '', false);

        $this->form = (new ObjectManager($this))->getObject(
            'Magento\UrlRewrite\Block\Catalog\Edit\Form',
            [
                'layout' => $this->layout,
                'productFactory' => $this->productFactory,
                'categoryFactory' => $this->categoryFactory,
                'formFactory' => $this->formFactory,
                'rewriteFactory' => $this->urlRewriteFactory,
                'data' => ['template' => null],
            ]
        );
    }

    public function testAddErrorMessageWhenProductWithoutStores()
    {
        $form = $this->getMock('Magento\Framework\Data\Form', [], [], '', false);
        $form->expects($this->any())->method('getElement')->will($this->returnValue(
            $this->getMockForAbstractClass('\Magento\Framework\Data\Form\Element\AbstractElement', [], '', false))
        );
        $this->formFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($form));
        $fieldset = $this->getMock('Magento\Framework\Data\Form\Element\Fieldset', [], [], '', false);
        $form->expects($this->once())
            ->method('addFieldset')
            ->will($this->returnValue($fieldset));
        $storeElement = $this->getMock(
            'Magento\Framework\Data\Form\Element\AbstractElement',
            ['setAfterElementHtml', 'setValues'],
            [],
            '',
            false
        );
        $fieldset->expects($this->at(2))
            ->method('addField')
            ->with(
                'store_id',
                'select',
                [
                    'label' => 'Store',
                    'title' => 'Store',
                    'name' => 'store_id',
                    'required' => true,
                    'value' => 0
                ]
            )
            ->willReturn($storeElement);

        $product = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $product->expects($this->any())->method('getId')->willReturn('product_id');
        $product->expects($this->once())->method('getStoreIds')->willReturn([]);
        $this->productFactory->expects($this->once())->method('create')->willReturn($product);
        $this->categoryFactory->expects($this->once())->method('create')
            ->willReturn($this->getMock('Magento\Catalog\Model\Category', [], [], '', false));

        $storeElement->expects($this->once())->method('setAfterElementHtml');
        $storeElement->expects($this->once())->method('setValues')->with([]);

        $this->layout->expects($this->once())->method('createBlock')
            ->willReturn($this->getMock('Magento\Framework\Data\Form\Element\Renderer\RendererInterface'));

        $this->form->toHtml();
    }
}
