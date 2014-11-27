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
namespace Magento\Framework\App\Config\Initial;

use Magento\Framework\Filesystem;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Config\Initial\Reader
     */
    protected $_model;

    /**
     * @var \Magento\Framework\Config\FileResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fileResolverMock;

    /**
     * @var \Magento\Framework\App\Config\Initial\Converter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_converterMock;

    /**
     * @var string
     */
    protected $_filePath;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Read|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rootDirectory;

    protected function setUp()
    {
        $this->_filePath = __DIR__ . '/_files/';
        $this->_fileResolverMock = $this->getMock('Magento\Framework\Config\FileResolverInterface');
        $this->_converterMock = $this->getMock('Magento\Framework\App\Config\Initial\Converter');
        $schemaLocatorMock = $this->getMock(
            'Magento\Framework\App\Config\Initial\SchemaLocator',
            array(),
            array(),
            '',
            false
        );
        $validationStateMock = $this->getMock('Magento\Framework\Config\ValidationStateInterface');
        $validationStateMock->expects($this->once())->method('isValidated')->will($this->returnValue(true));
        $schemaFile = $this->_filePath . 'config.xsd';
        $schemaLocatorMock->expects($this->once())->method('getSchema')->will($this->returnValue($schemaFile));
        $this->rootDirectory = $this->getMock(
            'Magento\Framework\Filesystem\Directory\Read',
            array('readFile', 'getRelativePath'),
            array(),
            '',
            false
        );
        $this->_model = new \Magento\Framework\App\Config\Initial\Reader(
            $this->_fileResolverMock,
            $this->_converterMock,
            $schemaLocatorMock,
            $validationStateMock
        );
    }

    /**
     * @covers \Magento\Framework\App\Config\Initial\Reader::read
     */
    public function testReadNoFiles()
    {
        $this->_fileResolverMock->expects(
            $this->at(0)
        )->method(
            'get'
        )->with(
            'config.xml',
            'global'
        )->will(
            $this->returnValue(array())
        );

        $this->assertEquals(array(), $this->_model->read());
    }

    /**
     * @covers \Magento\Framework\App\Config\Initial\Reader::read
     */
    public function testReadValidConfig()
    {
        $testXmlFilesList = array(
            file_get_contents($this->_filePath . 'initial_config1.xml'),
            file_get_contents($this->_filePath . 'initial_config2.xml')
        );
        $expectedConfig = array('data' => array(), 'metadata' => array());

        $this->_fileResolverMock->expects(
            $this->at(0)
        )->method(
            'get'
        )->with(
            'config.xml',
            'global'
        )->will(
            $this->returnValue($testXmlFilesList)
        );

        $this->_converterMock->expects(
            $this->once()
        )->method(
            'convert'
        )->with(
            $this->anything()
        )->will(
            $this->returnValue($expectedConfig)
        );

        $this->rootDirectory->expects($this->any())->method('getRelativePath')->will($this->returnArgument(0));

        $this->rootDirectory->expects($this->any())->method('readFile')->will($this->returnValue('<config></config>'));

        $this->assertEquals($expectedConfig, $this->_model->read());
    }
}
