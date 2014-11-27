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
 * Database saving file helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Core\Helper\File\Storage;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class Database extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Database storage model
     * @var null|\Magento\Core\Model\File\Storage\Database
     */
    protected $_databaseModel = null;

    /**
     * Storage resource model
     * @var null|\Magento\Core\Model\Resource\File\Storage\Database
     */
    protected $_resourceModel = null;

    /**
     * Db usage flag
     *
     * @var bool
     */
    protected $_useDb = null;

    /**
     * Media dir
     *
     * @var string
     */
    protected $_mediaBaseDirectory;

    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Core\Model\File\Storage\DatabaseFactory
     */
    protected $_dbStorageFactory;

    /**
     * @var \Magento\Core\Model\File\Storage\File
     */
    protected $_fileStorage;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $config;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Core\Model\File\Storage\DatabaseFactory $dbStorageFactory
     * @param \Magento\Core\Model\File\Storage\File $fileStorage
     * @param Filesystem $filesystem
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Core\Model\File\Storage\DatabaseFactory $dbStorageFactory,
        \Magento\Core\Model\File\Storage\File $fileStorage,
        Filesystem $filesystem,
        \Magento\Framework\App\Config\ScopeConfigInterface $config
    ) {
        $this->_filesystem = $filesystem;
        $this->_dbStorageFactory = $dbStorageFactory;
        $this->_fileStorage = $fileStorage;
        $this->config = $config;
        parent::__construct($context);
    }

    /**
     * Check if we use DB storage
     * Note: Disabled as not completed feature
     *
     * @return bool
     */
    public function checkDbUsage()
    {
        if (null === $this->_useDb) {
            $currentStorage = (int)$this->config->getValue(
                \Magento\Core\Model\File\Storage::XML_PATH_STORAGE_MEDIA,
                'default'
            );
            $this->_useDb = $currentStorage == \Magento\Core\Model\File\Storage::STORAGE_MEDIA_DATABASE;
        }

        return $this->_useDb;
    }

    /**
     * Get database storage model
     *
     * @return \Magento\Core\Model\File\Storage\Database
     */
    public function getStorageDatabaseModel()
    {
        if (is_null($this->_databaseModel)) {
            $this->_databaseModel = $this->_dbStorageFactory->create();
        }

        return $this->_databaseModel;
    }

    /**
     * Get file storage model
     *
     * @return \Magento\Core\Model\File\Storage\File
     */
    public function getStorageFileModel()
    {
        return $this->_fileStorage;
    }

    /**
     * Get storage model
     *
     * @return \Magento\Core\Model\Resource\File\Storage\Database
     */
    public function getResourceStorageModel()
    {
        if (is_null($this->_resourceModel)) {
            $this->_resourceModel = $this->getStorageDatabaseModel()->getResource();
        }
        return $this->_resourceModel;
    }

    /**
     * Save file in DB storage
     *
     * @param string $filename
     * @return void
     */
    public function saveFile($filename)
    {
        if ($this->checkDbUsage()) {
            $this->getStorageDatabaseModel()->saveFile($this->_removeAbsPathFromFileName($filename));
        }
    }

    /**
     * Rename file in DB storage
     *
     * @param string $oldName
     * @param string $newName
     * @return void
     */
    public function renameFile($oldName, $newName)
    {
        if ($this->checkDbUsage()) {
            $this->getStorageDatabaseModel()->renameFile(
                $this->_removeAbsPathFromFileName($oldName),
                $this->_removeAbsPathFromFileName($newName)
            );
        }
    }

    /**
     * Copy file in DB storage
     *
     * @param string $oldName
     * @param string $newName
     * @return void
     */
    public function copyFile($oldName, $newName)
    {
        if ($this->checkDbUsage()) {
            $this->getStorageDatabaseModel()->copyFile(
                $this->_removeAbsPathFromFileName($oldName),
                $this->_removeAbsPathFromFileName($newName)
            );
        }
    }

    /**
     * Check whether file exists in DB
     *
     * @param string $filename can be both full path or partial (like in DB)
     * @return bool|null
     */
    public function fileExists($filename)
    {
        if ($this->checkDbUsage()) {
            return $this->getStorageDatabaseModel()->fileExists($this->_removeAbsPathFromFileName($filename));
        } else {
            return null;
        }
    }

    /**
     * Get unique name for passed file in case this file already exists
     *
     * @param string $directory - can be both full path or partial (like in DB)
     * @param string $filename - not just a filename. Can have directory chunks. return will have this form
     * @return string
     */
    public function getUniqueFilename($directory, $filename)
    {
        if ($this->checkDbUsage()) {
            $directory = $this->_removeAbsPathFromFileName($directory);
            if ($this->fileExists($directory . $filename)) {
                $index = 1;
                $extension = strrchr($filename, '.');
                $filenameWoExtension = substr($filename, 0, -1 * strlen($extension));
                while ($this->fileExists($directory . $filenameWoExtension . '_' . $index . $extension)) {
                    $index++;
                }
                $filename = $filenameWoExtension . '_' . $index . $extension;
            }
        }
        return $filename;
    }

    /**
     * Save database file to file system
     *
     * @param string $filename
     * @return bool|int
     */
    public function saveFileToFilesystem($filename)
    {
        if ($this->checkDbUsage()) {
            /** @var $file \Magento\Core\Model\File\Storage\Database */
            $file = $this->_dbStorageFactory->create()->loadByFilename($this->_removeAbsPathFromFileName($filename));
            if (!$file->getId()) {
                return false;
            }

            return $this->getStorageFileModel()->saveFile($file->getData(), true);
        }
        return false;
    }

    /**
     * Return relative uri for media content by full path
     *
     * @param string $fullPath
     * @return string
     */
    public function getMediaRelativePath($fullPath)
    {
        $relativePath = ltrim(str_replace($this->getMediaBaseDir(), '', $fullPath), '\\/');
        return str_replace('\\', '/', $relativePath);
    }

    /**
     * Deletes from DB files, which belong to one folder
     *
     * @param string $folderName
     * @return void
     */
    public function deleteFolder($folderName)
    {
        if ($this->checkDbUsage()) {
            $this->getResourceStorageModel()->deleteFolder($this->_removeAbsPathFromFileName($folderName));
        }
    }

    /**
     * Deletes from DB files, which belong to one folder
     *
     * @param string $filename
     * @return void
     */
    public function deleteFile($filename)
    {
        if ($this->checkDbUsage()) {
            $this->getStorageDatabaseModel()->deleteFile($this->_removeAbsPathFromFileName($filename));
        }
    }

    /**
     * Saves uploaded by \Magento\Core\Model\File\Uploader file to DB with existence tests
     *
     * param $result should be result from \Magento\Core\Model\File\Uploader::save() method
     * Checks in DB, whether uploaded file exists ($result['file'])
     * If yes, renames file on FS (!!!!!)
     * Saves file with unique name into DB
     * If passed file exists returns new name, file was renamed to (in the same context)
     * Otherwise returns $result['file']
     *
     * @param array $result
     * @return string
     */
    public function saveUploadedFile($result)
    {
        if ($this->checkDbUsage()) {
            $path = rtrim(str_replace(array('\\', '/'), '/', $result['path']), '/');
            $file = '/' . ltrim($result['file'], '\\/');

            $uniqueResultFile = $this->getUniqueFilename($path, $file);

            if ($uniqueResultFile !== $file) {
                $dirWrite = $this->_filesystem->getDirectoryWrite(DirectoryList::ROOT);
                $dirWrite->renameFile($path . $file, $path . $uniqueResultFile);
            }
            $this->saveFile($path . $uniqueResultFile);

            return $uniqueResultFile;
        } else {
            return $result['file'];
        }
    }

    /**
     * Convert full file path to local (as used by model)
     * If not - returns just a filename
     *
     * @param string $filename
     * @return string
     */
    protected function _removeAbsPathFromFileName($filename)
    {
        return $this->getMediaRelativePath($filename);
    }

    /**
     * Return Media base dir
     *
     * @return string
     */
    public function getMediaBaseDir()
    {
        if (null === $this->_mediaBaseDirectory) {
            $mediaDir = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
            $this->_mediaBaseDirectory = rtrim($mediaDir, '/');
        }
        return $this->_mediaBaseDirectory;
    }
}
