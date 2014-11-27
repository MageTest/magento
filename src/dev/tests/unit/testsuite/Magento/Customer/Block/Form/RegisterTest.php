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
namespace Magento\Customer\Block\Form;

/**
 * Test class for \Magento\Customer\Block\Form\Register.
 */
class RegisterTest extends \PHPUnit_Framework_TestCase
{
    /** Constants used by the various unit tests */
    const POST_ACTION_URL = 'http://localhost/index.php/customer/account/createpost';

    const LOGIN_URL = 'http://localhost/index.php/customer/account/login';

    const COUNTRY_ID = 'US';

    const FORM_DATA = 'form_data';

    const REGION_ATTRIBUTE_VALUE = 'California';

    const REGION_ID_ATTRIBUTE_CODE = 'region_id';

    const REGION_ID_ATTRIBUTE_VALUE = '12';

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Core\Helper\Data */
    private $_coreData;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\Config\ScopeConfigInterface */
    private $_scopeConfig;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Model\Session */
    private $_customerSession;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Module\Manager */
    private $_moduleManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Model\Url */
    private $_customerUrl;

    /** @var Register */
    private $_block;

    public function setUp()
    {
        $this->_scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_moduleManager = $this->getMock('Magento\Framework\Module\Manager', array(), array(), '', false);
        $this->_coreData = $this->getMock('Magento\Core\Helper\Data', array(), array(), '', false);
        $this->_customerUrl = $this->getMock('Magento\Customer\Model\Url', array(), array(), '', false);
        $this->_customerSession = $this->getMock(
            'Magento\Customer\Model\Session',
            array('getCustomerFormData'),
            array(),
            '',
            false
        );

        $context = $this->getMock('Magento\Framework\View\Element\Template\Context', array(), array(), '', false);
        $context->expects($this->any())->method('getScopeConfig')->will($this->returnValue($this->_scopeConfig));

        $this->_block = new Register(
            $context,
            $this->_coreData,
            $this->getMockForAbstractClass('Magento\Framework\Json\EncoderInterface', array(), '', false),
            $this->getMock('Magento\Framework\App\Cache\Type\Config', array(), array(), '', false),
            $this->getMock('Magento\Directory\Model\Resource\Region\CollectionFactory', array(), array(), '', false),
            $this->getMock('Magento\Directory\Model\Resource\Country\CollectionFactory', array(), array(), '', false),
            $this->_moduleManager,
            $this->_customerSession,
            $this->_customerUrl
        );
    }

    /**
     * @param string $path
     * @param mixed $configValue
     *
     * @dataProvider getConfigProvider
     */
    public function testGetConfig($path, $configValue)
    {
        $this->_scopeConfig->expects($this->once())->method('getValue')->will($this->returnValue($configValue));
        $this->assertEquals($configValue, $this->_block->getConfig($path));
    }

    /**
     * @return array
     */
    public function getConfigProvider()
    {
        return array(
            array('/path/to/config/value', 'config value'),
            array('/path/to/config/value/that/does/not/exist', null)
        );
    }

    public function testGetPostActionUrl()
    {
        $this->_customerUrl->expects(
            $this->once()
        )->method(
            'getRegisterPostUrl'
        )->will(
            $this->returnValue(self::POST_ACTION_URL)
        );
        $this->assertEquals(self::POST_ACTION_URL, $this->_block->getPostActionUrl());
    }

    /**
     * Tests the use case where 'back_url' has not been set on the block.
     */
    public function testGetBackUrlNullData()
    {
        $this->_customerUrl->expects(
            $this->once()
        )->method(
            'getLoginUrl'
        )->will(
            $this->returnValue(self::LOGIN_URL)
        );
        $this->assertEquals(self::LOGIN_URL, $this->_block->getBackUrl());
    }

    /**
     * Tests the use case where 'back_url' has been set on the block.
     */
    public function testGetBackUrlNotNullData()
    {
        $this->_block->setData('back_url', self::LOGIN_URL);
        $this->assertEquals(self::LOGIN_URL, $this->_block->getBackUrl());
    }

