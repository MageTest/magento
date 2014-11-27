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

class RepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $baseUrl;

    /**
     * @var \Magento\Framework\View\DesignInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $design;

    /**
     * @var \Magento\Framework\View\Design\Theme\ListInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $themeList;

    /**
     * @var \Magento\Framework\View\Asset\Source|\PHPUnit_Framework_MockObject_MockObject
     */
    private $source;

    /**
     * @var \Magento\Framework\View\Design\ThemeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $theme;

    /**
     * @var Repository
     */
    private $object;

    protected function setUp()
    {
        $this->themeList = $this->getMockForAbstractClass('\Magento\Framework\View\Design\Theme\ListInterface');
        $this->source = $this->getMock(
            'Magento\Framework\View\Asset\Source', array('getFile', 'getContent'), array(), '', false
        );
        $this->baseUrl = $this->getMockForAbstractClass('Magento\Framework\UrlInterface');
        $this->design = $this->getMockForAbstractClass('Magento\Framework\View\DesignInterface');
        $this->theme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');
        $this->object = new Repository($this->baseUrl, $this->design, $this->themeList, $this->source);
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Could not find theme 'nonexistent_theme' for area 'area'
     */
    public function testUpdateDesignParamsWrongTheme()
    {
        $params = array('area' => 'area', 'theme' => 'nonexistent_theme');
        $this->themeList->expects($this->once())
            ->method('getThemeByFullPath')
            ->with('area/nonexistent_theme')
            ->will($this->returnValue(null));
        $this->object->updateDesignParams($params);
    }

    public function testCreateAsset()
    {
        $this->mockDesign();
        $this->baseUrl->expects($this->once())
            ->method('getBaseUrl')
            ->will($this->returnValue('http://example.com/static/'));
        $asset = $this->object->createAsset('test/file.js');
        $this->assertInstanceOf('\Magento\Framework\View\Asset\File', $asset);
        $this->assertEquals('area/theme/locale/test/file.js', $asset->getPath());
        $this->assertEquals('test/file.js', $asset->getFilePath());
        $this->assertEquals('js', $asset->getContentType());
        $this->assertInstanceOf('\Magento\Framework\View\Asset\File\FallbackContext', $asset->getContext());
        $this->assertEquals('', $asset->getModule());
        $this->assertEquals('http://example.com/static/area/theme/locale/test/file.js', $asset->getUrl());

        $this->source->expects($this->once())->method('getFile')->with($asset)->will($this->returnValue('source'));
        $this->source->expects($this->once())->method('getContent')->with($asset)->will($this->returnValue('content'));
        $this->assertEquals('source', $asset->getSourceFile());
        $this->assertEquals('content', $asset->getContent());

        $anotherAsset = $this->object->createAsset('another/file.id');
        $this->assertSame($anotherAsset->getContext(), $asset->getContext());
    }

    public function testCreateAssetModular()
    {
        $this->mockDesign();
        $asset = $this->object->createAsset('Module_Name::test/file.js');
        $this->assertEquals('Module_Name', $asset->getModule());
        $this->assertEquals('test/file.js', $asset->getFilePath());
    }

    public function testGetStaticViewFileContext()
    {
        $this->mockDesign();
        $context = $this->object->getStaticViewFileContext();
        $this->assertInstanceOf('\Magento\Framework\View\Asset\ContextInterface', $context);
        $this->assertSame($context, $this->object->getStaticViewFileContext()); // to ensure in-memory caching
        $asset = $this->object->createAsset('test/file.js');
        $this->assertSame($context, $asset->getContext()); // and once again to ensure in-memory caching for real
    }

    /**
     * @param string $fileId
     * @param string $similarToModule
     * @param string $expectedPath
     * @param string $expectedType
     * @param string $expectedModule
     * @dataProvider createSimilarDataProvider
     */
    public function testCreateSimilar($fileId, $similarToModule, $expectedPath, $expectedType, $expectedModule)
    {
        $similarTo = $this->getMockForAbstractClass('\Magento\Framework\View\Asset\LocalInterface');
        $context = $this->getMockForAbstractClass('\Magento\Framework\View\Asset\ContextInterface');
        $similarTo->expects($this->once())->method('getContext')->will($this->returnValue($context));
        $similarTo->expects($this->any())->method('getModule')->will($this->returnValue($similarToModule));
        $asset = $this->object->createSimilar($fileId, $similarTo);
        $this->assertInstanceOf('\Magento\Framework\View\Asset\File', $asset);
        $this->assertSame($context, $asset->getContext());
        $this->assertEquals($expectedPath, $asset->getFilePath());
        $this->assertEquals($expectedType, $asset->getContentType());
        $this->assertEquals($expectedModule, $asset->getModule());
    }

    /**
     * @return array
     */
    public function createSimilarDataProvider()
    {
        return array(
            array('test/file.css', '', 'test/file.css', 'css', ''),
            array('test/file.js', '', 'test/file.js', 'js', ''),
            array('test/file.css', 'Module_Name', 'test/file.css', 'css', 'Module_Name'),
            array('Module_Name::test/file.css', 'Module_Two', 'test/file.css', 'css', 'Module_Name'),
        );
    }

    /**
     * @param string $filePath
     * @param string $dirPath
     * @param string $baseUrlType
     * @param string $expectedType
     * @param string $expectedUrl
     * @dataProvider createArbitraryDataProvider
     */
    public function testCreateArbitrary($filePath, $dirPath, $baseUrlType, $expectedType, $expectedUrl)
    {
        $this->baseUrl->expects($this->once())
            ->method('getBaseUrl')
            ->will($this->returnValueMap(array(
                array(array('_type' => 'static'), 'http://example.com/static/'),
                array(array('_type' => 'media'), 'http://example.com/media/'),
            )));
        $dirType = 'dirType';
        $asset = $this->object->createArbitrary($filePath, $dirPath, $dirType, $baseUrlType);
        $this->assertInstanceOf('\Magento\Framework\View\Asset\File', $asset);
        $this->assertEquals($expectedType, $asset->getContentType());
        $this->assertEquals($expectedUrl, $asset->getUrl());
        $this->assertEquals($dirType, $asset->getContext()->getBaseDirType());

        $anotherAsset = $this->object->createArbitrary('another/path.js', $dirPath, $dirType, $baseUrlType);
        $this->assertSame($anotherAsset->getContext(), $asset->getContext());
    }

    /**
     * @return array
     */
    public function createArbitraryDataProvider()
    {
        return array(
            array('test/example.js', 'dir/path', 'static', 'js', 'http://example.com/static/dir/path/test/example.js'),
            array('test/example.css', '', 'media', 'css', 'http://example.com/media/test/example.css'),
            array('img/logo.gif', 'uploaded', 'media', 'gif', 'http://example.com/media/uploaded/img/logo.gif'),
        );
    }

    /**
     * @param string $fileId
     * @param string $relFilePath
     * @param string $relModule
     * @param string $expFilePath
     * @param string $expType
     * @param string $expModule
     * @dataProvider createRelatedDataProvider
     */
    public function testCreateRelated($fileId, $relFilePath, $relModule, $expFilePath, $expType, $expModule)
    {
        $relativeTo = $this->getMockForAbstractClass('\Magento\Framework\View\Asset\LocalInterface');
        $context = $this->getMockForAbstractClass('\Magento\Framework\View\Asset\ContextInterface');
        $relativeTo->expects($this->once())->method('getContext')->will($this->returnValue($context));
        $relativeTo->expects($this->any())->method('getModule')->will($this->returnValue($relModule));
        $relativeTo->expects($this->any())->method('getFilePath')->will($this->returnValue($relFilePath));
        $asset = $this->object->createRelated($fileId, $relativeTo);
        $this->assertInstanceOf('\Magento\Framework\View\Asset\File', $asset);
        $this->assertSame($context, $asset->getContext());
        $this->assertEquals($expFilePath, $asset->getFilePath());
        $this->assertEquals($expType, $asset->getContentType());
        $this->assertEquals($expModule, $asset->getModule());
    }

    /**
     * @return array
     */
    public function createRelatedDataProvider()
    {
        return array(
            array('test/file.ext', 'rel/file.ext2', '', 'rel/test/file.ext', 'ext', ''),
            array('test/file.ext', 'rel/file.ext2', 'Module_Name', 'rel/test/file.ext', 'ext', 'Module_Name'),
            array('Module_One::test/file.ext', 'rel/file.ext2', 'Module_Two', 'test/file.ext', 'ext', 'Module_One'),
            array('Module_Name::test/file.ext', '', '', 'test/file.ext', 'ext', 'Module_Name'),
        );
    }

    public function testCreateRemoteAsset()
    {
        $asset = $this->object->createRemoteAsset('url', 'type');
        $this->assertInstanceOf('\Magento\Framework\View\Asset\Remote', $asset);
        $this->assertEquals('url', $asset->getUrl());
        $this->assertEquals('type', $asset->getContentType());
    }

    public function testGetUrl()
    {
        $this->mockDesign();
        $this->baseUrl->expects($this->once())
            ->method('getBaseUrl')
            ->will($this->returnValue('http://example.com/static/'));
        $result = $this->object->getUrl('Module_Name::img/product/placeholder.png');
        $this->assertEquals(
            'http://example.com/static/area/theme/locale/Module_Name/img/product/placeholder.png',
            $result
        );
    }

    public function testGetUrlWithParams()
    {
        $defaultTheme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');
        $defaults = array(
            'area' => 'area',
            'themeModel' => $defaultTheme,
            'locale' => 'locale',
        );
        $this->design->expects($this->atLeastOnce())->method('getDesignParams')->will($this->returnValue($defaults));
        $this->design->expects($this->once())
            ->method('getConfigurationDesignTheme')
            ->with('custom_area')
            ->will($this->returnValue(false));
        $this->design->expects($this->any())
            ->method('getThemePath')
            ->with($this->theme)
            ->will($this->returnValue('custom_theme'));
        $this->baseUrl->expects($this->once())
            ->method('getBaseUrl')
            ->will($this->returnValue('http://example.com/static/'));
        $params = array(
            'area' => 'custom_area',
            'locale' => 'en_US',
            'module' => 'This_Shall_Not_Be_Used',
        );
        $result = $this->object->getUrlWithParams('Module_Name::file.ext', $params);
        $this->assertEquals('http://example.com/static/custom_area/custom_theme/en_US/Module_Name/file.ext', $result);
    }

    private function mockDesign()
    {
        $params = array(
            'area'       => 'area',
            'themeModel' => $this->theme,
            'locale'     => 'locale',
        );
        $this->design->expects($this->atLeastOnce())->method('getDesignParams')->will($this->returnValue($params));
        $this->design->expects($this->any())
            ->method('getConfigurationDesignTheme')
            ->with('area')
            ->will($this->returnValue($this->theme));
        $this->design->expects($this->any())
            ->method('getThemePath')
            ->with($this->theme)
            ->will($this->returnValue('theme'));
        $this->themeList->expects($this->any())->method('getThemeByFullPath')->will($this->returnValue($this->theme));
    }

    /**
     * @expectedException \Magento\Framework\Exception
     * @expectedExceptionMessage Scope separator "::" cannot be used without scope identifier.
     */
    public function testExtractModuleException()
    {
        Repository::extractModule('::no_scope.ext');
    }

    public function testExtractModule()
    {
        $this->assertEquals(array('Module_One', 'File'), Repository::extractModule('Module_One::File'));
        $this->assertEquals(array('', 'File'), Repository::extractModule('File'));
        $this->assertEquals(
            array('Module_One', 'File::SomethingElse'),
            Repository::extractModule('Module_One::File::SomethingElse')
        );
    }
} 
