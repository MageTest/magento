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
namespace Magento\Catalog\Block\Adminhtml\Category\Checkboxes;

/**
 * @magentoAppArea adminhtml
 */
class TreeTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Catalog\Block\Adminhtml\Category\Checkboxes\Tree */
    protected $_block;

    protected function setUp()
    {
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Block\Adminhtml\Category\Checkboxes\Tree'
        );
    }

    public function testSetGetCategoryIds()
    {
        $this->_block->setCategoryIds([1, 4, 7, 56, 2]);
        $this->assertEquals([1, 4, 7, 56, 2], $this->_block->getCategoryIds());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     */
    public function testGetTreeJson()
    {
        $jsonTree = $this->_block->getTreeJson();
        $this->assertContains('Default Category (1)', $jsonTree);
        $this->assertContains('Category 1.1 (2)', $jsonTree);
        $this->assertContains('Category 1.1.1 (1)', $jsonTree);
        $this->assertContains('Category 2 (0)', $jsonTree);
        $this->assertContains('Movable (0)', $jsonTree);
        $this->assertContains('Movable Position 1 (0)', $jsonTree);
        $this->assertContains('Movable Position 2 (2)', $jsonTree);
        $this->assertContains('Movable Position 3 (2)', $jsonTree);
        $this->assertContains('Category 12 (2)', $jsonTree);
        $this->assertContains('"path":"1\/2\/3\/4\/5"', $jsonTree);
    }
}
