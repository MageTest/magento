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
namespace Magento\DesignEditor\Block\Adminhtml\Editor\Toolbar\Buttons;

/**
 * Save button block
 *
 * @method bool|null getHasThemeAssigned() If there is a theme that assigned to the store view
 * @method \Magento\DesignEditor\Block\Adminhtml\Editor\Toolbar\Buttons\Save setHasThemeAssigned(bool $flag)
 * @method \Magento\DesignEditor\Block\Adminhtml\Editor\Toolbar\Buttons\Save setMode(bool $flag)
 */
class Save extends \Magento\Backend\Block\Widget\Button\SplitButton
{
    /**
     * Current theme used for preview
     *
     * @var \Magento\Core\Model\Theme
     */
    protected $_theme;

    /**
     * Init save button
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function init()
    {
        $theme = $this->getTheme();
        $themeType = $theme->getType();
        if ($themeType == \Magento\Framework\View\Design\ThemeInterface::TYPE_PHYSICAL) {
            $this->_initPhysical();
        } else if ($themeType == \Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL) {
            if ($theme->getDomainModel(\Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL)->isAssigned()) {
                $this->_initAssigned();
            } else {
                $this->_initUnAssigned();
            }
        } else {
            throw new \InvalidArgumentException(
                sprintf('Invalid theme of a "%s" type passed to save button block', $themeType)
            );
        }

        return $this;
    }

    /**
     * Get current theme
     *
     * @return \Magento\Core\Model\Theme
     * @throws \InvalidArgumentException
     */
    public function getTheme()
    {
        if (null === $this->_theme) {
            throw new \InvalidArgumentException('Current theme was not passed to save button');
        }
        return $this->_theme;
    }

    /**
     * Set current theme
     *
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @return $this
     */
    public function setTheme($theme)
    {
        $this->_theme = $theme;

        return $this;
    }

    /**
     * Whether button is disabled
     *
     * @return bool
     */
    public function getDisabled()
    {
        return false;
    }

    /**
     * Disable actions-split functionality if no options provided
     *
     * @return bool
     */
    public function hasSplit()
    {
        $options = $this->getOptions();
        return is_array($options) && count($options) > 0;
    }

    /**
     * Get URL to apply changes from 'staging' theme to 'virtual' theme
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('adminhtml/system_design_editor/save', array('theme_id' => $this->getTheme()->getId()));
    }

    /**
     * Init 'Save' button for 'physical' theme
     *
     * @return $this
     */
    protected function _initPhysical()
    {
        $this->setData(
            array(
                'label' => __('Assign'),
                'data_attribute' => array('mage-init' => $this->_getAssignInitData()),
                'options' => array()
            )
        );

        return $this;
    }

    /**
     * Init 'Save' button for 'virtual' theme assigned to a store
     *
     * @return $this
     */
    protected function _initAssigned()
    {
        $this->setData(
            array(
                'label' => __('Save'),
                'data_attribute' => array('mage-init' => $this->_getSaveAssignedInitData()),
                'options' => array()
            )
        );

        return $this;
    }

    /**
     * Init 'Save' button for 'virtual' theme assigned to a store
     *
     * @return $this
     */
    protected function _initUnAssigned()
    {
        $this->setData(
            array(
                'label' => __('Save'),
                'data_attribute' => array('mage-init' => $this->_getSaveInitData()),
                'options' => array(
                    array(
                        'label' => __('Save & Assign'),
                        'data_attribute' => array('mage-init' => $this->_getSaveAndAssignInitData())
                    )
                )
            )
        );

        return $this;
    }

    /**
     * Get 'data-mage-init' attribute value for 'Save' button
     *
     * @return string
     */
    protected function _getSaveInitData()
    {
        $data = array(
            'button' => array(
                'event' => 'save',
                'target' => 'body',
                'eventData' => array(
                    'theme_id' => $this->getTheme()->getId(),
                    'save_url' => $this->getSaveUrl(),
                    'confirm' => false
                )
            )
        );

        return $this->_encode($data);
    }

    /**
     * Get 'data-mage-init' attribute value for 'Save' button when theme is live (assigned)
     *
     * @return string
     */
    protected function _getSaveAssignedInitData()
    {
        $message = __("You changed the design of your live store. Are you sure you want to do that?");
        $title = __("Save");

        $data = array(
            'button' => array(
                'event' => 'save',
                'target' => 'body',
                'eventData' => array(
                    'theme_id' => $this->getTheme()->getId(),
                    'save_url' => $this->getSaveUrl(),
                    'confirm' => array('message' => (string)$message, 'title' => (string)$title, 'buttons' => array())
                )
            )
        );

        return $this->_encode($data);
    }

    /**
     * Get 'data-mage-init' attribute value for 'Save' button
     *
     * @return string
     */
    protected function _getAssignInitData()
    {
        $message = __("Are you sure you want to change the theme of your live store?");
        $title = __("Assign");

        $data = array(
            'button' => array(
                'event' => 'assign',
                'target' => 'body',
                'eventData' => array(
                    'theme_id' => $this->getTheme()->getId(),
                    'confirm' => array('message' => (string)$message, 'title' => (string)$title)
                )
            )
        );

        return $this->_encode($data);
    }

    /**
     * Get 'data-mage-init' attribute value for 'Save and Assign' button
     *
     * Used in VDE when clicking button on top toolbar
     *
     * @return string
     */
    protected function _getSaveAndAssignInitData()
    {
        if ($this->getHasThemeAssigned()) {
            $message = __("Are you sure you want this theme to replace your current theme?");
        } else {
            $message = __("Do you want to use this theme in your live store?");
        }
        $title = __("Save & Assign");

        $data = array(
            'button' => array(
                'event' => 'save-and-assign',
                'target' => 'body',
                'eventData' => array(
                    'theme_id' => $this->getTheme()->getId(),
                    'save_url' => $this->getSaveUrl(),
                    'confirm' => array('message' => (string)$message, 'title' => (string)$title)
                )
            )
        );

        return $this->_encode($data);
    }

    /**
     * Get encoded data string
     *
     * @param array $data
     * @return string
     */
    protected function _encode($data)
    {
        return $this->escapeHtml(json_encode($data));
    }
}
