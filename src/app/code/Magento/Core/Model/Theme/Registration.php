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

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Model\Exception;
use Magento\Framework\View\Design\ThemeInterface;

/**
 * Theme registration model class
 */
class Registration
{
    /**
     * @var \Magento\Core\Model\Resource\Theme\Data\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * Collection of themes in file-system
     *
     * @var Collection
     */
    protected $_themeCollection;

    /**
     * Allowed sequence relation by type, array(parent theme, child theme)
     *
     * @var array
     */
    protected $_allowedRelations = array(
        array(ThemeInterface::TYPE_PHYSICAL, ThemeInterface::TYPE_VIRTUAL),
        array(ThemeInterface::TYPE_VIRTUAL, ThemeInterface::TYPE_STAGING)
    );

    /**
     * Forbidden sequence relation by type
     *
     * @var array
     */
    protected $_forbiddenRelations = array(
        array(ThemeInterface::TYPE_VIRTUAL, ThemeInterface::TYPE_VIRTUAL),
        array(ThemeInterface::TYPE_PHYSICAL, ThemeInterface::TYPE_STAGING)
    );

    /**
     * @var \Magento\Framework\Filesystem\Directory\Read
     */
    protected $directoryRead;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Core\Model\Resource\Theme\Data\CollectionFactory $collectionFactory
     * @param \Magento\Core\Model\Theme\Data\Collection $filesystemCollection
     * @param Filesystem $filesystem
     */
    public function __construct(
        \Magento\Core\Model\Resource\Theme\Data\CollectionFactory $collectionFactory,
        \Magento\Core\Model\Theme\Data\Collection $filesystemCollection,
        Filesystem $filesystem
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_themeCollection = $filesystemCollection;
        $this->directoryRead = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
    }

    /**
     * Theme registration
     *
     * @param string $pathPattern
     * @return $this
     */
    public function register($pathPattern = '')
    {
        if (empty($pathPattern)) {
            $this->_themeCollection->addDefaultPattern('*');
        } else {
            $this->_themeCollection->addTargetPattern($pathPattern);
        }

        foreach ($this->_themeCollection as $theme) {
            $this->_registerThemeRecursively($theme);
        }

        $this->checkPhysicalThemes()->checkAllowedThemeRelations();

        return $this;
    }

    /**
     * Register theme and recursively all its ascendants
     * Second param is optional and is used to prevent circular references in inheritance chain
     *
     * @param ThemeInterface &$theme
     * @param array $inheritanceChain
     * @return $this
     * @throws Exception
     */
    protected function _registerThemeRecursively(&$theme, $inheritanceChain = array())
    {
        if ($theme->getId()) {
            return $this;
        }
        $themeModel = $this->getThemeFromDb($theme->getFullPath());
        if ($themeModel->getId()) {
            $theme = $themeModel;
            return $this;
        }

        $tempId = $theme->getFullPath();
        if (in_array($tempId, $inheritanceChain)) {
            throw new Exception(__('Circular-reference in theme inheritance detected for "%1"', $tempId));
        }
        $inheritanceChain[] = $tempId;
        $parentTheme = $theme->getParentTheme();
        if ($parentTheme) {
            $this->_registerThemeRecursively($parentTheme, $inheritanceChain);
            $theme->setParentId($parentTheme->getId());
        }

        $this->_savePreviewImage($theme);
        $theme->setType(ThemeInterface::TYPE_PHYSICAL);
        $theme->save();

        return $this;
    }

    /**
     * Save preview image for theme
     *
     * @param ThemeInterface $theme
     * @return $this
     */
    protected function _savePreviewImage(ThemeInterface $theme)
    {
        $themeDirectory = $theme->getCustomization()->getThemeFilesPath();
        if (!$theme->getPreviewImage() || !$themeDirectory) {
            return $this;
        }
        $imagePath = $themeDirectory . '/' . $theme->getPreviewImage();
        if (0 === strpos($imagePath, $themeDirectory)) {
            $theme->getThemeImage()->createPreviewImage($imagePath);
        }
        return $this;
    }

    /**
     * Get theme from DB by full path
     *
     * @param string $fullPath
     * @return ThemeInterface
     */
    public function getThemeFromDb($fullPath)
    {
        return $this->_collectionFactory->create()->getThemeByFullPath($fullPath);
    }

    /**
     * Checks all physical themes that they were not deleted
     *
     * @return $this
     */
    public function checkPhysicalThemes()
    {
        $themes = $this->_collectionFactory->create()->addTypeFilter(ThemeInterface::TYPE_PHYSICAL);
        /** @var $theme ThemeInterface */
        foreach ($themes as $theme) {
            if (!$this->_themeCollection->hasTheme($theme)) {
                $theme->setType(ThemeInterface::TYPE_VIRTUAL)->save();
            }
        }
        return $this;
    }

    /**
     * Check whether all themes have correct parent theme by type
     *
     * @return $this
     */
    public function checkAllowedThemeRelations()
    {
        foreach ($this->_forbiddenRelations as $typesSequence) {
            list($parentType, $childType) = $typesSequence;
            $collection = $this->_collectionFactory->create();
            $collection->addTypeRelationFilter($parentType, $childType);
            /** @var $theme ThemeInterface */
            foreach ($collection as $theme) {
                $parentId = $this->_getResetParentId($theme);
                if ($theme->getParentId() != $parentId) {
                    $theme->setParentId($parentId)->save();
                }
            }
        }
        return $this;
    }

    /**
     * Reset parent themes by type
     *
     * @param ThemeInterface $theme
     * @return int|null
     */
    protected function _getResetParentId(ThemeInterface $theme)
    {
        $parentTheme = $theme->getParentTheme();
        while ($parentTheme) {
            foreach ($this->_allowedRelations as $typesSequence) {
                list($parentType, $childType) = $typesSequence;
                if ($theme->getType() == $childType && $parentTheme->getType() == $parentType) {
                    return $parentTheme->getId();
                }
            }
            $parentTheme = $parentTheme->getParentTheme();
        }
        return null;
    }
}
