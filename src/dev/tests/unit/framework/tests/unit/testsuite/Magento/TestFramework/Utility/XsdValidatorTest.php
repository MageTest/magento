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
namespace Magento\TestFramework\Utility;

class XsdValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Utility\XsdValidator
     */
    protected $_validator;

    /**
     * Path to xsd schema file
     * @var string
     */
    protected $_xsdSchema;

    protected function setUp()
    {
        $this->_validator = new \Magento\TestFramework\Utility\XsdValidator();
        $this->_xsdSchema = realpath(__DIR__ . '/_files/valid.xsd');
    }

    public function testValidXml()
    {
        $xmlFile = realpath(__DIR__ . '/_files/valid.xml');
        $xmlString = file_get_contents($xmlFile);

        $this->assertEquals(array(), $this->_validator->validate($this->_xsdSchema, $xmlString));
    }

    public function testInvalidXml()
    {
        $xmlFile = realpath(__DIR__ . '/_files/invalid.xml');
        $expected = array(
            "Element 'block', attribute 'type': The attribute 'type' is not allowed.",
            "Element 'actions': This element is not expected. Expected is ( property )."
        );
        $xmlString = file_get_contents($xmlFile);

        $this->assertEquals($expected, $this->_validator->validate($this->_xsdSchema, $xmlString));
    }
}
