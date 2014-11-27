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
namespace Magento\Backend\Block\System\Config;
use Magento\Framework\App\Cache\State;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * @magentoAppArea adminhtml
 */
class FormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $_formFactory;

    /**
     * @var \Magento\Backend\Model\Config\Structure\Element\Section
     */
    protected $_section;

    /**
     * @var \Magento\Backend\Model\Config\Structure\Element\Group
     */
    protected $_group;

    /**
     * @var \Magento\Backend\Model\Config\Structure\Element\Field
     */
    protected $_field;

    /**
     * @var array
     */
    protected $_configData;

    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_formFactory = $this->_objectManager->create('Magento\Framework\Data\FormFactory');
    }

    public function testDependenceHtml()
    {
        /** @var $layout \Magento\Framework\View\LayoutInterface */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\View\Layout',
            array('area' => 'adminhtml')
        );
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\Config\ScopeInterface'
        )->setCurrentScope(
            \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE
        );
        /** @var $block \Magento\Backend\Block\System\Config\Form */
        $block = $layout->createBlock('Magento\Backend\Block\System\Config\Form', 'block');

        /** @var $childBlock \Magento\Framework\View\Element\Text */
        $childBlock = $layout->addBlock('Magento\Framework\View\Element\Text', 'element_dependence', 'block');

        $expectedValue = 'dependence_html_relations';
        $this->assertNotContains($expectedValue, $block->toHtml());

        $childBlock->setText($expectedValue);
        $this->assertContains($expectedValue, $block->toHtml());
    }

    /**
     * @covers \Magento\Backend\Block\System\Config\Form::initFields
     * @param bool $useConfigField uses the test_field_use_config field if true
     * @param bool $isConfigDataEmpty if the config data array should be empty or not
     * @param $configDataValue the value that the field path should be set to in the config data
     * @param bool $expectedUseDefault
     * @dataProvider initFieldsUseDefaultCheckboxDataProvider
     */
    public function testInitFieldsUseDefaultCheckbox(
        $useConfigField,
        $isConfigDataEmpty,
        $configDataValue,
        $expectedUseDefault
    ) {
        $this->_setupFieldsInheritCheckbox($useConfigField, $isConfigDataEmpty, $configDataValue);

        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\Config\ScopeInterface'
        )->setCurrentScope(
            \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE
        );
        $form = $this->_formFactory->create();
        $fieldset = $form->addFieldset($this->_section->getId() . '_' . $this->_group->getId(), array());

        /* @TODO Eliminate stub by proper mock / config fixture usage */
        /** @var $block \Magento\Backend\Block\System\Config\FormStub */
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Backend\Block\System\Config\FormStub'
        );
        $block->setScope(\Magento\Backend\Block\System\Config\Form::SCOPE_WEBSITES);
        $block->setStubConfigData($this->_configData);
        $block->initFields($fieldset, $this->_group, $this->_section);

        $fieldsetSel = 'fieldset';
        $valueSel = sprintf(
            'input#%s_%s_%s',
            $this->_section->getId(),
            $this->_group->getId(),
            $this->_field->getId()
        );
        $valueDisabledSel = sprintf('%s[disabled="disabled"]', $valueSel);
        $useDefaultSel = sprintf(
            'input#%s_%s_%s_inherit.checkbox',
            $this->_section->getId(),
            $this->_group->getId(),
            $this->_field->getId()
        );
        $useDefaultCheckedSel = sprintf('%s[checked="checked"]', $useDefaultSel);
        $fieldsetHtml = $fieldset->getElementHtml();

        $this->assertSelectCount($fieldsetSel, true, $fieldsetHtml, 'Fieldset HTML is invalid');
        $this->assertSelectCount($valueSel, true, $fieldsetHtml, 'Field input not found in fieldset HTML');
        $this->assertSelectCount(
            $useDefaultSel,
            true,
            $fieldsetHtml,
            '"Use Default" checkbox not found in fieldset HTML'
        );

        if ($expectedUseDefault) {
            $this->assertSelectCount(
                $useDefaultCheckedSel,
                true,
                $fieldsetHtml,
                '"Use Default" checkbox should be checked'
            );
            $this->assertSelectCount($valueDisabledSel, true, $fieldsetHtml, 'Field input should be disabled');
        } else {
            $this->assertSelectCount(
                $useDefaultCheckedSel,
                false,
                $fieldsetHtml,
                '"Use Default" checkbox should not be checked'
            );
            $this->assertSelectCount($valueDisabledSel, false, $fieldsetHtml, 'Field input should not be disabled');
        }
    }

    /**
     * @return array
     */
    public static function initFieldsUseDefaultCheckboxDataProvider()
    {
        return array(
            array(false, true, null, true),
            array(false, false, null, false),
            array(false, false, '', false),
            array(false, false, 'value', false),
            array(true, false, 'config value', false)
        );
    }

    /**
     * @covers \Magento\Backend\Block\System\Config\Form::initFields
     * @param bool $useConfigField uses the test_field_use_config field if true
     * @param bool $isConfigDataEmpty if the config data array should be empty or not
     * @param $configDataValue the value that the field path should be set to in the config data
     * @dataProvider initFieldsUseConfigPathDataProvider
     * @magentoConfigFixture default/test_config_section/test_group_config_node/test_field_value config value
     */
    public function testInitFieldsUseConfigPath($useConfigField, $isConfigDataEmpty, $configDataValue)
    {
        $this->_setupFieldsInheritCheckbox($useConfigField, $isConfigDataEmpty, $configDataValue);

        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\Config\ScopeInterface'
        )->setCurrentScope(
            \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE
        );
        $form = $this->_formFactory->create();
        $fieldset = $form->addFieldset($this->_section->getId() . '_' . $this->_group->getId(), array());

        /* @TODO Eliminate stub by proper mock / config fixture usage */
        /** @var $block \Magento\Backend\Block\System\Config\FormStub */
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Backend\Block\System\Config\FormStub'
        );
        $block->setScope(\Magento\Backend\Block\System\Config\Form::SCOPE_DEFAULT);
        $block->setStubConfigData($this->_configData);
        $block->initFields($fieldset, $this->_group, $this->_section);

        $fieldsetSel = 'fieldset';
        $valueSel = sprintf(
            'input#%s_%s_%s',
            $this->_section->getId(),
            $this->_group->getId(),
            $this->_field->getId()
        );
        $fieldsetHtml = $fieldset->getElementHtml();

        $this->assertSelectCount($fieldsetSel, true, $fieldsetHtml, 'Fieldset HTML is invalid');
        $this->assertSelectCount($valueSel, true, $fieldsetHtml, 'Field input not found in fieldset HTML');
    }

    /**
     * @return array
     */
    public static function initFieldsUseConfigPathDataProvider()
    {
        return array(
            array(false, true, null),
            array(false, false, null),
            array(false, false, ''),
            array(false, false, 'value'),
            array(true, false, 'config value')
        );
    }

    /**
     * @param bool $useConfigField uses the test_field_use_config field if true
     * @param bool $isConfigDataEmpty if the config data array should be empty or not
     * @param $configDataValue the value that the field path should be set to in the config data
     */
    protected function _setupFieldsInheritCheckbox($useConfigField, $isConfigDataEmpty, $configDataValue)
    {
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize(array(
            State::PARAM_BAN_CACHE => true,
        ));
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Framework\Config\ScopeInterface')
            ->setCurrentScope(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\App\AreaList')
            ->getArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE)
            ->load(\Magento\Framework\App\Area::PART_CONFIG);

        $fileResolverMock = $this->getMockBuilder(
            'Magento\Framework\App\Config\FileResolver'
        )->disableOriginalConstructor()->getMock();
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Framework\Filesystem');
        /** @var $directory  \Magento\Framework\Filesystem\Directory\Read */
        $directory = $filesystem->getDirectoryRead(DirectoryList::ROOT);
        $fileIteratorFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\Config\FileIteratorFactory'
        );
        $fileIterator = $fileIteratorFactory->create(
            $directory,
            array($directory->getRelativePath(__DIR__ . '/_files/test_section_config.xml'))
        );
        $fileResolverMock->expects($this->any())->method('get')->will($this->returnValue($fileIterator));

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $structureReader = $objectManager->create(
            'Magento\Backend\Model\Config\Structure\Reader',
            array('fileResolver' => $fileResolverMock)
        );
        $structureData = $objectManager->create(
            'Magento\Backend\Model\Config\Structure\Data',
            array('reader' => $structureReader)
        );
        /** @var \Magento\Backend\Model\Config\Structure $structure  */
        $structure = $objectManager->create(
            'Magento\Backend\Model\Config\Structure',
            array('structureData' => $structureData)
        );

        $this->_section = $structure->getElement('test_section');

        $this->_group = $structure->getElement('test_section/test_group');

        if ($useConfigField) {
            $this->_field = $structure->getElement('test_section/test_group/test_field_use_config');
        } else {
            $this->_field = $structure->getElement('test_section/test_group/test_field');
        }
        $fieldPath = $this->_field->getConfigPath();

        if ($isConfigDataEmpty) {
            $this->_configData = array();
        } else {
            $this->_configData = array($fieldPath => $configDataValue);
        }
    }

    public function testInitFormAddsFieldsets()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\App\ResponseInterface'
        )->headersSentThrowsException = false;
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\App\RequestInterface'
        )->setParam(
            'section',
            'general'
        );
        /** @var $block \Magento\Backend\Block\System\Config\Form */
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Backend\Block\System\Config\Form'
        );
        $block->initForm();
        $expectedIds = array(
            'general_country' => array(
                'general_country_default' => 'select',
                'general_country_allow' => 'select',
                'general_country_optional_zip_countries' => 'select',
                'general_country_eu_countries' => 'select'
            ),
            'general_region' => array(
                'general_region_state_required' => 'select',
                'general_region_display_all' => 'select'
            ),
            'general_locale' => array(
                'general_locale_timezone' => 'select',
                'general_locale_code' => 'select',
                'general_locale_firstday' => 'select',
                'general_locale_weekend' => 'select'
            ),
            'general_restriction' => array(
                'general_restriction_is_active' => 'select',
                'general_restriction_mode' => 'select',
                'general_restriction_http_redirect' => 'select',
                'general_restriction_cms_page' => 'select',
                'general_restriction_http_status' => 'select'
            ),
            'general_store_information' => array(
                'general_store_information_name' => 'text',
                'general_store_information_phone' => 'text',
                'general_store_information_merchant_country' => 'select',
                'general_store_information_merchant_vat_number' => 'text',
                'general_store_information_validate_vat_number' => 'text',
                'general_store_information_address' => 'textarea'
            ),
            'general_single_store_mode' => array('general_single_store_mode_enabled' => 'select')
        );
        $elements = $block->getForm()->getElements();
        foreach ($elements as $element) {
            /** @var $element \Magento\Framework\Data\Form\Element\Fieldset */
            $this->assertInstanceOf('Magento\Framework\Data\Form\Element\Fieldset', $element);
            $this->assertArrayHasKey($element->getId(), $expectedIds);
            $fields = $element->getElements();
            $this->assertEquals(count($expectedIds[$element->getId()]), count($fields));
            foreach ($element->getElements() as $field) {
                $this->assertArrayHasKey($field->getId(), $expectedIds[$element->getId()]);
                $this->assertEquals($expectedIds[$element->getId()][$field->getId()], $field->getType());
            }
        }
    }
}
