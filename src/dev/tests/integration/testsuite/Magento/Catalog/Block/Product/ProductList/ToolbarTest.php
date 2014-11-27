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
namespace Magento\Catalog\Block\Product\ProductList;

class ToolbarTest extends \PHPUnit_Framework_TestCase
{
    public function testGetPagerHtml()
    {
        /** @var $layout \Magento\Framework\View\Layout */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        );
        /** @var $block \Magento\Catalog\Block\Product\ProductList\Toolbar */
        $block = $layout->createBlock('Magento\Catalog\Block\Product\ProductList\Toolbar', 'block');
        /** @var $childBlock \Magento\Framework\View\Element\Text */
        $childBlock = $layout->addBlock('Magento\Framework\View\Element\Text', 'product_list_toolbar_pager', 'block');

        $expectedHtml = '<b>Any text there</b>';
        $this->assertNotEquals($expectedHtml, $block->getPagerHtml());
        $childBlock->setText($expectedHtml);
        $this->assertEquals($expectedHtml, $block->getPagerHtml());
    }
}
