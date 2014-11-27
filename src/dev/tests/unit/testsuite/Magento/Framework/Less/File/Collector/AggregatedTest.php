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
namespace Magento\Framework\Less\File\Collector;

/**
 * Tests Aggregate
 */
class AggregatedTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Magento\Framework\View\File\FileList\Factory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileListFactoryMock;

    /**
     * @var \Magento\Framework\View\File\FileList|PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileListMock;

    /**
     * @var \Magento\Framework\View\File\CollectorInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $libraryFilesMock;

    /**
     * @var \Magento\Framework\View\File\CollectorInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $baseFilesMock;

    /**
     * @var \Magento\Framework\View\File\CollectorInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $overriddenBaseFilesMock;

    /**
     * @var \Magento\Framework\View\Design\ThemeInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $themeMock;

    /**
     * Setup tests
     * @return void
     */
    public function setup()
    {
        $this->fileListFactoryMock = $this->getMockBuilder('Magento\Framework\View\File\FileList\Factory')
            ->disableOriginalConstructor()->getMock();
        $this->fileListMock = $this->getMockBuilder('Magento\Framework\View\File\FileList')
            ->disableOriginalConstructor()->getMock();
        $this->fileListFactoryMock->expects($this->any())->method('create')
            ->will($this->returnValue($this->fileListMock));
        $this->libraryFilesMock = $this->getMockBuilder('Magento\Framework\View\File\CollectorInterface')
            ->getMock();

        $this->baseFilesMock = $this->getMockBuilder('Magento\Framework\View\File\CollectorInterface')->getMock();
        $this->overriddenBaseFilesMock = $this->getMockBuilder('Magento\Framework\View\File\CollectorInterface')
            ->getMock();
        $this->themeMock = $this->getMockBuilder('\Magento\Framework\View\Design\ThemeInterface')->getMock();
    }

    /**
     * Tests exception path of no files
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage magento_import returns empty result by path
     *
     * @return void
     */
    public function testGetFilesEmpty()
    {
        $this->libraryFilesMock->expects($this->any())->method('getFiles')->will($this->returnValue([]));
        $this->baseFilesMock->expects($this->any())->method('getFiles')->will($this->returnValue([]));
        $this->overriddenBaseFilesMock->expects($this->any())->method('getFiles')->will($this->returnValue([]));

        $aggregated = new Aggregated(
            $this->fileListFactoryMock,
            $this->libraryFilesMock,
            $this->baseFilesMock,
            $this->overriddenBaseFilesMock
        );

        $this->themeMock->expects($this->any())->method('getInheritedThemes')->will($this->returnValue([]));

        $aggregated->getFiles($this->themeMock, '*');
    }

    /**
     *
     * @dataProvider getFilesDataProvider
     *
     * @param $libraryFiles array Files in lib directory
     * @param $baseFiles array Files in base directory
     * @param $themeFiles array Files in theme
     * *
     * @return void
     */
    public function testGetFiles($libraryFiles, $baseFiles, $themeFiles)
    {
        $this->fileListMock->expects($this->at(0))->method('add')->with($this->equalTo($libraryFiles));
        $this->fileListMock->expects($this->at(1))->method('add')->with($this->equalTo($baseFiles));
        $this->fileListMock->expects($this->any())->method('getAll')->will($this->returnValue(['returnedFile']));

        $subPath = '*';
        $this->libraryFilesMock->expects($this->atLeastOnce())
            ->method('getFiles')
            ->with($this->themeMock, $subPath)
            ->will($this->returnValue($libraryFiles));

        $this->baseFilesMock->expects($this->atLeastOnce())
            ->method('getFiles')
            ->with($this->themeMock, $subPath)
            ->will($this->returnValue($baseFiles));

        $this->overriddenBaseFilesMock->expects($this->any())
            ->method('getFiles')
            ->will($this->returnValue($themeFiles));

        $aggregated = new Aggregated(
            $this->fileListFactoryMock,
            $this->libraryFilesMock,
            $this->baseFilesMock,
            $this->overriddenBaseFilesMock
        );

        $inheritedThemeMock = $this->getMockBuilder('\Magento\Framework\View\Design\ThemeInterface')->getMock();
        $this->themeMock->expects($this->any())->method('getInheritedThemes')
            ->will($this->returnValue([$inheritedThemeMock]));

        $this->assertEquals(['returnedFile'], $aggregated->getFiles($this->themeMock, $subPath));
    }

    /**
     * Provides test data for testGetFiles()
     *
     * @return array
     */
    public function getFilesDataProvider()
    {
        return [
            'all files' => [['file1'], ['file2'], ['file3']],
            'no library' => [[], ['file1', 'file2'], ['file3']],
        ];
    }
}