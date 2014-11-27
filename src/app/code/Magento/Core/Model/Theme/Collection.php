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
namespace Magento\Core\Model\Theme;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Design\Theme\ListInterface;
use Magento\Framework\View\Design\ThemeInterface;

/**
 * Theme filesystem collection
 */
class Collection extends \Magento\Framework\Data\Collection implements ListInterface
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\Read
     */
    protected $_directory;

    /**
     * Model of collection item
     *
     * @var string
     */
    protected $_itemObjectClass = 'Magento\Core\Model\Theme';

    /**
     * Target directory
     *
     * @var array
     */
    protected $_targetDirs = array();

    /**
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Framework\Filesystem $filesystem
    ) {
        parent::__construct($entityFactory);
        $this->_directory = $filesystem->getDirectoryRead(DirectoryList::THEMES);
    }

    /**
     * Add default pattern to themes configuration
     *
     * @param string $area
     * @return $this
     */
    public function addDefaultPattern($area = \Magento\Framework\App\Area::AREA_FRONTEND)
    {
        $this->addTargetPattern(implode('/', array($area, '{*/*,*/}', 'theme.xml')));
        return $this;
    }

    /**
     * Target directory setter. Adds directory to be scanned
     *
     * @param string $relativeTarget
     * @return $this
     */
    public function addTargetPattern($relativeTarget)
    {
        if ($this->isLoaded()) {
            $this->clear();
        }
        $this->_targetDirs[] = $relativeTarget;
        return $this;
    }

    /**
     * Clear target patterns
     *
     * @return $this
     */
    public function clearTargetPatterns()
    {
        $this->_targetDirs = array();
        return $this;
    }

    /**
     * Return target dir for themes with theme configuration file
     *
     * @throws \Magento\Framework\Exception
     * @return array|string
     */
    public function getTargetPatterns()
    {
        if (empty($this->_targetDirs)) {
            throw new \Magento\Framework\Exception('Please specify at least one target pattern to theme config file.');
        }
        return $this->_targetDirs;
    }

    /**
     * Fill collection with theme model loaded from filesystem
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }

        $pathsToThemeConfig = array();
        foreach ($this->getTargetPatterns() as $directoryPath) {
            $themeConfigs = $this->_directory->search($directoryPath);
            foreach ($themeConfigs as &$relPathToTheme) {
                $relPathToTheme = $this->_directory->getAbsolutePath($relPathToTheme);
            }
            $pathsToThemeConfig = array_merge($pathsToThemeConfig, $themeConfigs);
        }

        $this->_loadFromFilesystem(
            $pathsToThemeConfig
        )->clearTargetPatterns()->_updateRelations()->_renderFilters()->_clearFilters();

        return $this;
    }

    /**
     * Set all parent themes
     *
     * @return $this
     */
    protected function _updateRelations()
    {
        $themeItems = $this->getItems();
        /** @var $theme \Magento\Framework\Object|ThemeInterface */
        foreach ($themeItems as $theme) {
            $parentThemePath = $theme->getData('parent_theme_path');
            if ($parentThemePath) {
                $themePath = $theme->getArea() . ThemeInterface::PATH_SEPARATOR . $parentThemePath;
                if (isset($themeItems[$themePath])) {
                    $theme->setParentTheme($themeItems[$themePath]);
                }
            }
        }
        return $this;
    }

    /**
     * Load themes collection from file system by file list
     *
     * @param array $themeConfigPaths
     * @return $this
     */
    protected function _loadFromFilesystem(array $themeConfigPaths)
    {
        foreach ($themeConfigPaths as $themeConfigPath) {
            $theme = $this->getNewEmptyItem()->addData($this->_prepareConfigurationData($themeConfigPath));
            $this->addItem($theme);
        }
        $this->_setIsLoaded();

        return $this;
    }

    /**
     * Return default path related data
     *
     * @param string $configPath
     * @return array
     */
    protected function _preparePathData($configPath)
    {
        $themeDirectory = dirname($configPath);
        $fullPath = trim(substr($themeDirectory, strlen($this->_directory->getAbsolutePath())), '/');
        $pathPieces = explode('/', $fullPath);
        $area = array_shift($pathPieces);
        return array('area' => $area, 'theme_path_pieces' => $pathPieces);
    }

    /**
     * Return default configuration data
     *
     * @param string $configPath
     * @return array
     */
    public function _prepareConfigurationData($configPath)
    {

        $themeConfig = $this->_getConfigModel($configPath);
        $pathData = $this->_preparePathData($configPath);
        $media = $themeConfig->getMedia();

        $parentPathPieces = $themeConfig->getParentTheme();
        if (count($parentPathPieces) == 1) {
            $pathPieces = $pathData['theme_path_pieces'];
            array_pop($pathPieces);
            $parentPathPieces = array_merge($pathPieces, $parentPathPieces);
        }

        $themePath = implode(ThemeInterface::PATH_SEPARATOR, $pathData['theme_path_pieces']);
        $themeCode = implode(ThemeInterface::CODE_SEPARATOR, $pathData['theme_path_pieces']);
        $parentPath = $parentPathPieces ? implode(ThemeInterface::PATH_SEPARATOR, $parentPathPieces) : null;

        return array(
            'parent_id' => null,
            'type' => ThemeInterface::TYPE_PHYSICAL,
            'area' => $pathData['area'],
            'theme_path' => $themePath,
            'code' => $themeCode,
            'theme_version' => $themeConfig->getThemeVersion(),
            'theme_title' => $themeConfig->getThemeTitle(),
            'preview_image' => $media['preview_image'] ? $media['preview_image'] : null,
            'parent_theme_path' => $parentPath
        );
    }

    /**
     * Apply set field filters
     *
     * @return $this
     */
    protected function _renderFilters()
    {
        $filters = $this->getFilter(array());
        /** @var $theme ThemeInterface */
        foreach ($this->getItems() as $itemKey => $theme) {
            $removeItem = false;
            foreach ($filters as $filter) {
                if ($filter['type'] == 'and' && $theme->getDataUsingMethod($filter['field']) != $filter['value']) {
                    $removeItem = true;
                }
            }
            if ($removeItem) {
                $this->removeItemByKey($itemKey);
            }
        }
        return $this;
    }

    /**
     * Clear all added filters
     *
     * @return $this
     */
    protected function _clearFilters()
    {
        $this->_filters = array();
        return $this;
    }

    /**
     * Return configuration model for themes
     *
     * @param string $configPath
     * @return \Magento\Framework\Config\Theme
     */
    protected function _getConfigModel($configPath)
    {
        return new \Magento\Framework\Config\Theme(
            $this->_directory->readFile($this->_directory->getRelativePath($configPath))
        );
    }

    /**
     * Retrieve item id
     *
     * @param \Magento\Framework\Object $item
     * @return string
     */
    protected function _getItemId(\Magento\Framework\Object $item)
    {
        return $item->getFullPath();
    }

    /**
     * Return array for select field
     *
     * @param bool $addEmptyField
     * @return array
     */
    public function toOptionArray($addEmptyField = false)
    {
        $optionArray = $addEmptyField ? array('' => '') : array();
        return $optionArray + $this->_toOptionArray('theme_id', 'theme_title');
    }

    /**
     * Checks that a theme present in filesystem collection
     *
     * @param ThemeInterface $theme
     * @return bool
     */
    public function hasTheme(ThemeInterface $theme)
    {
        $themeItems = $this->getItems();
        return $theme->getThemePath() && isset($themeItems[$theme->getFullPath()]);
    }

    /**
     * Get theme from file system by area and theme_path
     *
     * @param string $fullPath
     * @return ThemeInterface
     */
    public function getThemeByFullPath($fullPath)
    {
        list($area, $themePath) = explode('/', $fullPath, 2);
        $this->addDefaultPattern($area)->addFilter('theme_path', $themePath);

        return $this->getFirstItem();
    }
}
