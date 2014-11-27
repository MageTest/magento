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
namespace Magento\Catalog\Helper;

class OutputTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Helper\Output
     */
    protected $_helper;

    protected function setUp()
    {
        $this->_helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Catalog\Helper\Output'
        );
    }

    /**
     * addHandler()
     * getHandlers()
     */
    public function testAddHandlerGetHandlers()
    {
        // invalid handler
        $this->_helper->addHandler('method', 'handler');
        $this->assertEquals(array(), $this->_helper->getHandlers('method'));

        // add one handler
        $objectOne = new \StdClass();
        $this->_helper->addHandler('valid', $objectOne);
        $this->assertSame(array($objectOne), $this->_helper->getHandlers('valid'));

        // add another one
        $objectTwo = new \StdClass();
        $this->_helper->addHandler('valid', $objectTwo);
        $this->assertSame(array($objectOne, $objectTwo), $this->_helper->getHandlers('valid'));
    }

    public function testProcess()
    {
        $this->_helper->addHandler('sampleProcessor', $this);
        $this->assertStringStartsWith(__CLASS__, $this->_helper->process('sampleProcessor', uniqid(), array()));
    }

    public function testProductAttribute()
    {
        $this->_testAttribute(
            'productAttribute',
            \Magento\Catalog\Model\Product::ENTITY,
            "&lt;p&gt;line1&lt;/p&gt;<br />\nline2"
        );
    }

    public function testCategoryAttribute()
    {
        $this->_testAttribute(
            'categoryAttribute',
            \Magento\Catalog\Model\Category::ENTITY,
            "&lt;p&gt;line1&lt;/p&gt;\nline2"
        );
    }

    /**
     * Helper method for testProcess()
     *
     * @param \Magento\Catalog\Helper\Output $helper
     * @param string $string
     * @param mixed $params
     * @return string
     * @see testProcess()
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function sampleProcessor(\Magento\Catalog\Helper\Output $helper, $string, $params)
    {
        return __CLASS__ . $string;
    }

    /**
     * Test productAttribute() or categoryAttribute() method
     *
     * @param string $method
     * @param string $entityCode
     * @param string $expectedResult
     * @throws \Exception on assertion failure
     */
    protected function _testAttribute($method, $entityCode, $expectedResult)
    {
        $attributeName = 'description';
        $attribute = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Eav\Model\Config'
        )->getAttribute(
            $entityCode,
            $attributeName
        );
        $isHtml = $attribute->getIsHtmlAllowedOnFront();
        $isWysiwyg = $attribute->getIsWysiwygEnabled();
        $attribute->setIsHtmlAllowedOnFront(0)->setIsWysiwygEnabled(0);

        try {
            $this->assertEquals(
                $expectedResult,
                $this->_helper->{$method}(uniqid(), "<p>line1</p>\nline2", $attributeName)
            );

            $attribute->setIsHtmlAllowedOnFront($isHtml)->setIsWysiwygEnabled($isWysiwyg);
        } catch (\Exception $e) {
            $attribute->setIsHtmlAllowedOnFront($isHtml)->setIsWysiwygEnabled($isWysiwyg);
            throw $e;
        }
    }
}
