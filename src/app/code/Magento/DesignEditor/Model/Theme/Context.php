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
namespace Magento\DesignEditor\Model\Theme;

use Magento\Framework\Model\Exception as CoreException;

/**
 * Design editor theme context
 */
class Context
{
    /**
     * @var \Magento\Core\Model\ThemeFactory
     */
    protected $_themeFactory;

    /**
     * @var \Magento\Theme\Model\CopyService
     */
    protected $_copyService;

    /**
     * @var \Magento\Core\Model\Theme
     */
    protected $_theme;

    /**
     * @var \Magento\Core\Model\Theme
     */
    protected $_stagingTheme;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Core\Model\ThemeFactory $themeFactory
     * @param \Magento\Theme\Model\CopyService $copyService
     */
    public function __construct(
        \Magento\Core\Model\ThemeFactory $themeFactory,
        \Magento\Theme\Model\CopyService $copyService
    ) {
        $this->_themeFactory = $themeFactory;
        $this->_copyService = $copyService;
    }

    /**
     * Reset checked theme
     *
     * @return $this
     */
    public function reset()
    {
        $this->_theme = null;
        return $this;
    }

    /**
     * Set theme which will be editable in store designer
     *
     * @param int $themeId
     * @return $this
     * @throws CoreException
     */
    public function setEditableThemeById($themeId)
    {
        $this->_theme = $this->_themeFactory->create();
        if (!$this->_theme->load($themeId)->getId()) {
            throw new CoreException(__('We can\'t find theme "%1".', $themeId));
        }
        if ($this->_theme->getType() === \Magento\Framework\View\Design\ThemeInterface::TYPE_STAGING) {
            throw new CoreException(__('Wrong theme type set as editable'));
        }
        return $this;
    }

    /**
     * Get current editable theme
     *
     * @return \Magento\Core\Model\Theme
     * @throws CoreException
     */
    public function getEditableTheme()
    {
        if (null === $this->_theme) {
            throw new CoreException(__('Theme has not been set'));
        }
        return $this->_theme;
    }

    /**
     * Get staging theme
     *
     * @return \Magento\Core\Model\Theme
     * @throws CoreException
     */
    public function getStagingTheme()
    {
        if (null === $this->_stagingTheme) {
            $editableTheme = $this->getEditableTheme();
            if (!$editableTheme->isVirtual()) {
                throw new CoreException(__('Theme "%1" is not editable.', $editableTheme->getThemeTitle()));
            }
            $this->_stagingTheme = $editableTheme->getDomainModel(
                \Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL
            )->getStagingTheme();
        }
        return $this->_stagingTheme;
    }

    /**
     * Theme which can be rendered on store designer
     *
     * @return \Magento\Core\Model\Theme
     */
    public function getVisibleTheme()
    {
        $editableTheme = $this->getEditableTheme();
        return $editableTheme->isVirtual() ? $this->getStagingTheme() : $editableTheme;
    }

    /**
     * Copy all changed data related to launched theme from staging theme
     *
     * @return $this
     */
    public function copyChanges()
    {
        $this->_copyService->copy($this->getStagingTheme(), $this->getEditableTheme());
        return $this;
    }
}