    /**
     * Form data has been set on the block so Form\Register::getFormData() simply returns it.
     */
    public function testGetFormDataNotNullFormData()
    {
        $data = new \Magento\Framework\Object();
        $this->_block->setData(self::FORM_DATA, $data);
        $this->assertSame($data, $this->_block->getFormData());
    }

    /**
     * Form data has not been set on the block and there is no customer data in the customer session. So
     * we expect an empty \Magento\Framework\Object.
     */
    public function testGetFormDataNullFormData()
    {
        $data = new \Magento\Framework\Object();
        $this->_customerSession->expects($this->once())->method('getCustomerFormData')->will($this->returnValue(null));
        $this->assertEquals($data, $this->_block->getFormData());
        $this->assertEquals($data, $this->_block->getData(self::FORM_DATA));
    }

    /**
     * Form data has not been set on the block, but there is customer data from the customer session.
     * The customer data is something other than 'region_id' so that code path is skipped.
     */
    public function testGetFormDataNullFormDataCustomerFormData()
    {
        $data = new \Magento\Framework\Object();
        $data->setFirstname('John');
        $data->setCustomerData(1);
        $customerFormData = array('firstname' => 'John');
        $this->_customerSession->expects(
            $this->once()
        )->method(
            'getCustomerFormData'
        )->will(
            $this->returnValue($customerFormData)
        );
        $this->assertEquals($data, $this->_block->getFormData());
        $this->assertEquals($data, $this->_block->getData(self::FORM_DATA));
    }

    /**
     * Form data has not been set on the block, but there is customer data from the customer session.
     * The customer data is the 'region_id' so that code path is executed.
     */
    public function testGetFormDataCustomerFormDataRegionId()
    {
        $data = new \Magento\Framework\Object();
        $data->setRegionId(self::REGION_ID_ATTRIBUTE_VALUE);
        $data->setCustomerData(1);
        $data[self::REGION_ID_ATTRIBUTE_CODE] = (int)self::REGION_ID_ATTRIBUTE_VALUE;
        $customerFormData = array(self::REGION_ID_ATTRIBUTE_CODE => self::REGION_ID_ATTRIBUTE_VALUE);
        $this->_customerSession->expects(
            $this->once()
        )->method(
            'getCustomerFormData'
        )->will(
            $this->returnValue($customerFormData)
        );
        $formData = $this->_block->getFormData();
        $this->assertEquals($data, $formData);
        $this->assertTrue(isset($formData[self::REGION_ID_ATTRIBUTE_CODE]));
        $this->assertSame((int)self::REGION_ID_ATTRIBUTE_VALUE, $formData[self::REGION_ID_ATTRIBUTE_CODE]);
    }

    /**
     * Tests the Form\Register::getCountryId() use case where CountryId has been set on the form data
     * Object that has been set on the block.
     */
    public function testGetCountryIdFormData()
    {
        $formData = new \Magento\Framework\Object();
        $formData->setCountryId(self::COUNTRY_ID);
        $this->_block->setData(self::FORM_DATA, $formData);
        $this->assertEquals(self::COUNTRY_ID, $this->_block->getCountryId());
    }

    /**
     * Tests the default country use case of parent::getCountryId() where CountryId has not been set
     * and the 'country_id' attribute has also not been set.
     */
    public function testGetCountryIdParentNullData()
    {
        $this->_coreData->expects(
            $this->once()
        )->method(
            'getDefaultCountry'
        )->will(
            $this->returnValue(self::COUNTRY_ID)
        );
        $this->assertEquals(self::COUNTRY_ID, $this->_block->getCountryId());
    }

