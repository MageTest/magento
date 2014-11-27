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
namespace Magento\Core\Model\Theme;

class ResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Theme\Resolver
     */
    protected $model;

    /**
     * @var \Magento\Framework\View\DesignInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $designMock;

    /**
     * @var \Magento\Core\Model\Resource\Theme\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $themeCollectionFactoryMock;

    /**
     * @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $appStateMock;

    /**
     * @var \Magento\Core\Model\Resource\Theme\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $themeCollectionMock;

    /**
     * @var \Magento\Framework\View\Design\ThemeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $themeMock;

    protected function setUp()
    {
        $this->designMock = $this->getMockForAbstractClass('Magento\Framework\View\DesignInterface');
        $this->themeCollectionFactoryMock = $this->getMock(
            'Magento\Core\Model\Resource\Theme\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->themeCollectionMock = $this->getMock(
            'Magento\Core\Model\Resource\Theme\Collection',
            [],
            [],
            '',
            false
        );
        $this->appStateMock = $this->getMock(
            'Magento\Framework\App\State',
            [],
            [],
            '',
            false
        );
        $this->themeMock = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');

        $this->model = new \Magento\Core\Model\Theme\Resolver(
            $this->appStateMock,
            $this->designMock,
            $this->themeCollectionFactoryMock
        );
    }

    public function testGetByAreaWithThemeDefaultArea()
    {
        $this->designMock->expects(
            $this->exactly(2)
        )->method(
            'getDesignTheme'
        )->will(
            $this->returnValue($this->themeMock)
        );
        $this->designMock->expects($this->never())->method('getArea');
        $this->designMock->expects($this->never())->method('getConfigurationDesignTheme');

        $this->themeMock->expects(
            $this->once()
        )->method(
            'getArea'
        )->will(
            $this->returnValue('theme_area')
        );

        $this->themeCollectionFactoryMock->expects($this->never())->method('create');

        $this->appStateMock->expects(
            $this->once()
        )->method(
            'getAreaCode'
        )->will(
            $this->returnValue('theme_area')
        );

        $this->assertEquals($this->themeMock, $this->model->get());
    }

    public function testGetByAreaWithDesignDefaultArea()
    {
        $this->designMock->expects(
            $this->exactly(2)
        )->method(
            'getDesignTheme'
        )->will(
            $this->returnValue($this->themeMock)
        );
        $this->designMock->expects(
            $this->once()
        )->method(
            'getArea'
        )->will(
            $this->returnValue('design_area')
        );
        $this->designMock->expects($this->never())->method('getConfigurationDesignTheme');

        $this->themeMock->expects(
            $this->once()
        )->method(
            'getArea'
        )->will(
            $this->returnValue('theme_area')
        );

        $this->themeCollectionFactoryMock->expects($this->never())->method('create');

        $this->appStateMock->expects(
            $this->once()
        )->method(
            'getAreaCode'
        )->will(
            $this->returnValue('design_area')
        );

        $this->assertEquals($this->themeMock, $this->model->get());
    }

    public function testGetByAreaWithOtherAreaAndStringThemeId()
    {
        $this->designMock->expects(
            $this->once()
        )->method(
            'getDesignTheme'
        )->will(
            $this->returnValue($this->themeMock)
        );
        $this->designMock->expects(
            $this->once()
        )->method(
            'getArea'
        )->will(
            $this->returnValue('design_area')
        );
        $this->designMock->expects(
            $this->once()
        )->method(
            'getConfigurationDesignTheme'
        )->will(
            $this->returnValue('other_theme')
        );

        $this->themeMock->expects(
            $this->once()
        )->method(
            'getArea'
        )->will(
            $this->returnValue('theme_area')
        );

        $this->themeCollectionFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->themeCollectionMock)
        );

        $this->themeCollectionMock->expects(
            $this->once()
        )->method(
            'getThemeByFullPath'
        )->with(
            'other_area' . \Magento\Framework\View\Design\ThemeInterface::PATH_SEPARATOR . 'other_theme'
        )->will(
            $this->returnValue($this->themeMock)
        );

        $this->appStateMock->expects(
            $this->once()
        )->method(
            'getAreaCode'
        )->will(
            $this->returnValue('other_area')
        );

        $this->assertEquals($this->themeMock, $this->model->get());
    }

    public function testGetByAreaWithOtherAreaAndNumericThemeId()
    {
        $this->designMock->expects(
            $this->once()
        )->method(
            'getDesignTheme'
        )->will(
            $this->returnValue($this->themeMock)
        );
        $this->designMock->expects(
            $this->once()
        )->method(
            'getArea'
        )->will(
            $this->returnValue('design_area')
        );
        $this->designMock->expects(
            $this->once()
        )->method(
            'getConfigurationDesignTheme'
        )->will(
            $this->returnValue(12)
        );

        $this->themeMock->expects(
            $this->once()
        )->method(
            'getArea'
        )->will(
            $this->returnValue('theme_area')
        );

        $this->themeCollectionFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->themeCollectionMock)
        );

        $this->themeCollectionMock->expects(
            $this->once()
        )->method(
            'getItemById'
        )->with(
            12
        )->will(
            $this->returnValue($this->themeMock)
        );

        $this->appStateMock->expects(
            $this->once()
        )->method(
            'getAreaCode'
        )->will(
            $this->returnValue('other_area')
        );

        $this->assertEquals($this->themeMock, $this->model->get());
    }
}
