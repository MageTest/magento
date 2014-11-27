<?php
/**
 * \Magento\Framework\Object\Copy\Config\Reader
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
namespace Magento\Framework\Object\Copy\Config;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * @magentoDataFixture Magento/Backend/controllers/_files/cache/all_types_disabled.php
 */
class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Object\Copy\Config\Reader
     */
    protected $_model;

    public function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Framework\Filesystem $filesystem */
        $filesystem = $objectManager->create(
            'Magento\Framework\Filesystem',
            array(
                'directoryList' => $objectManager->create(
                    'Magento\Framework\App\Filesystem\DirectoryList',
                    array(
                        'root' => BP,
                        'config' => array(
                            DirectoryList::MODULES => array(DirectoryList::PATH => __DIR__ . '/_files'),
                            DirectoryList::CONFIG => array(DirectoryList::PATH => __DIR__ . '/_files')
                        )
                    )
                )
            )
        );

        /** @var \Magento\Framework\Module\Declaration\FileResolver $modulesDeclarations */
        $modulesDeclarations = $objectManager->create(
            'Magento\Framework\Module\Declaration\FileResolver',
            array('filesystem' => $filesystem)
        );


        /** @var \Magento\Framework\Module\Declaration\Reader\Filesystem $filesystemReader */
        $filesystemReader = $objectManager->create(
            'Magento\Framework\Module\Declaration\Reader\Filesystem',
            array('fileResolver' => $modulesDeclarations)
        );

        /** @var \Magento\Framework\Module\ModuleList $modulesList */
        $modulesList = $objectManager
            ->create('Magento\Framework\Module\ModuleList', array('reader' => $filesystemReader));

        /** @var \Magento\Framework\Module\Dir\Reader $moduleReader */
        $moduleReader = $objectManager->create(
            'Magento\Framework\Module\Dir\Reader',
            array('moduleList' => $modulesList, 'filesystem' => $filesystem)
        );
        $moduleReader->setModuleDir('Magento_Test', 'etc', __DIR__ . '/_files/Magento/Test/etc');

        /** @var \Magento\Framework\App\Config\FileResolver $fileResolver */
        $fileResolver = $objectManager->create(
            'Magento\Framework\App\Config\FileResolver',
            array('moduleReader' => $moduleReader)
        );

        $this->_model = $objectManager->create(
            'Magento\Framework\Object\Copy\Config\Reader',
            array('fileResolver' => $fileResolver)
        );
    }

    public function testRead()
    {
        $result = $this->_model->read('global');
        $expected = include '_files/expectedArray.php';
        $this->assertEquals($expected, $result);
    }

    public function testMergeCompleteAndPartial()
    {
        $fileList = array(
            file_get_contents(__DIR__ . '/_files/partialFieldsetFirst.xml'),
            file_get_contents(__DIR__ . '/_files/partialFieldsetSecond.xml')
        );
        $fileResolverMock = $this->getMockBuilder(
            'Magento\Framework\Config\FileResolverInterface'
        )->setMethods(
            array('get')
        )->disableOriginalConstructor()->getMock();
        $fileResolverMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            $this->equalTo('fieldset.xml'),
            $this->equalTo('global')
        )->will(
            $this->returnValue($fileList)
        );

        /** @var \Magento\Framework\Object\Copy\Config\Reader $model */
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\Object\Copy\Config\Reader',
            array('fileResolver' => $fileResolverMock)
        );
        $expected = array(
            'global' => array(
                'sales_convert_quote_item' => array(
                    'event_id' => array('to_order_item' => "*"),
                    'event_name' => array('to_order_item' => "*"),
                    'event_description' => array('to_order_item' => "complexDesciption")
                )
            )
        );
        $this->assertEquals($expected, $model->read('global'));
    }
}
