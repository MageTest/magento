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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\File\Storage;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\WriteInterface as DirectoryWrite;
use Magento\Framework\Filesystem\File\Write;
use Magento\Framework\Filesystem\FilesystemException;

class Config
{
    /**
     * Config cache file path
     *
     * @var string
     */
    protected $cacheFilePath;

    /**
     * Loaded config
     *
     * @var array
     */
    protected $config;

    /**
     * File stream handler
     *
     * @var DirectoryWrite
     */
    protected $pubDirectory;

    /**
     * @param \Magento\Core\Model\File\Storage $storage
     * @param \Magento\Framework\Filesystem $filesystem
     * @param string $cacheFile
     */
    public function __construct(
        \Magento\Core\Model\File\Storage $storage,
        \Magento\Framework\Filesystem $filesystem,
        $cacheFile
    ) {
        $this->config = $storage->getScriptConfig();
        $this->pubDirectory = $filesystem->getDirectoryWrite(DirectoryList::PUB);
        $this->cacheFilePath = $cacheFile;
    }

    /**
     * Retrieve media directory
     *
     * @return string
     */
    public function getMediaDirectory()
    {
        return $this->config['media_directory'];
    }

    /**
     * Retrieve list of allowed resources
     *
     * @return array
     */
    public function getAllowedResources()
    {
        return $this->config['allowed_resources'];
    }

    /**
     * Save config in cache file
     *
     * @return void
     */
    public function save()
    {
        /** @var Write $file */
        $file = $this->pubDirectory->openFile($this->pubDirectory->getRelativePath($this->cacheFilePath), 'w');
        try {
            $file->lock();
            $file->write(json_encode($this->config));
            $file->unlock();
            $file->close();
        } catch (FilesystemException $e) {
            $file->close();
        }
    }
}
