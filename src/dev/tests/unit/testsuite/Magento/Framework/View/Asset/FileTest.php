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

namespace Magento\Framework\View\Asset;

class FileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Source|\PHPUnit_Framework_MockObject_MockObject
     */
    private $source;

    /**
     * @var \Magento\Framework\View\Asset\ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var File
     */
    private $object;

    public function setUp()
    {
        $this->source = $this->getMock('Magento\Framework\View\Asset\Source', [], [], '', false);
        $this->context = $this->getMockForAbstractClass('\Magento\Framework\View\Asset\ContextInterface');
        $this->object = new File($this->source, $this->context, 'dir/file.css', 'Magento_Module', 'css');
    }

    public function testGetUrl()
    {
        $this->context->expects($this->once())->method('getBaseUrl')->will($this->returnValue('http://example.com/'));
        $this->context->expects($this->once())->method('getPath')->will($this->returnValue('static'));
        $this->assertEquals('http://example.com/static/Magento_Module/dir/file.css', $this->object->getUrl());
    }

    public function testGetContentType()
    {
        $this->assertEquals('css', $this->object->getContentType());
        $object = new File($this->source, $this->context, '', '', 'type');
        $this->assertEquals('type', $object->getContentType());
    }

    /**
     * @param string $contextPath
     * @param string $module
     * @param string $filePath
     * @param string $expected
     * @dataProvider getPathDataProvider
     */
    public function testGetPath($contextPath, $module, $filePath, $expected)
    {
        $this->context->expects($this->once())->method('getPath')->will($this->returnValue($contextPath));
        $object = new File($this->source, $this->context, $filePath, $module, '');
        $this->assertEquals($expected, $object->getPath());
    }

    /**
     * @return array
     */
    public function getPathDataProvider()
    {
        return [
            ['', '', '', ''],
            ['', '', 'c/d', 'c/d'],
            ['', 'b', '', 'b'],
            ['', 'b', 'c/d', 'b/c/d'],
            ['a', '', '', 'a'],
            ['a', '', 'c/d', 'a/c/d'],
            ['a', 'b', '', 'a/b'],
            ['a', 'b', 'c/d', 'a/b/c/d'],
        ];
    }

    public function testGetSourceFile()
    {
        $this->source->expects($this->once())
            ->method('getFile')
            ->with($this->object)
            ->will($this->returnValue('result'));
        $this->assertEquals('result', $this->object->getSourceFile());
        $this->assertEquals('result', $this->object->getSourceFile()); // second time to assert in-memory caching
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Unable to resolve the source file for 'context/Magento_Module/dir/file.css'
     */
    public function testGetSourceFileMissing()
    {
        $this->context->expects($this->once())->method('getPath')->will($this->returnValue('context'));
        $this->source->expects($this->once())->method('getFile')->will($this->returnValue(false));
        $this->object->getSourceFile();
    }

    public function testGetContent()
    {
        $this->source->expects($this->exactly(2))
            ->method('getContent')
            ->with($this->object)
            ->will($this->returnValue('content'));
        $this->assertEquals('content', $this->object->getContent());
        $this->assertEquals('content', $this->object->getContent()); // no in-memory caching for content
    }

    public function testSimpleGetters()
    {
        $this->assertEquals('dir/file.css', $this->object->getFilePath());
        $this->assertSame($this->context, $this->object->getContext());
        $this->assertEquals('Magento_Module', $this->object->getModule());
    }
}
