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

/**
 * Service of copying customizations from one theme to another
 */
namespace Magento\Theme\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Design\ThemeInterface;

class CopyService
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\Write
     */
    protected $_directory;

    /**
     * @var \Magento\Framework\View\Design\Theme\FileFactory
     */
    protected $_fileFactory;

    /**
     * @var \Magento\Core\Model\Layout\Link
     */
    protected $_link;

    /**
     * @var \Magento\Core\Model\Layout\UpdateFactory
     */
    protected $_updateFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\Framework\View\Design\Theme\Customization\Path
     */
    protected $_customizationPath;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\View\Design\Theme\FileFactory $fileFactory
     * @param \Magento\Core\Model\Layout\Link $link
     * @param \Magento\Core\Model\Layout\UpdateFactory $updateFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\View\Design\Theme\Customization\Path $customization
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\View\Design\Theme\FileFactory $fileFactory,
        \Magento\Core\Model\Layout\Link $link,
        \Magento\Core\Model\Layout\UpdateFactory $updateFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\View\Design\Theme\Customization\Path $customization
    ) {
        $this->_directory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_fileFactory = $fileFactory;
        $this->_link = $link;
        $this->_updateFactory = $updateFactory;
        $this->_eventManager = $eventManager;
        $this->_customizationPath = $customization;
    }

    /**
     * Copy customizations from one theme to another
     *
     * @param ThemeInterface $source
     * @param ThemeInterface $target
     * @return void
     */
    public function copy(ThemeInterface $source, ThemeInterface $target)
    {
        $this->_copyDatabaseCustomization($source, $target);
        $this->_copyLayoutCustomization($source, $target);
        $this->_copyFilesystemCustomization($source, $target);
    }

    /**
     * Copy customizations stored in a database from one theme to another, overriding existing data
     *
     * @param ThemeInterface $source
     * @param ThemeInterface $target
     * @return void
     */
    protected function _copyDatabaseCustomization(ThemeInterface $source, ThemeInterface $target)
    {
        /** @var $themeFile \Magento\Core\Model\Theme\File */
        foreach ($target->getCustomization()->getFiles() as $themeFile) {
            $themeFile->delete();
        }
        /** @var $newFile \Magento\Core\Model\Theme\File */
        foreach ($source->getCustomization()->getFiles() as $themeFile) {
            /** @var $newThemeFile \Magento\Core\Model\Theme\File */
            $newThemeFile = $this->_fileFactory->create();
            $newThemeFile->setData(
                array(
                    'theme_id' => $target->getId(),
                    'file_path' => $themeFile->getFilePath(),
                    'file_type' => $themeFile->getFileType(),
                    'content' => $themeFile->getContent(),
                    'sort_order' => $themeFile->getData('sort_order')
                )
            );
            $newThemeFile->save();
        }
    }

    /**
     * Add layout links to general layout updates for themes
     *
     * @param ThemeInterface $source
     * @param ThemeInterface $target
     * @return void
     */
    protected function _copyLayoutCustomization(ThemeInterface $source, ThemeInterface $target)
    {
        $update = $this->_updateFactory->create();
        /** @var $targetUpdates \Magento\Core\Model\Resource\Layout\Update\Collection */
        $targetUpdates = $update->getCollection();
        $targetUpdates->addThemeFilter($target->getId());
        $targetUpdates->delete();

        /** @var $sourceCollection \Magento\Core\Model\Resource\Layout\Link\Collection */
        $sourceCollection = $this->_link->getCollection();
        $sourceCollection->addThemeFilter($source->getId());
        /** @var $layoutLink \Magento\Core\Model\Layout\Link */
        foreach ($sourceCollection as $layoutLink) {
            /** @var $update \Magento\Core\Model\Layout\Update */
            $update = $this->_updateFactory->create();
            $update->load($layoutLink->getLayoutUpdateId());
            if ($update->getId()) {
                $update->setId(null);
                $update->save();
                $layoutLink->setThemeId($target->getId());
                $layoutLink->setLayoutUpdateId($update->getId());
                $layoutLink->setId(null);
                $layoutLink->save();
            }
        }
    }

    /**
     * Copy customizations stored in a file system from one theme to another, overriding existing data
     *
     * @param ThemeInterface $source
     * @param ThemeInterface $target
     * @return void
     */
    protected function _copyFilesystemCustomization(ThemeInterface $source, ThemeInterface $target)
    {
        $sourcePath = $this->_customizationPath->getCustomizationPath($source);
        $targetPath = $this->_customizationPath->getCustomizationPath($target);

        if (!$sourcePath || !$targetPath) {
            return;
        }

        $this->_deleteFilesRecursively($targetPath);

        if ($this->_directory->isDirectory($sourcePath)) {
            $this->_copyFilesRecursively($sourcePath, $sourcePath, $targetPath);
        }
    }

    /**
     * Copies all files in a directory recursively
     *
     * @param string $baseDir
     * @param string $sourceDir
     * @param string $targetDir
     * @return void
     */
    protected function _copyFilesRecursively($baseDir, $sourceDir, $targetDir)
    {
        foreach ($this->_directory->read($sourceDir) as $path) {
            if ($this->_directory->isDirectory($path)) {
                $this->_copyFilesRecursively($baseDir, $path, $targetDir);
            } else {
                $filePath = substr($path, strlen($baseDir) + 1);
                $this->_directory->copyFile($path, $targetDir . '/' . $filePath);
            }
        }
    }

    /**
     * Delete all files in a directory recursively
     *
     * @param string $targetDir
     * @return void
     */
    protected function _deleteFilesRecursively($targetDir)
    {
        if (!$this->_directory->isExist($targetDir)) {
            return;
        }
        foreach ($this->_directory->read($targetDir) as $path) {
            $this->_directory->delete($path);
        }
    }
}
