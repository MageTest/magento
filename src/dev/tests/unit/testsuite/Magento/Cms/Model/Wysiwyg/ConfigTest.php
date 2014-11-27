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
namespace Magento\Cms\Model\Wysiwyg;

/**
 * @covers \Magento\Cms\Model\Wysiwyg\Config
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $this;

    /**
     * @var \Magento\Backend\Model\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $backendUrlMock;

    /**
     * @var \Magento\Framework\View\Asset\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $assetRepoMock;

    /**
     * @var \Magento\Framework\AuthorizationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $authorizationMock;

    /**
     * @var \Magento\Core\Model\Variable\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $variableConfigMock;

    /**
     * @var \Magento\Widget\Model\Widget\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $widgetConfigMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var array
     */
    protected $windowSize = [];

    protected function setUp()
    {
        $this->backendUrlMock = $this->getMockBuilder('Magento\Backend\Model\UrlInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assetRepoMock = $this->getMockBuilder('Magento\Framework\View\Asset\Repository')
            ->disableOriginalConstructor()
            ->getMock();
        $this->authorizationMock = $this->getMockBuilder('Magento\Framework\AuthorizationInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->variableConfigMock = $this->getMockBuilder('Magento\Core\Model\Variable\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->widgetConfigMock = $this->getMockBuilder('Magento\Widget\Model\Widget\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->windowSize = [
            'width' => 1200,
            'height' => 800
        ];

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->this = $objectManager->getObject(
            'Magento\Cms\Model\Wysiwyg\Config',
            [
                'backendUrl' => $this->backendUrlMock,
                'assetRepo' => $this->assetRepoMock,
                'authorization' => $this->authorizationMock,
                'variableConfig' => $this->variableConfigMock,
                'widgetConfig' => $this->widgetConfigMock,
                'scopeConfig' => $this->scopeConfigMock,
                'windowSize' => $this->windowSize
            ]
        );
    }

    /**
     * @covers \Magento\Cms\Model\Wysiwyg\Config::getConfig
     * @param array $data
     * @param boolean $isAuthorizationAllowed
     * @param array $expectedResults
     *
     * @dataProvider getConfigDataProvider
     */
    public function testGetConfig($data, $isAuthorizationAllowed, $expectedResults)
    {
        $wysiwygPluginSettings = [
            'wysiwygPluginSettings' => 'wysiwyg is here'
        ];

        $pluginSettings = [
            'pluginSettings' => 'plugins are here'
        ];

        $this->backendUrlMock->expects($this->atLeastOnce())
            ->method('getUrl')
            ->withConsecutive(
                ['cms/wysiwyg/directive'],
                ['cms/wysiwyg_images/index']
            );
        $this->assetRepoMock->expects($this->atLeastOnce())
            ->method('getUrl')
            ->withConsecutive(
                ['mage/adminhtml/wysiwyg/tiny_mce/themes/advanced/skins/default/dialog.css'],
                ['mage/adminhtml/wysiwyg/tiny_mce/themes/advanced/skins/default/content.css']
            );
        $this->authorizationMock->expects($this->atLeastOnce())
            ->method('isAllowed')
            ->with('Magento_Cms::media_gallery')
            ->willReturn($isAuthorizationAllowed);
        $this->variableConfigMock->expects($this->any())
            ->method('getWysiwygPluginSettings')
            ->willReturn($wysiwygPluginSettings);
        $this->widgetConfigMock->expects($this->any())
            ->method('getPluginSettings')
            ->willReturn($pluginSettings);

        $config = $this->this->getConfig($data);
        $this->assertInstanceOf('Magento\Framework\Object', $config);
        $this->assertEquals($expectedResults[0], $config->getData('someData'));
        $this->assertEquals($expectedResults[1], $config->getData('wysiwygPluginSettings'));
        $this->assertEquals($expectedResults[2], $config->getData('pluginSettings'));
    }

    public function getConfigDataProvider()
    {
        return [
            'add_variables IS FALSE, add_widgets IS FALSE, isAuthorizationAllowed IS FALSE' => [
                'data' => [
                    'add_variables' => false,
                    'add_widgets' => false
                ],
                'isAuthorizationAllowed' => false,
                'expectedResults' => [null, null, null]
            ],
            'add_variables IS TRUE, add_widgets IS TRUE, isAuthorizationAllowed IS TRUE' => [
                'data' => [
                    'someData' => 'important data',
                    'add_variables' => true,
                    'add_widgets' => true
                ],
                'isAuthorizationAllowed' => true,
                'expectedResults' => ['important data', 'wysiwyg is here', 'plugins are here']
            ]
        ];
    }

    /**
     * @covers \Magento\Cms\Model\Wysiwyg\Config::getSkinImagePlaceholderUrl
     */
    public function testGetSkinImagePlaceholderUrl()
    {
        $url = '/some/url';

        $this->assetRepoMock->expects($this->atLeastOnce())
            ->method('getUrl')
            ->with('Magento_Cms::images/wysiwyg_skin_image.png')
            ->willReturn($url);

        $this->assertEquals($url, $this->this->getSkinImagePlaceholderUrl());
    }

    /**
     * @covers \Magento\Cms\Model\Wysiwyg\Config::isEnabled
     * @param string $wysiwygState
     * @param boolean $expectedResult
     *
     * @dataProvider isEnabledDataProvider
     */
    public function testIsEnabled($wysiwygState, $expectedResult)
    {
        $storeId = 1;
        $this->this->setStoreId($storeId);

        $this->scopeConfigMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->with('cms/wysiwyg/enabled', 'store', $storeId)
            ->willReturn($wysiwygState);

        $this->assertEquals($expectedResult, $this->this->isEnabled());
    }

    public function isEnabledDataProvider()
    {
        return [
            ['wysiwygState' => 'enabled', 'expectedResult' => true],
            ['wysiwygState' => 'hidden', 'expectedResult' => true],
            ['wysiwygState' => 'masked', 'expectedResult' => false]
        ];
    }

    /**
     * @covers \Magento\Cms\Model\Wysiwyg\Config::isHidden
     * @param string $status
     * @param boolean $expectedResult
     *
     * @dataProvider isHiddenDataProvider
     */
    public function testIsHidden($status, $expectedResult)
    {
        $this->scopeConfigMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->with('cms/wysiwyg/enabled', 'store')
            ->willReturn($status);

        $this->assertEquals($expectedResult, $this->this->isHidden());
    }

    public function isHiddenDataProvider()
    {
        return [
            ['status' => 'hidden', 'expectedResult' => true],
            ['status' => 'enabled', 'expectedResult' => false],
            ['status' => 'masked', 'expectedResult' => false]
        ];
    }
}
