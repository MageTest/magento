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
namespace Magento\Email\Block\Adminhtml\Template;

/**
 * Adminhtml system template edit block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @method array getTemplateOptions()
 */
class Edit extends \Magento\Backend\Block\Widget implements \Magento\Backend\Block\Widget\ContainerInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registryManager;

    /**
     * @var \Magento\Backend\Model\Menu\Config
     */
    protected $_menuConfig;

    /**
     * @var \Magento\Backend\Model\Config\Structure
     */
    protected $_configStructure;

    /**
     * @var \Magento\Email\Model\Template\Config
     */
    private $_emailConfig;

    /**
     * Template file
     *
     * @var string
     */
    protected $_template = 'template/edit.phtml';

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreHelper;

    /**
     * @var \Magento\Backend\Block\Widget\Button\ButtonList
     */
    protected $buttonList;

    /**
     * @var \Magento\Backend\Block\Widget\Button\ToolbarInterface
     */
    protected $toolbar;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Model\Menu\Config $menuConfig
     * @param \Magento\Backend\Model\Config\Structure $configStructure
     * @param \Magento\Email\Model\Template\Config $emailConfig
     * @param \Magento\Core\Helper\Data $coreHelper
     * @param \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
     * @param \Magento\Backend\Block\Widget\Button\ToolbarInterface $toolbar
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\Menu\Config $menuConfig,
        \Magento\Backend\Model\Config\Structure $configStructure,
        \Magento\Email\Model\Template\Config $emailConfig,
        \Magento\Core\Helper\Data $coreHelper,
        \Magento\Backend\Block\Widget\Button\ButtonList $buttonList,
        \Magento\Backend\Block\Widget\Button\ToolbarInterface $toolbar,
        array $data = array()
    ) {
        $this->_coreHelper = $coreHelper;
        $this->_jsonEncoder = $jsonEncoder;
        $this->_registryManager = $registry;
        $this->_menuConfig = $menuConfig;
        $this->_configStructure = $configStructure;
        $this->_emailConfig = $emailConfig;
        $this->buttonList = $buttonList;
        $this->toolbar = $toolbar;
        parent::__construct($context, $data);
    }

    /**
     *{@inheritdoc}
     */
    public function updateButton($buttonId, $key, $data)
    {
        $this->buttonList->update($buttonId, $key, $data);
        return $this;
    }

    /**
     *{@inheritdoc}
     */
    public function canRender(\Magento\Backend\Block\Widget\Button\Item $item)
    {
        return !$item->isDeleted();
    }

    /**
     *{@inheritdoc}
     */
    public function removeButton($buttonId)
    {
        $this->buttonList->remove($buttonId);
        return $this;
    }

    /**
     * Prepare layout
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareLayout()
    {
        $this->buttonList->add(
            'back',
            array(
                'label' => __('Back'),
                'onclick' => "window.location.href = '" . $this->getUrl('adminhtml/*') . "'",
                'class' => 'back'
            )
        );
        $this->buttonList->add(
            'reset',
            array('label' => __('Reset'), 'onclick' => 'window.location.href = window.location.href')
        );

        if ($this->getEditMode()) {
            $this->buttonList->add(
                'delete',
                array(
                    'label' => __('Delete Template'),
                    'data_attribute' => array(
                        'role' => 'template-delete'
                    ),
                    'class' => 'delete'
                )
            );
        }
        if (!$this->isTextType()) {
            $this->buttonList->add(
                'to_plain',
                array(
                    'label' => __('Convert to Plain Text'),
                    'data_attribute' => array(
                        'role' => 'template-strip'
                    ),
                    'id' => 'convert_button'
                )
            );
            $this->buttonList->add(
                'to_html',
                array(
                    'label' => __('Return Html Version'),
                    'data_attribute' => array(
                        'role' => 'template-unstrip'
                    ),
                    'id' => 'convert_button_back',
                    'style' => 'display:none'
                )
            );
        }
        $this->buttonList->add(
            'preview',
            array(
                'label' => __('Preview Template'),
                'data_attribute' => array(
                    'role' => 'template-preview'
                )
            )
        );
        $this->buttonList->add(
            'save',
            array(
                'label' => __('Save Template'),
                'data_attribute' => array(
                    'role' => 'template-save'
                ),
                'class' => 'save primary save-template'
            )
        );
        $this->buttonList->add(
            'load',
            array(
                'label' => __('Load Template'),
                'data_attribute' => array(
                    'role' => 'template-load'
                ),
                'type' => 'button',
                'class' => 'save'
            ),
            0,
            0,
            null
        );
        $this->toolbar->pushButtons($this, $this->buttonList);
        $this->addChild('form', 'Magento\Email\Block\Adminhtml\Template\Edit\Form');
        return parent::_prepareLayout();
    }

    /**
     *{@inheritdoc}
     */
    public function addButton($buttonId, $data, $level = 0, $sortOrder = 0, $region = 'toolbar')
    {
        $this->buttonList->add($buttonId, $data, $level, $sortOrder, $region);
        return $this;
    }

    /**
     * Collect, sort and set template options
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $groupedOptions = array();
        foreach ($this->_getDefaultTemplatesAsOptionsArray() as $option) {
            $groupedOptions[$option['group']][] = $option;
        }
        ksort($groupedOptions);
        $this->setData('template_options', $groupedOptions);

        return parent::_beforeToHtml();
    }

    /**
     * Get default templates as options array
     *
     * @return array
     */
    protected function _getDefaultTemplatesAsOptionsArray()
    {
        $options = array(array('value' => '', 'label' => '', 'group' => ''));
        foreach ($this->_emailConfig->getAvailableTemplates() as $templateId) {
            $options[] = array(
                'value' => $templateId,
                'label' => $this->_emailConfig->getTemplateLabel($templateId),
                'group' => $this->_emailConfig->getTemplateModule($templateId)
            );
        }
        uasort(
            $options,
            function (array $firstElement, array $secondElement) {
                return strcmp($firstElement['label'], $secondElement['label']);
            }
        );
        return $options;
    }

    /**
     * Get the html element for load button
     *
     * @return string
     */
    public function getLoadButtonHtml()
    {
        return $this->getChildHtml('load_button');
    }

    /**
     * Return edit flag for block
     *
     * @return int|null
     */
    public function getEditMode()
    {
        return $this->getEmailTemplate()->getId();
    }

    /**
     * Return header text for form
     *
     * @return string
     */
    public function getHeaderText()
    {
        if ($this->getEditMode()) {
            return __('Edit Email Template');
        }
        return __('New Email Template');
    }

    /**
     * Return form block HTML
     *
     * @return string
     */
    public function getFormHtml()
    {
        return $this->getChildHtml('form');
    }

    /**
     * Return action url for form
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('adminhtml/*/save', array('_current' => true));
    }

    /**
     * Return preview action url for form
     *
     * @return string
     */
    public function getPreviewUrl()
    {
        return $this->getUrl('adminhtml/*/preview');
    }

    /**
     * Return true if template type is text; return false otherwise
     *
     * @return bool
     */
    public function isTextType()
    {
        return $this->getEmailTemplate()->isPlain();
    }

    /**
     * Return delete url for customer group
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('adminhtml/*/delete', array('_current' => true));
    }

    /**
     * Retrieve email template model
     *
     * @return \Magento\Email\Model\Template
     */
    public function getEmailTemplate()
    {
        return $this->_registryManager->registry('current_email_template');
    }

    /**
     * Load template url
     *
     * @return string
     */
    public function getLoadUrl()
    {
        return $this->getUrl('adminhtml/*/defaultTemplate');
    }

    /**
     * Get paths of where current template is used as default
     *
     * @param bool $asJSON
     * @return string
     */
    public function getUsedDefaultForPaths($asJSON = true)
    {
        /** @var $template \Magento\Email\Model\BackendTemplate */
        $template = $this->getEmailTemplate();
        $paths = $template->getSystemConfigPathsWhereUsedAsDefault();
        $pathsParts = $this->_getSystemConfigPathsParts($paths);
        if ($asJSON) {
            return $this->_coreHelper->jsonEncode($pathsParts);
        }
        return $pathsParts;
    }

    /**
     * Get paths of where current template is currently used
     *
     * @param bool $asJSON
     * @return string
     */
    public function getUsedCurrentlyForPaths($asJSON = true)
    {
        /** @var $template \Magento\Email\Model\BackendTemplate */
        $template = $this->getEmailTemplate();
        $paths = $template->getSystemConfigPathsWhereUsedCurrently();
        $pathsParts = $this->_getSystemConfigPathsParts($paths);
        if ($asJSON) {
            return $this->_jsonEncoder->encode($pathsParts);
        }
        return $pathsParts;
    }

    /**
     * Convert xml config paths to decorated names
     *
     * @param array $paths
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _getSystemConfigPathsParts($paths)
    {
        $result = $urlParams = $prefixParts = array();
        $scopeLabel = __('GLOBAL');
        if ($paths) {
            /** @var $menu \Magento\Backend\Model\Menu */
            $menu = $this->_menuConfig->getMenu();
            $item = $menu->get('Magento_Backend::stores');
            // create prefix path parts
            $prefixParts[] = array('title' => __($item->getTitle()));
            $item = $menu->get('Magento_Backend::system_config');
            $prefixParts[] = array(
                'title' => __($item->getTitle()),
                'url' => $this->getUrl('adminhtml/system_config/')
            );

            $pathParts = $prefixParts;
            foreach ($paths as $pathData) {
                $pathDataParts = explode('/', $pathData['path']);
                $sectionName = array_shift($pathDataParts);

                $urlParams = array('section' => $sectionName);
                if (isset($pathData['scope']) && isset($pathData['scope_id'])) {
                    switch ($pathData['scope']) {
                        case 'stores':
                            $store = $this->_storeManager->getStore($pathData['scope_id']);
                            if ($store) {
                                $urlParams['website'] = $store->getWebsite()->getCode();
                                $urlParams['store'] = $store->getCode();
                                $scopeLabel = $store->getWebsite()->getName() . '/' . $store->getName();
                            }
                            break;
                        case 'websites':
                            $website = $this->_storeManager->getWebsite($pathData['scope_id']);
                            if ($website) {
                                $urlParams['website'] = $website->getCode();
                                $scopeLabel = $website->getName();
                            }
                            break;
                        default:
                            break;
                    }
                }
                $pathParts[] = array(
                    'title' => $this->_configStructure->getElement($sectionName)->getLabel(),
                    'url' => $this->getUrl('adminhtml/system_config/edit', $urlParams)
                );
                $elementPathParts = array($sectionName);
                while (count($pathDataParts) != 1) {
                    $elementPathParts[] = array_shift($pathDataParts);
                    $pathParts[] = array(
                        'title' => $this->_configStructure->getElementByPathParts($elementPathParts)->getLabel()
                    );
                }
                $elementPathParts[] = array_shift($pathDataParts);
                $pathParts[] = array(
                    'title' => $this->_configStructure->getElementByPathParts($elementPathParts)->getLabel(),
                    'scope' => $scopeLabel
                );
                $result[] = $pathParts;
                $pathParts = $prefixParts;
            }
        }
        return $result;
    }

    /**
     * Return original template code of current template
     *
     * @return string
     */
    public function getOrigTemplateCode()
    {
        return $this->getEmailTemplate()->getOrigTemplateCode();
    }
}
