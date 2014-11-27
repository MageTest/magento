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
namespace Magento\Test\Integrity\Magento\Webapi\Model;

use Magento\TestFramework\Integrity\AbstractConfig;

/**
 * Find webapi xml files and validate them
 */
class ConfigTest extends AbstractConfig
{
    public function testSchemaUsingInvalidXml($expectedErrors = null)
    {
        // @codingStandardsIgnoreStart
        $expectedErrors = array(
            "Element 'route', attribute 'method': [facet 'enumeration'] The value 'PATCH' is not an element of the set {'GET', 'PUT', 'POST', 'DELETE'}.",
            "Element 'route', attribute 'method': 'PATCH' is not a valid value of the local atomic type.",
            "Element 'service': The attribute 'method' is required but missing.",
            "Element 'data': Missing child element(s). Expected is ( parameter ).",
            "Element 'route': Missing child element(s). Expected is ( service ).",
            "Element 'route': Missing child element(s). Expected is ( resources ).",
        );
        // @codingStandardsIgnoreEnd
        parent::testSchemaUsingInvalidXml($expectedErrors);
    }

    /**
     * Returns the name of the xml files to validate
     *
     * @return string
     */
    protected function _getXmlName()
    {
        return 'webapi.xml';
    }

    /**
     * The location of a single valid complete xml file
     *
     * @return string
     */
    protected function _getKnownValidXml()
    {
        return __DIR__ . '/_files/webapi.xml';
    }

    /**
     * The location of a single known invalid complete xml file
     *
     * @return string
     */
    protected function _getKnownInvalidXml()
    {
        return __DIR__ . '/_files/invalid_webapi.xml';
    }

    /**
     * The location of a single known valid partial xml file
     *
     * @return string
     */
    protected function _getKnownValidPartialXml()
    {
        return null;
    }

    /**
     * The location of a single known invalid partial xml file
     *
     * @return string
     */
    protected function _getKnownInvalidPartialXml()
    {
        return null;
    }

    /**
     * Returns the name of the XSD file to be used to validate the XSD
     *
     * @return string
     */
    protected function _getXsd()
    {
        return '/app/code/Magento/Webapi/etc/webapi.xsd';
    }

    /**
     * Returns the name of the XSD file to be used to validate partial XML
     *
     * @return string
     */
    protected function _getFileXsd()
    {
        return null;
    }
}