    /**
     * Tests the parent::getCountryId() use case where CountryId has not been set and the 'country_id'
     * attribute code has been set on the block.
     */
    public function testGetCountryIdParentNotNullData()
    {
        $this->_block->setData('country_id', self::COUNTRY_ID);
        $this->assertEquals(self::COUNTRY_ID, $this->_block->getCountryId());
    }

    /**
     * Tests the first if conditional of Form\Register::getRegion(), which checks to see if Region has
     * been set on the form data Object that's set on the block.
     */
    public function testGetRegionByRegion()
    {
        $formData = new \Magento\Framework\Object();
        $formData->setRegion(self::REGION_ATTRIBUTE_VALUE);
        $this->_block->setData(self::FORM_DATA, $formData);
        $this->assertSame(self::REGION_ATTRIBUTE_VALUE, $this->_block->getRegion());
    }

    /**
     * Tests the second if conditional of Form\Register::getRegion(), which checks to see if RegionId
     * has been set on the form data Object that's set on the block.
     */
    public function testGetRegionByRegionId()
    {
        $formData = new \Magento\Framework\Object();
        $formData->setRegionId(self::REGION_ID_ATTRIBUTE_VALUE);
        $this->_block->setData(self::FORM_DATA, $formData);
        $this->assertSame(self::REGION_ID_ATTRIBUTE_VALUE, $this->_block->getRegion());
    }

    /**
     * Neither Region, nor RegionId have been set on the form data Object that's set on the block so a
     * null value is expected.
     */
    public function testGetRegionNull()
    {
        $formData = new \Magento\Framework\Object();
        $this->_block->setData(self::FORM_DATA, $formData);
        $this->assertNull($this->_block->getRegion());
    }

    /**
     * @param $isNewsletterEnabled
     * @param $expectedValue
     *
     * @dataProvider isNewsletterEnabledProvider
     */
    public function testIsNewsletterEnabled($isNewsletterEnabled, $expectedValue)
    {
        $this->_moduleManager->expects(
            $this->once()
        )->method(
            'isOutputEnabled'
        )->with(
            'Magento_Newsletter'
        )->will(
            $this->returnValue($isNewsletterEnabled)
        );
        $this->assertEquals($expectedValue, $this->_block->isNewsletterEnabled());
    }

    /**
     * @return array
     */
    public function isNewsletterEnabledProvider()
    {
        return array(array(true, true), array(false, false));
    }

    /**
     * This test is designed to execute all code paths of Form\Register::getFormData() when testing the
     * Form\Register::restoreSessionData() method.
     */
    public function testRestoreSessionData()
    {
        $data = new \Magento\Framework\Object();
        $data->setRegionId(self::REGION_ID_ATTRIBUTE_VALUE);
        $data->setCustomerData(1);
        $data[self::REGION_ID_ATTRIBUTE_CODE] = (int)self::REGION_ID_ATTRIBUTE_VALUE;
        $customerFormData = array(self::REGION_ID_ATTRIBUTE_CODE => self::REGION_ID_ATTRIBUTE_VALUE);
        $this->_customerSession->expects(
            $this->once()
        )->method(
            'getCustomerFormData'
        )->will(
            $this->returnValue($customerFormData)
        );
        $form = $this->getMock('Magento\Customer\Model\Metadata\Form', array(), array(), '', false);
        $request = $this->getMockForAbstractClass('Magento\Framework\App\RequestInterface', array(), '', false);
        $formData = $this->_block->getFormData();
        $form->expects(
            $this->once()
        )->method(
            'prepareRequest'
        )->with(
            $formData->getData()
        )->will(
            $this->returnValue($request)
        );
        $form->expects(
            $this->once()
        )->method(
            'extractData'
        )->with(
            $request,
            null,
            false
        )->will(
            $this->returnValue($customerFormData)
        );
        $form->expects($this->once())->method('restoreData')->will($this->returnValue($customerFormData));
        $block = $this->_block->restoreSessionData($form, null, false);
        $this->assertSame($this->_block, $block);
        $this->assertEquals($data, $block->getData(self::FORM_DATA));
    }
}
