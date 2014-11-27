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
namespace Magento\Customer\Model\Address\Config;

class SchemaLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Model\Address\Config\SchemaLocator
     */
    protected $_model;

    /**
     * @var \Magento\Framework\Module\Dir\Reader|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_moduleReader;

    /**
     * @var string
     */
    protected $_xsdDir = 'schema_dir';

    /**
     * @var string
     */
    protected $_xsdFile;

    protected function setUp()
    {
        $this->_xsdFile = $this->_xsdDir . '/address_formats.xsd';
        $this->_moduleReader = $this->getMock(
            'Magento\Framework\Module\Dir\Reader',
            array('getModuleDir'),
            array(),
            '',
            false
        );
        $this->_moduleReader->expects(
            $this->once()
        )->method(
            'getModuleDir'
        )->with(
            'etc',
            'Magento_Customer'
        )->will(
            $this->returnValue($this->_xsdDir)
        );

        $this->_model = new \Magento\Customer\Model\Address\Config\SchemaLocator($this->_moduleReader);
    }

    public function testGetSchema()
    {
        $this->assertEquals($this->_xsdFile, $this->_model->getSchema());
        // Makes sure the value is calculated only once
        $this->assertEquals($this->_xsdFile, $this->_model->getSchema());
    }

    public function testGetPerFileSchema()
    {
        $this->assertEquals($this->_xsdFile, $this->_model->getPerFileSchema());
        // Makes sure the value is calculated only once
        $this->assertEquals($this->_xsdFile, $this->_model->getPerFileSchema());
    }
}
