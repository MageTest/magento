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
namespace Magento\DesignEditor\Block\Adminhtml\Editor\Tools\QuickStyles;

/**
 * Block that renders Quick Styles tabs
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractTab extends \Magento\Backend\Block\Widget\Form
{
    /**
     * Form factory for VDE "Quick Styles" tab
     *
     * @var \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Form\Builder
     */
    protected $_formBuilder;

    /**
     * Theme context
     *
     * @var \Magento\DesignEditor\Model\Theme\Context
     */
    protected $_themeContext;

    /**
     * Tab form HTML identifier
     *
     * @var string
     */
    protected $_formId = null;

    /**
     * Controls group which will be rendered on the tab form
     *
     * @var string
     */
    protected $_tab = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Form\Builder $formBuilder
     * @param \Magento\DesignEditor\Model\Theme\Context $themeContext
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Form\Builder $formBuilder,
        \Magento\DesignEditor\Model\Theme\Context $themeContext,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->_formBuilder = $formBuilder;
        $this->_themeContext = $themeContext;
    }

    /**
     * Create a form element with necessary controls
     *
     * @return \Magento\DesignEditor\Block\Adminhtml\Editor\Tools\QuickStyles\Header
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _prepareForm()
    {
        if (!$this->_formId || !$this->_tab) {
            throw new \Magento\Framework\Model\Exception(
                __('We found an invalid block of class "%1". Please define the required properties.', get_class($this))
            );
        }
        $form = $this->_formBuilder->create(
            array(
                'id' => $this->_formId,
                'action' => '#',
                'method' => 'post',
                'tab' => $this->_tab,
                'theme' => $this->_themeContext->getStagingTheme(),
                'parent_theme' => $this->_themeContext->getEditableTheme()->getParentTheme()
            )
        );
        $form->setUseContainer(true);

        $this->setForm($form);

        parent::_prepareForm();
        return $this;
    }
}
