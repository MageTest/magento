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
namespace Magento\Integration\Block\Adminhtml\Integration\Edit\Tab;

use Magento\Integration\Controller\Adminhtml\Integration;
use Magento\Integration\Model\Integration as IntegrationModel;

/**
 * Main Integration info edit form
 *
 */
class Info extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**#@+
     * Form elements names.
     */
    const HTML_ID_PREFIX = 'integration_properties_';

    const DATA_ID = 'integration_id';

    const DATA_NAME = 'name';

    const DATA_EMAIL = 'email';

    const DATA_ENDPOINT = 'endpoint';

    const DATA_IDENTITY_LINK_URL = 'identity_link_url';

    const DATA_SETUP_TYPE = 'setup_type';

    const DATA_CONSUMER_ID = 'consumer_id';

    /**#@-*/

    /**
     * Set form id prefix, declare fields for integration info
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix(self::HTML_ID_PREFIX);
        $integrationData = $this->_coreRegistry->registry(Integration::REGISTRY_KEY_CURRENT_INTEGRATION);
        $this->_addGeneralFieldset($form, $integrationData);
        $this->_addDetailsFieldset($form, $integrationData);
        $form->setValues($integrationData);
        $this->setForm($form);
        return $this;
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Integration Info');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Add fieldset with general integration information.
     *
     * @param \Magento\Framework\Data\Form $form
     * @param array $integrationData
     * @return void
     */
    protected function _addGeneralFieldset($form, $integrationData)
    {
        $fieldset = $form->addFieldset('base_fieldset', array('legend' => __('General')));

        $disabled = false;
        if (isset($integrationData[self::DATA_ID])) {
            $fieldset->addField(self::DATA_ID, 'hidden', array('name' => 'id'));

            if ($integrationData[self::DATA_SETUP_TYPE] == IntegrationModel::TYPE_CONFIG) {
                $disabled = true;
            }
        }

        $fieldset->addField(
            self::DATA_NAME,
            'text',
            array(
                'label' => __('Name'),
                'name' => self::DATA_NAME,
                'required' => true,
                'disabled' => $disabled,
                'maxlength' => '255'
            )
        );
        $fieldset->addField(
            self::DATA_EMAIL,
            'text',
            array(
                'label' => __('Email'),
                'name' => self::DATA_EMAIL,
                'disabled' => $disabled,
                'class' => 'validate-email',
                'maxlength' => '254'
            )
        );
        $fieldset->addField(
            self::DATA_ENDPOINT,
            'text',
            array(
                'label' => __('Callback URL'),
                'name' => self::DATA_ENDPOINT,
                'disabled' => $disabled,
                // @codingStandardsIgnoreStart
                'note' => __(
                    'Enter URL where Oauth credentials can be sent when using Oauth for token exchange. We strongly recommend using https://.'
                )
                // @codingStandardsIgnoreEnd
            )
        );
        $fieldset->addField(
            self::DATA_IDENTITY_LINK_URL,
            'text',
            array(
                'label' => __('Identity link URL'),
                'name' => self::DATA_IDENTITY_LINK_URL,
                'disabled' => $disabled,
                'note' => __(
                    'URL to redirect user to link their 3rd party account with this Magento integration credentials.'
                )
            )
        );
    }

    /**
     * Add fieldset with integration details. This fieldset is available for existing integrations only.
     *
     * @param \Magento\Framework\Data\Form $form
     * @param array $integrationData
     * @return void
     */
    protected function _addDetailsFieldset($form, $integrationData)
    {
        if (isset($integrationData[self::DATA_ID])) {
            $fieldset = $form->addFieldset('details_fieldset', array('legend' => __('Integration Details')));
            /** @var \Magento\Integration\Block\Adminhtml\Integration\Tokens $tokensBlock */
            $tokensBlock = $this->getChildBlock('integration_tokens');
            foreach ($tokensBlock->getFormFields() as $field) {
                $fieldset->addField($field['name'], $field['type'], $field['metadata']);
            }
        }
    }
}
