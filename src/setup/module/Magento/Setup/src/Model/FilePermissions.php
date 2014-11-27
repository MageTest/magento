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

namespace Magento\Setup\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class FilePermissions
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * List of required writable directories for installation
     *
     * @var array
     */
    protected $installationWritableDirectories = [];

    /**
     * List of recommended non-writable directories for application
     *
     * @var array
     */
    protected $applicationNonWritableDirectories = [];

    /**
     * List of current writable directories for installation
     *
     * @var array
     */
    protected $installationCurrentWritableDirectories = [];

    /**
     * List of current non-writable directories for application
     *
     * @var array
     */
    protected $applicationCurrentNonWritableDirectories = [];

    /**
     * @param Filesystem $filesystem
     * @param DirectoryList $directoryList
     */
    public function __construct(
        Filesystem $filesystem,
        DirectoryList $directoryList
    ) {
        $this->filesystem = $filesystem;
        $this->directoryList = $directoryList;
    }

    /**
     * Retrieve list of required writable directories for installation
     *
     * @return array
     */
    public function getInstallationWritableDirectories()
    {
        if (!$this->installationWritableDirectories) {
            $data = array(
                DirectoryList::CONFIG,
                DirectoryList::VAR_DIR,
                DirectoryList::MEDIA,
                DirectoryList::STATIC_VIEW
            );
            foreach ($data as $code) {
                $this->installationWritableDirectories[$code] = $this->directoryList->getPath($code);
            }
        }
        return array_values($this->installationWritableDirectories);
    }

    /**
     * Retrieve list of recommended non-writable directories for application
     *
     * @return array
     */
    public function getApplicationNonWritableDirectories()
    {
        if (!$this->applicationNonWritableDirectories) {
            $data = array(
                DirectoryList::CONFIG
            );
            foreach ($data as $code) {
                $this->applicationNonWritableDirectories[$code] = $this->directoryList->getPath($code);
            }
        }
        return array_values($this->applicationNonWritableDirectories);
    }

    /**
     * Retrieve list of currently writable directories for installation
     *
     * @param bool
     * @return array
     */
    public function getInstallationCurrentWritableDirectories()
    {
        if (!$this->installationCurrentWritableDirectories) {
            foreach ($this->installationWritableDirectories as $code => $path) {
                if ($this->isWritable($code)) {
                    $this->installationCurrentWritableDirectories[] = $path;
                }
            }
        }
        return $this->installationCurrentWritableDirectories;
    }

    /**
     * Retrieve list of currently non-writable directories for application
     *
     * @param bool
     * @return array
     */
    public function getApplicationCurrentNonWritableDirectories()
    {
        if (!$this->applicationCurrentNonWritableDirectories) {
            foreach ($this->applicationNonWritableDirectories as $code => $path) {
                if ($this->isNonWritable($code)) {
                    $this->applicationCurrentNonWritableDirectories[] = $path;
                }
            }
        }
        return $this->applicationCurrentNonWritableDirectories;
    }

    /**
     * Checks if directory is writable by given directory code
     *
     * @param string $code
     * @return bool
     */
    protected function isWritable($code)
    {
        $directory = $this->filesystem->getDirectoryWrite($code);
        return $this->isReadableDirectory($directory) && $directory->isWritable();
    }

    /**
     * Checks if directory is non-writable by given directory code
     *
     * @param string $code
     * @return bool
     */
    protected function isNonWritable($code)
    {
        $directory = $this->filesystem->getDirectoryWrite($code);
        return $this->isReadableDirectory($directory) && !$directory->isWritable();
    }

    /**
     * Checks if directory exists and is readable
     *
     * @param \Magento\Framework\Filesystem\Directory\WriteInterface $directory
     * @return bool
     */
    protected function isReadableDirectory($directory)
    {
        if (!$directory->isExist() || !$directory->isDirectory() || !$directory->isReadable()) {
            return false;
        }
        return true;
    }

    /**
     * Checks writable directories for installation
     *
     * @return array
     */
    public function getMissingWritableDirectoriesForInstallation()
    {
        $required = $this->getInstallationWritableDirectories();
        $current = $this->getInstallationCurrentWritableDirectories();
        return array_diff($required, $current);
    }

    /**
     * Checks non-writable directories for application
     *
     * @return array
     */
    public function getUnnecessaryWritableDirectoriesForApplication()
    {
        $required = $this->getApplicationNonWritableDirectories();
        $current = $this->getApplicationCurrentNonWritableDirectories();
        return array_diff($required, $current);
    }
}
