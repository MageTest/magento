<?php
/**
 * \Magento\Widget\Model\Config\FileResolver
 *
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
namespace Magento\Widget\Model\Config;

use Magento\Framework\App\Filesystem\DirectoryList;

class FileResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Widget\Model\Config\FileResolver
     */
    private $_object;

    public function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\TestFramework\App\Filesystem $filesystem */
        $filesystem = $objectManager->get('Magento\Framework\Filesystem');
        $filesystem->overridePath(DirectoryList::MODULES, __DIR__ . '/_files/code');
        $filesystem->overridePath(DirectoryList::THEMES, __DIR__ . '/_files/design');
        $filesystem->overridePath(DirectoryList::CONFIG, __DIR__ . '/_files');

        $moduleListMock = $this->getMockBuilder(
            'Magento\Framework\Module\ModuleListInterface'
        )->disableOriginalConstructor()->getMock();
        $moduleListMock->expects(
            $this->any()
        )->method(
            'getModules'
        )->will(
            $this->returnValue(
                array('Magento_Test' => array('name' => 'Magento_Test', 'version' => '1.11.1', 'active' => 'true'))
            )
        );


        $moduleReader = $objectManager->create(
            'Magento\Framework\Module\Dir\Reader',
            array('moduleList' => $moduleListMock, 'filesystem' => $filesystem)
        );
        $moduleReader->setModuleDir('Magento_Test', 'etc', __DIR__ . '/_files/code/Magento/Test/etc');
        $this->_object = $objectManager->create(
            'Magento\Widget\Model\Config\FileResolver',
            array('moduleReader' => $moduleReader, 'filesystem' => $filesystem)
        );
    }

    public function testGetDesign()
    {
        $widgetConfigs = $this->_object->get('widget.xml', 'design');
        $expected = str_replace('\\', '/', realpath(__DIR__ . '/_files/design/frontend/Test/etc/widget.xml'));
        $actual = $widgetConfigs->key();
        $this->assertCount(1, $widgetConfigs);
        $this->assertStringEndsWith($actual, $expected);
    }

    public function testGetGlobal()
    {
        $widgetConfigs = $this->_object->get('widget.xml', 'global');
        $expected = str_replace('\\', '/', realpath(__DIR__ . '/_files/code/Magento/Test/etc/widget.xml'));
        $actual = $widgetConfigs->key();
        $this->assertCount(1, $widgetConfigs);
        $this->assertStringEndsWith($actual, $expected);
    }
}
