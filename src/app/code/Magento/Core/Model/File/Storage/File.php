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
namespace Magento\Core\Model\File\Storage;

/**
 * Class File
 */
class File
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'core_file_storage_file';

    /**
     * Store media base directory path
     *
     * @var string
     */
    protected $_mediaBaseDirectory = null;

    /**
     * Core file storage database
     *
     * @var \Magento\Core\Helper\File\Storage\Database
     */
    protected $_storageHelper = null;

    /**
     * @var \Magento\Core\Helper\File\Media
     */
    protected $_mediaHelper = null;

    /**
     * Data at storage
     *
     * @var array
     */
    protected $_data = null;

    /**
     * Collect errors during sync process
     *
     * @var string[]
     */
    protected $_errors = array();

    /**
     * @var \Magento\Framework\Logger
     */
    protected $_logger;

    /**
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Core\Helper\File\Storage\Database $storageHelper
     * @param \Magento\Core\Helper\File\Media $mediaHelper
     * @param \Magento\Core\Model\Resource\File\Storage\File $fileUtility
     */
    public function __construct(
        \Magento\Framework\Logger $logger,
        \Magento\Core\Helper\File\Storage\Database $storageHelper,
        \Magento\Core\Helper\File\Media $mediaHelper,
        \Magento\Core\Model\Resource\File\Storage\File $fileUtility
    ) {
        $this->_fileUtility = $fileUtility;
        $this->_storageHelper = $storageHelper;
        $this->_logger = $logger;
        $this->_mediaHelper = $mediaHelper;
    }

    /**
     * Initialization
     *
     * @return $this
     */
    public function init()
    {
        return $this;
    }

    /**
     * Return storage name
     *
     * @return string
     */
    public function getStorageName()
    {
        return __('File system');
    }

    /**
     * Get files and directories from storage
     *
     * @return array
     */
    public function getStorageData()
    {
        return $this->_fileUtility->getStorageData();
    }

    /**
     * Check if there was errors during sync process
     *
     * @return bool
     */
    public function hasErrors()
    {
        return !empty($this->_errors);
    }

    /**
     * Clear files and directories in storage
     *
     * @return $this
     */
    public function clear()
    {
        $this->_fileUtility->clear();
        return $this;
    }

    /**
     * Collect files and directories from storage
     *
     * @param  int $offset
     * @param  int $count
     * @param  string $type
     * @return array|bool
     */
    public function collectData($offset = 0, $count = 100, $type = 'files')
    {
        if (!in_array($type, array('files', 'directories'))) {
            return false;
        }

        $offset = (int)$offset >= 0 ? (int)$offset : 0;
        $count = (int)$count >= 1 ? (int)$count : 1;

        if (empty($this->_data)) {
            $this->_data = $this->getStorageData();
        }

        if (!array_key_exists($type, $this->_data)) {
            return false;
        }
        $slice = array_slice($this->_data[$type], $offset, $count);
        return $slice ?: false;
    }

    /**
     * Retrieve connection name saved at config
     *
     * @return null
     */
    public function getConfigConnectionName()
    {
        return null;
    }

    /**
     * Export directories list from storage
     *
     * @param  int $offset
     * @param  int $count
     * @return array|bool
     */
    public function exportDirectories($offset = 0, $count = 100)
    {
        return $this->collectData($offset, $count, 'directories');
    }

    /**
     * Export files list in defined range
     *
     * @param  int $offset
     * @param  int $count
     * @return array|bool
     */
    public function exportFiles($offset = 0, $count = 1)
    {
        $slice = $this->collectData($offset, $count, 'files');

        if (!$slice) {
            return false;
        }

        $result = array();
        foreach ($slice as $fileName) {
            try {
                $fileInfo = $this->_mediaHelper->collectFileInfo($this->getMediaBaseDirectory(), $fileName);
            } catch (\Exception $e) {
                $this->_logger->logException($e);
                continue;
            }

            $result[] = $fileInfo;
        }

        return $result;
    }

    /**
     * Import entities to storage
     *
     * @param  array $data
     * @param  string $callback
     * @return $this
     */
    public function import($data, $callback)
    {
        if (!is_array($data) || !method_exists($this, $callback)) {
            return $this;
        }

        foreach ($data as $part) {
            try {
                $this->{$callback}($part);
            } catch (\Exception $e) {
                $this->_errors[] = $e->getMessage();
                $this->_logger->logException($e);
            }
        }

        return $this;
    }

    /**
     * Import directories to storage
     *
     * @param  array $dirs
     * @return $this
     */
    public function importDirectories($dirs)
    {
        return $this->import($dirs, 'saveDir');
    }

    /**
     * Import files list
     *
     * @param  array $files
     * @return $this
     */
    public function importFiles($files)
    {
        return $this->import($files, 'saveFile');
    }

    /**
     * Save directory to storage
     *
     * @param  array $dir
     * @return bool
     */
    public function saveDir($dir)
    {
        return $this->_fileUtility->saveDir($dir);
    }

    /**
     * Save file to storage
     *
     * @param  array $file
     * @param  bool $overwrite
     * @throws \Magento\Framework\Model\Exception
     * @return bool
     */
    public function saveFile($file, $overwrite = true)
    {
        if (isset(
            $file['filename']
        ) && !empty($file['filename']) && isset(
            $file['content']
        ) && !empty($file['content'])
        ) {
            try {
                $filename = isset(
                    $file['directory']
                ) && !empty($file['directory']) ? $file['directory'] . '/' . $file['filename'] : $file['filename'];

                return $this->_fileUtility->saveFile($filename, $file['content'], $overwrite);
            } catch (\Exception $e) {
                $this->_logger->logException($e);
                throw new \Magento\Framework\Model\Exception(
                    __('Unable to save file "%1" at "%2"', $file['filename'], $file['directory'])
                );
            }
        } else {
            throw new \Magento\Framework\Model\Exception(__('Wrong file info format'));
        }

        return false;
    }

    /**
     * Retrieve media base directory path
     *
     * @return string
     */
    public function getMediaBaseDirectory()
    {
        if (is_null($this->_mediaBaseDirectory)) {
            $this->_mediaBaseDirectory = $this->_storageHelper->getMediaBaseDir();
        }
        return $this->_mediaBaseDirectory;
    }
}
