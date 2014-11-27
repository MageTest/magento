<?php
/**
 * Test SOAP server model.
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
namespace Magento\Webapi\Model\Soap;

class ServerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configScopeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_areaListMock;

    /** @var \Magento\Webapi\Controller\Soap\Request */
    protected $_requestMock;

    /** @var \Magento\Framework\DomDocument\Factory */
    protected $_domDocumentFactory;

    /** @var \Magento\Store\Model\Store */
    protected $_storeMock;

    /** @var \Magento\Framework\StoreManagerInterface */
    protected $_storeManagerMock;

    /** @var \Magento\Webapi\Model\Soap\Server\Factory */
    protected $_soapServerFactory;

    /** @var \Magento\Framework\Reflection\TypeProcessor */
    protected $_typeProcessor;

    /** @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject */
    protected $_configMock;

    protected function setUp()
    {
        $this->_storeManagerMock = $this->getMockBuilder(
            'Magento\Store\Model\StoreManager'
        )->disableOriginalConstructor()->getMock();
        $this->_storeMock = $this->getMockBuilder(
            'Magento\Store\Model\Store'
        )->disableOriginalConstructor()->getMock();

        $this->_areaListMock = $this->getMock('Magento\Framework\App\AreaList', array(), array(), '', false);
        $this->_configScopeMock = $this->getMock('Magento\Framework\Config\ScopeInterface');
        $this->_storeManagerMock->expects(
            $this->any()
        )->method(
            'getStore'
        )->will(
            $this->returnValue($this->_storeMock)
        );
        $this->_requestMock = $this->getMockBuilder(
            'Magento\Webapi\Controller\Soap\Request'
        )->disableOriginalConstructor()->getMock();
        $this->_domDocumentFactory = $this->getMockBuilder(
            'Magento\Framework\DomDocument\Factory'
        )->disableOriginalConstructor()->getMock();
        $this->_soapServerFactory = $this->getMockBuilder(
            'Magento\Webapi\Model\Soap\Server\Factory'
        )->disableOriginalConstructor()->getMock();
        $this->_typeProcessor = $this->getMock(
            'Magento\Framework\Reflection\TypeProcessor',
            array(),
            array(),
            '',
            false
        );
        $this->_configMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');

        parent::setUp();
    }

    /**
     * Test SOAP server construction with WSDL cache enabling.
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testConstructEnableWsdlCache()
    {
        /** Mock getConfig method to return true. */
        $this->_configMock->expects($this->once())->method('isSetFlag')->will($this->returnValue(true));
        /** Create SOAP server object. */
        $server = new \Magento\Webapi\Model\Soap\Server(
            $this->_areaListMock,
            $this->_configScopeMock,
            $this->_requestMock,
            $this->_domDocumentFactory,
            $this->_storeManagerMock,
            $this->_soapServerFactory,
            $this->_typeProcessor,
            $this->_configMock
        );
        /** Assert that SOAP WSDL caching option was enabled after SOAP server initialization. */
        $this->assertTrue((bool)ini_get('soap.wsdl_cache_enabled'), 'WSDL caching was not enabled.');
    }

    /**
     * Test SOAP server construction with WSDL cache disabling.
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testConstructDisableWsdlCache()
    {
        /** Mock getConfig method to return false. */
        $this->_configMock->expects($this->once())->method('isSetFlag')->will($this->returnValue(false));
        /** Create SOAP server object. */
        $server = new \Magento\Webapi\Model\Soap\Server(
            $this->_areaListMock,
            $this->_configScopeMock,
            $this->_requestMock,
            $this->_domDocumentFactory,
            $this->_storeManagerMock,
            $this->_soapServerFactory,
            $this->_typeProcessor,
            $this->_configMock
        );
        /** Assert that SOAP WSDL caching option was disabled after SOAP server initialization. */
        $this->assertFalse((bool)ini_get('soap.wsdl_cache_enabled'), 'WSDL caching was not disabled.');
    }
}
