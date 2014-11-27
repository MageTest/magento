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

namespace Magento\Framework\Less\PreProcessor\Instruction;

class ImportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Asset\ModuleNotation\Resolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $notationResolver;

    /**
     * @var \Magento\Framework\View\Asset\File|\PHPUnit_Framework_MockObject_MockObject
     */
    private $asset;

    /**
     * @var \Magento\Framework\Less\PreProcessor\Instruction\Import
     */
    private $object;

    protected function setUp()
    {
        $this->notationResolver = $this->getMock(
            '\Magento\Framework\View\Asset\ModuleNotation\Resolver', [], [], '', false
        );
        $this->asset = $this->getMock('\Magento\Framework\View\Asset\File', array(), array(), '', false);
        $this->asset->expects($this->any())->method('getContentType')->will($this->returnValue('css'));
        $this->object = new \Magento\Framework\Less\PreProcessor\Instruction\Import($this->notationResolver);
    }

    /**
     * @param string $originalContent
     * @param string $foundPath
     * @param string $resolvedPath
     * @param string $expectedContent
     *
     * @dataProvider processDataProvider
     */
    public function testProcess($originalContent, $foundPath, $resolvedPath, $expectedContent)
    {
        $chain = new \Magento\Framework\View\Asset\PreProcessor\Chain($this->asset, $originalContent, 'css');
        $this->notationResolver->expects($this->once())
            ->method('convertModuleNotationToPath')
            ->with($this->asset, $foundPath)
            ->will($this->returnValue($resolvedPath));
        $this->object->process($chain);
        $this->assertEquals($expectedContent, $chain->getContent());
        $this->assertEquals('css', $chain->getContentType());
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        return [
            'non-modular notation' => [
                '@import (type) "some/file.css" media;',
                'some/file.css',
                'some/file.css',
                "@import (type) 'some/file.css' media;",
            ],
            'modular, with extension' => [
                '@import (type) "Magento_Module::something.css" media;',
                'Magento_Module::something.css',
                'Magento_Module/something.css',
                "@import (type) 'Magento_Module/something.css' media;",
            ],
            'modular, no extension' => [
                '@import (type) "Magento_Module::something" media;',
                'Magento_Module::something.less',
                'Magento_Module/something.less',
                "@import (type) 'Magento_Module/something.less' media;",
            ],
            'no type' => [
                '@import "Magento_Module::something.css" media;',
                'Magento_Module::something.css',
                'Magento_Module/something.css',
                "@import 'Magento_Module/something.css' media;",
            ],
            'no media' => [
                '@import (type) "Magento_Module::something.css";',
                'Magento_Module::something.css',
                'Magento_Module/something.css',
                "@import (type) 'Magento_Module/something.css';",
            ],
        ];
    }

    public function testProcessNoImport()
    {
        $originalContent = 'color: #000000;';
        $expectedContent = 'color: #000000;';

        $chain = new \Magento\Framework\View\Asset\PreProcessor\Chain($this->asset, $originalContent, 'css');
        $this->notationResolver->expects($this->never())
            ->method('convertModuleNotationToPath');
        $this->object->process($chain);
        $this->assertEquals($expectedContent, $chain->getContent());
        $this->assertEquals('css', $chain->getContentType());
    }

    /**
     * @covers \Magento\Framework\Less\PreProcessor\Instruction\Import::resetRelatedFiles
     */
    public function testGetRelatedFiles()
    {
        $this->assertSame([], $this->object->getRelatedFiles());

        $this->notationResolver->expects($this->once())
            ->method('convertModuleNotationToPath')
            ->with($this->asset, 'Magento_Module::something.css')
            ->will($this->returnValue('Magento_Module/something.css'));
        $chain = new \Magento\Framework\View\Asset\PreProcessor\Chain(
            $this->asset,
            '@import (type) "Magento_Module::something.css" media;',
            'css'
        );
        $this->object->process($chain);
        $chain = new \Magento\Framework\View\Asset\PreProcessor\Chain($this->asset, 'color: #000000;', 'css');
        $this->object->process($chain);

        $expected = [['Magento_Module::something.css', $this->asset]];
        $this->assertSame($expected, $this->object->getRelatedFiles());

        $this->object->resetRelatedFiles();
        $this->assertSame([], $this->object->getRelatedFiles());
    }
}
