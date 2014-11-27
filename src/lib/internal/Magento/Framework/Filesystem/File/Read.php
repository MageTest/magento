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
namespace Magento\Framework\Filesystem\File;

use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\FilesystemException;

class Read implements ReadInterface
{
    /**
     * Full path to file
     *
     * @var string
     */
    protected $path;

    /**
     * Mode to open the file
     *
     * @var string
     */
    protected $mode = 'r';

    /**
     * Opened file resource
     *
     * @var resource
     */
    protected $resource;

    /**
     * @var \Magento\Framework\Filesystem\DriverInterface
     */
    protected $driver;

    /**
     * Constructor
     *
     * @param string $path
     * @param DriverInterface $driver
     */
    public function __construct($path, DriverInterface $driver)
    {
        $this->path = $path;

        $this->driver = $driver;

        $this->open();
    }

    /**
     * Open file
     *
     * @return $this
     */
    protected function open()
    {
        $this->assertValid();
        $this->resource = $this->driver->fileOpen($this->path, $this->mode);
        return $this;
    }

    /**
     * Assert file existence
     *
     * @return bool
     * @throws \Magento\Framework\Filesystem\FilesystemException
     */
    protected function assertValid()
    {
        if (!$this->driver->isExists($this->path)) {
            throw new FilesystemException(sprintf('The file "%s" doesn\'t exist', $this->path));
        }
        return true;
    }

    /**
     * Reads the specified number of bytes from the current position.
     *
     * @param int $length The number of bytes to read
     * @return string
     */
    public function read($length)
    {
        return $this->driver->fileRead($this->resource, $length);
    }

    /**
     * Return file content
     *
     * @param string|null $flag
     * @param resource|null $context
     * @return string
     */
    public function readAll($flag = null, $context = null)
    {
        return $this->driver->fileGetContents($this->path, $flag, $context);
    }

    /**
     * Reads the line with specified number of bytes from the current position.
     *
     * @param int $length The number of bytes to read
     * @param string $ending [optional]
     * @return string
     */
    public function readLine($length, $ending = null)
    {
        return $this->driver->fileReadLine($this->resource, $length, $ending);
    }

    /**
     * Reads one CSV row from the file
     *
     * @param int $length [optional]
     * @param string $delimiter [optional]
     * @param string $enclosure [optional]
     * @param string $escape [optional]
     * @return array|bool|null
     */
    public function readCsv($length = 0, $delimiter = ',', $enclosure = '"', $escape = '\\')
    {
        return $this->driver->fileGetCsv($this->resource, $length, $delimiter, $enclosure, $escape);
    }

    /**
     * Returns the current cursor position
     *
     * @return int
     */
    public function tell()
    {
        return $this->driver->fileTell($this->resource);
    }

    /**
     * Seeks to the specified offset
     *
     * @param int $offset
     * @param int $whence
     * @return int
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        return $this->driver->fileSeek($this->resource, $offset, $whence);
    }

    /**
     * Checks if the current position is the end-of-file
     *
     * @return bool
     */
    public function eof()
    {
        return $this->driver->endOfFile($this->resource);
    }

    /**
     * Closes the file.
     *
     * @return bool
     */
    public function close()
    {
        return $this->driver->fileClose($this->resource);
    }

    /**
     * {@inheritDoc}
     *
     * @return array
     */
    public function stat()
    {
        return $this->driver->stat($this->path);
    }
}
