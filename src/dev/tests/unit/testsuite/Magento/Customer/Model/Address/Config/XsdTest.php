<?php
/**
 * Test for validation rules implemented by XSD schema for customer address format configuration
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
namespace Magento\Customer\Model\Address\Config;

class XsdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $_schemaFile;

    protected function setUp()
    {
        $this->_schemaFile = BP . '/app/code/Magento/Customer/etc/address_formats.xsd';
    }

    /**
     * @param string $fixtureXml
     * @param array $expectedErrors
     * @dataProvider exemplarXmlDataProvider
     */
    public function testExemplarXml($fixtureXml, array $expectedErrors)
    {
        $dom = new \Magento\Framework\Config\Dom($fixtureXml, array(), null, null, '%message%');
        $actualResult = $dom->validate($this->_schemaFile, $actualErrors);
        $this->assertEquals(empty($expectedErrors), $actualResult);
        $this->assertEquals($expectedErrors, $actualErrors);
    }

    public function exemplarXmlDataProvider()
    {
        return array(
            'valid' => array('<config><format code="code" title="title" /></config>', array()),
            'valid with optional attributes' => array(
                '<config><format code="code" title="title" renderer="Some_Renderer" escapeHtml="false" /></config>',
                array()
            ),
            'empty root node' => array(
                '<config/>',
                array("Element 'config': Missing child element(s). Expected is ( format ).")
            ),
            'irrelevant root node' => array(
                '<attribute name="attr"/>',
                array("Element 'attribute': No matching global declaration available for the validation root.")
            ),
            'irrelevant node' => array(
                '<config><format code="code" title="title" /><invalid /></config>',
                array("Element 'invalid': This element is not expected. Expected is ( format ).")
            ),
            'non empty node "format"' => array(
                '<config><format code="code" title="title"><invalid /></format></config>',
                array("Element 'format': Element content is not allowed, because the content type is empty.")
            ),
            'node "format" without attribute "code"' => array(
                '<config><format title="title" /></config>',
                array("Element 'format': The attribute 'code' is required but missing.")
            ),
            'node "format" without attribute "title"' => array(
                '<config><format code="code" /></config>',
                array("Element 'format': The attribute 'title' is required but missing.")
            ),
            'node "format" with invalid attribute' => array(
                '<config><format code="code" title="title" invalid="invalid" /></config>',
                array("Element 'format', attribute 'invalid': The attribute 'invalid' is not allowed.")
            ),
            'attribute "escapeHtml" with invalid type' => array(
                '<config><format code="code" title="title" escapeHtml="invalid" /></config>',
                array(
                    "Element 'format', attribute 'escapeHtml': 'invalid' is not a valid value of the atomic type" .
                    " 'xs:boolean'."
                )
            )
        );
    }
}
