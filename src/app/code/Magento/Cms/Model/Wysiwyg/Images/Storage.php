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
namespace Magento\Cms\Model\Wysiwyg\Images;

use Magento\Cms\Helper\Wysiwyg\Images;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Wysiwyg Images model
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Storage extends \Magento\Framework\Object
{
    const DIRECTORY_NAME_REGEXP = '/^[a-z0-9\-\_]+$/si';

    const THUMBS_DIRECTORY_NAME = '.thumbs';

    const THUMB_PLACEHOLDER_PATH_SUFFIX = 'Magento_Cms::images/placeholder_thumbnail.jpg';

    /**
     * Config object
     *
     * @var \Magento\Framework\App\Config\Element
     */
    protected $_config;

    /**
     * Config object as array
     *
     * @var array
     */
    protected $_configAsArray;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write
     */
    protected $_directory;

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $_imageFactory;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * Core file storage database
     *
     * @var \Magento\Core\Helper\File\Storage\Database
     */
    protected $_coreFileStorageDb = null;

    /**
     * Cms wysiwyg images
     *
     * @var \Magento\Cms\Helper\Wysiwyg\Images
     */
    protected $_cmsWysiwygImages = null;

    /**
     * @var array
     */
    protected $_resizeParameters;

    /**
     * @var array
     */
    protected $_extensions;

    /**
     * @var array
     */
    protected $_dirs;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_backendUrl;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_session;

    /**
     * Directory database factory
     *
     * @var \Magento\Core\Model\File\Storage\Directory\DatabaseFactory
     */
    protected $_directoryDatabaseFactory;

    /**
     * Storage database factory
     *
     * @var \Magento\Core\Model\File\Storage\DatabaseFactory
     */
    protected $_storageDatabaseFactory;

    /**
     * Storage file factory
     *
     * @var \Magento\Core\Model\File\Storage\FileFactory
     */
    protected $_storageFileFactory;

    /**
     * Storage collection factory
     *
     * @var \Magento\Cms\Model\Wysiwyg\Images\Storage\CollectionFactory
     */
    protected $_storageCollectionFactory;

    /**
     * Uploader factory
     *
     * @var \Magento\Core\Model\File\UploaderFactory
     */
    protected $_uploaderFactory;

    /**
     * Construct
     *
     * @param \Magento\Backend\Model\Session $session
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Cms\Helper\Wysiwyg\Images $cmsWysiwygImages
     * @param \Magento\Core\Helper\File\Storage\Database $coreFileStorageDb
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Image\AdapterFactory $imageFactory
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Cms\Model\Wysiwyg\Images\Storage\CollectionFactory $storageCollectionFactory
     * @param \Magento\Core\Model\File\Storage\FileFactory $storageFileFactory
     * @param \Magento\Core\Model\File\Storage\DatabaseFactory $storageDatabaseFactory
     * @param \Magento\Core\Model\File\Storage\Directory\DatabaseFactory $directoryDatabaseFactory
     * @param \Magento\Core\Model\File\UploaderFactory $uploaderFactory
     * @param array $resizeParameters
     * @param array $extensions
     * @param array $dirs
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Model\Session $session,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Cms\Helper\Wysiwyg\Images $cmsWysiwygImages,
        \Magento\Core\Helper\File\Storage\Database $coreFileStorageDb,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Cms\Model\Wysiwyg\Images\Storage\CollectionFactory $storageCollectionFactory,
        \Magento\Core\Model\File\Storage\FileFactory $storageFileFactory,
        \Magento\Core\Model\File\Storage\DatabaseFactory $storageDatabaseFactory,
        \Magento\Core\Model\File\Storage\Directory\DatabaseFactory $directoryDatabaseFactory,
        \Magento\Core\Model\File\UploaderFactory $uploaderFactory,
        array $resizeParameters = array(),
        array $extensions = array(),
        array $dirs = array(),
        array $data = array()
    ) {
        $this->_session = $session;
        $this->_backendUrl = $backendUrl;
        $this->_cmsWysiwygImages = $cmsWysiwygImages;
        $this->_coreFileStorageDb = $coreFileStorageDb;
        $this->_directory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_imageFactory = $imageFactory;
        $this->_assetRepo = $assetRepo;
        $this->_storageCollectionFactory = $storageCollectionFactory;
        $this->_storageFileFactory = $storageFileFactory;
        $this->_storageDatabaseFactory = $storageDatabaseFactory;
        $this->_directoryDatabaseFactory = $directoryDatabaseFactory;
        $this->_uploaderFactory = $uploaderFactory;
        $this->_resizeParameters = $resizeParameters;
        $this->_extensions = $extensions;
        $this->_dirs = $dirs;
        parent::__construct($data);
    }

    /**
     * Return one-level child directories for specified path
     *
     * @param string $path Parent directory path
     * @return \Magento\Framework\Data\Collection\Filesystem
     */
    public function getDirsCollection($path)
    {
        if ($this->_coreFileStorageDb->checkDbUsage()) {
            /** @var \Magento\Core\Model\File\Storage\Directory\Database $subDirectories */
            $subDirectories = $this->_directoryDatabaseFactory->create();
            $subDirectories->getSubdirectories($path);
            foreach ($subDirectories as $directory) {
                $fullPath = rtrim($path, '/') . '/' . $directory['name'];
                $this->_directory->create($fullPath);
            }
        }

        $conditions = array('reg_exp' => array(), 'plain' => array());

        if ($this->_dirs['exclude']) {
            foreach ($this->_dirs['exclude'] as $dir) {
                $conditions[$dir->getAttribute('regexp') ? 'reg_exp' : 'plain'][$dir] = true;
            }
        }

        // "include" section takes precedence and can revoke directory exclusion
        if ($this->_dirs['include']) {
            foreach ($this->_dirs['include'] as $dir) {
                unset($conditions['regexp'][(string)$dir], $conditions['plain'][$dir]);
            }
        }

        $regExp = $conditions['reg_exp'] ? '~' . implode('|', array_keys($conditions['reg_exp'])) . '~i' : null;
        $collection = $this->getCollection(
            $path
        )->setCollectDirs(
            true
        )->setCollectFiles(
            false
        )->setCollectRecursively(
            false
        );
        $storageRootLength = strlen($this->_cmsWysiwygImages->getStorageRoot());

        foreach ($collection as $key => $value) {
            $rootChildParts = explode('/', substr($value->getFilename(), $storageRootLength));

            if (array_key_exists(
                $rootChildParts[0],
                $conditions['plain']
            ) || $regExp && preg_match(
                $regExp,
                $value->getFilename()
            )
            ) {
                $collection->removeItemByKey($key);
            }
        }

        return $collection;
    }

    /**
     * Return files
     *
     * @param string $path Parent directory path
     * @param string $type Type of storage, e.g. image, media etc.
     * @return \Magento\Framework\Data\Collection\Filesystem
     */
    public function getFilesCollection($path, $type = null)
    {
        if ($this->_coreFileStorageDb->checkDbUsage()) {
            $files = $this->_storageDatabaseFactory->create()->getDirectoryFiles($path);

            /** @var \Magento\Core\Model\File\Storage\File $fileStorageModel */
            $fileStorageModel = $this->_storageFileFactory->create();
            foreach ($files as $file) {
                $fileStorageModel->saveFile($file);
            }
        }

        $collection = $this->getCollection(
            $path
        )->setCollectDirs(
            false
        )->setCollectFiles(
            true
        )->setCollectRecursively(
            false
        )->setOrder(
            'mtime',
            \Magento\Framework\Data\Collection::SORT_ORDER_ASC
        );

        // Add files extension filter
        if ($allowed = $this->getAllowedExtensions($type)) {
            $collection->setFilesFilter('/\.(' . implode('|', $allowed) . ')$/i');
        }

        // prepare items
        foreach ($collection as $item) {
            $item->setId($this->_cmsWysiwygImages->idEncode($item->getBasename()));
            $item->setName($item->getBasename());
            $item->setShortName($this->_cmsWysiwygImages->getShortFilename($item->getBasename()));
            $item->setUrl($this->_cmsWysiwygImages->getCurrentUrl() . $item->getBasename());

            if ($this->isImage($item->getBasename())) {
                $thumbUrl = $this->getThumbnailUrl($item->getFilename(), true);
                // generate thumbnail "on the fly" if it does not exists
                if (!$thumbUrl) {
                    $thumbUrl = $this->_backendUrl->getUrl('cms/*/thumbnail', array('file' => $item->getId()));
                }

                $size = @getimagesize($item->getFilename());

                if (is_array($size)) {
                    $item->setWidth($size[0]);
                    $item->setHeight($size[1]);
                }
            } else {
                $thumbUrl = $this->_assetRepo->getUrl(self::THUMB_PLACEHOLDER_PATH_SUFFIX);
            }

            $item->setThumbUrl($thumbUrl);
        }

        return $collection;
    }

    /**
     * Storage collection
     *
     * @param string $path Path to the directory
     * @return \Magento\Cms\Model\Wysiwyg\Images\Storage\Collection
     */
    public function getCollection($path = null)
    {
        /** @var \Magento\Cms\Model\Wysiwyg\Images\Storage\Collection $collection */
        $collection = $this->_storageCollectionFactory->create();
        if ($path !== null) {
            $collection->addTargetDir($path);
        }
        return $collection;
    }

    /**
     * Create new directory in storage
     *
     * @param string $name New directory name
     * @param string $path Parent directory path
     * @return array New directory info
     * @throws \Magento\Framework\Model\Exception
     */
    public function createDirectory($name, $path)
    {
        if (!preg_match(self::DIRECTORY_NAME_REGEXP, $name)) {
            throw new \Magento\Framework\Model\Exception(
                __('Please correct the folder name. Use only letters, numbers, underscores and dashes.')
            );
        }

        $relativePath = $this->_directory->getRelativePath($path);
        if (!$this->_directory->isDirectory($relativePath) || !$this->_directory->isWritable($relativePath)) {
            $path = $this->_cmsWysiwygImages->getStorageRoot();
        }

        $newPath = $path . '/' . $name;
        $relativeNewPath = $this->_directory->getRelativePath($newPath);
        if ($this->_directory->isDirectory($relativeNewPath)) {
            throw new \Magento\Framework\Model\Exception(
                __('We found a directory with the same name. Please try another folder name.')
            );
        }

        $this->_directory->create($relativeNewPath);
        try {
            if ($this->_coreFileStorageDb->checkDbUsage()) {
                $relativePath = $this->_coreFileStorageDb->getMediaRelativePath($newPath);
                $this->_directoryDatabaseFactory->create()->createRecursive($relativePath);
            }

            $result = array(
                'name' => $name,
                'short_name' => $this->_cmsWysiwygImages->getShortFilename($name),
                'path' => $newPath,
                'id' => $this->_cmsWysiwygImages->convertPathToId($newPath)
            );
            return $result;
        } catch (\Magento\Framework\Filesystem\FilesystemException $e) {
            throw new \Magento\Framework\Model\Exception(__('We cannot create a new directory.'));
        }
    }

    /**
     * Recursively delete directory from storage
     *
     * @param string $path Target dir
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    public function deleteDirectory($path)
    {
        if ($this->_coreFileStorageDb->checkDbUsage()) {
            $this->_directoryDatabaseFactory->create()->deleteDirectory($path);
        }
        try {
            $this->_deleteByPath($path);
            $path = $this->getThumbnailRoot() . $this->_getRelativePathToRoot($path);
            $this->_deleteByPath($path);
        } catch (\Magento\Framework\Filesystem\FilesystemException $e) {
            throw new \Magento\Framework\Model\Exception(__('We cannot delete directory %1.', $path));
        }
    }

    /**
     * Delete by path
     *
     * @param string $path
     * @return void
     */
    protected function _deleteByPath($path)
    {
        $path = $this->_sanitizePath($path);
        if (!empty($path)) {
            $this->_validatePath($path);
            $this->_directory->delete($this->_directory->getRelativePath($path));
        }
    }

    /**
     * Delete file (and its thumbnail if exists) from storage
     *
     * @param string $target File path to be deleted
     * @return $this
     */
    public function deleteFile($target)
    {
        $relativePath = $this->_directory->getRelativePath($target);
        if ($this->_directory->isFile($relativePath)) {
            $this->_directory->delete($relativePath);
        }
        $this->_coreFileStorageDb->deleteFile($target);

        $thumb = $this->getThumbnailPath($target, true);
        $relativePathThumb = $this->_directory->getRelativePath($thumb);
        if ($thumb) {
            if ($this->_directory->isFile($relativePathThumb)) {
                $this->_directory->delete($relativePathThumb);
            }
            $this->_coreFileStorageDb->deleteFile($thumb);
        }
        return $this;
    }

    /**
     * Upload and resize new file
     *
     * @param string $targetPath Target directory
     * @param string $type Type of storage, e.g. image, media etc.
     * @return array File info Array
     * @throws \Magento\Framework\Model\Exception
     */
    public function uploadFile($targetPath, $type = null)
    {
        /** @var \Magento\Core\Model\File\Uploader $uploader */
        $uploader = $this->_uploaderFactory->create(array('fileId' => 'image'));
        $allowed = $this->getAllowedExtensions($type);
        if ($allowed) {
            $uploader->setAllowedExtensions($allowed);
        }
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);
        $result = $uploader->save($targetPath);

        if (!$result) {
            throw new \Magento\Framework\Model\Exception(__('We cannot upload the file.'));
        }

        // create thumbnail
        $this->resizeFile($targetPath . '/' . $uploader->getUploadedFileName(), true);

        $result['cookie'] = array(
            'name' => $this->getSession()->getName(),
            'value' => $this->getSession()->getSessionId(),
            'lifetime' => $this->getSession()->getCookieLifetime(),
            'path' => $this->getSession()->getCookiePath(),
            'domain' => $this->getSession()->getCookieDomain()
        );

        return $result;
    }

    /**
     * Thumbnail path getter
     *
     * @param  string $filePath original file path
     * @param  bool $checkFile OPTIONAL is it necessary to check file availability
     * @return string|false
     */
    public function getThumbnailPath($filePath, $checkFile = false)
    {
        $mediaRootDir = $this->_cmsWysiwygImages->getStorageRoot();

        if (strpos($filePath, $mediaRootDir) === 0) {
            $thumbPath = $this->getThumbnailRoot() . substr($filePath, strlen($mediaRootDir));

            if (!$checkFile || $this->_directory->isExist($this->_directory->getRelativePath($thumbPath))) {
                return $thumbPath;
            }
        }

        return false;
    }

    /**
     * Thumbnail URL getter
     *
     * @param  string $filePath original file path
     * @param  bool $checkFile OPTIONAL is it necessary to check file availability
     * @return string|false
     */
    public function getThumbnailUrl($filePath, $checkFile = false)
    {
        $mediaRootDir = $this->_cmsWysiwygImages->getStorageRoot();

        if (strpos($filePath, $mediaRootDir) === 0) {
            $thumbSuffix = self::THUMBS_DIRECTORY_NAME . substr($filePath, strlen($mediaRootDir));
            if (!$checkFile || $this->_directory->isExist(
                $this->_directory->getRelativePath($mediaRootDir . '/' . $thumbSuffix)
            )
            ) {
                $thumbSuffix = substr(
                    $mediaRootDir,
                    strlen($this->_directory->getAbsolutePath())
                ) . '/' . $thumbSuffix;
                $randomIndex = '?rand=' . time();
                return str_replace('\\', '/', $this->_cmsWysiwygImages->getBaseUrl() . $thumbSuffix) . $randomIndex;
            }
        }

        return false;
    }

    /**
     * Create thumbnail for image and save it to thumbnails directory
     *
     * @param string $source Image path to be resized
     * @param bool $keepRation Keep aspect ratio or not
     * @return bool|string Resized filepath or false if errors were occurred
     */
    public function resizeFile($source, $keepRation = true)
    {
        $realPath = $this->_directory->getRelativePath($source);
        if (!$this->_directory->isFile($realPath) || !$this->_directory->isExist($realPath)) {
            return false;
        }

        $targetDir = $this->getThumbsPath($source);
        $pathTargetDir = $this->_directory->getRelativePath($targetDir);
        if (!$this->_directory->isExist($pathTargetDir)) {
            $this->_directory->create($pathTargetDir);
        }
        if (!$this->_directory->isExist($pathTargetDir)) {
            return false;
        }
        $image = $this->_imageFactory->create();
        $image->open($source);
        $image->keepAspectRatio($keepRation);
        $image->resize($this->_resizeParameters['width'], $this->_resizeParameters['height']);
        $dest = $targetDir . '/' . pathinfo($source, PATHINFO_BASENAME);
        $image->save($dest);
        if ($this->_directory->isFile($this->_directory->getRelativePath($dest))) {
            return $dest;
        }
        return false;
    }

    /**
     * Resize images on the fly in controller action
     *
     * @param string $filename File basename
     * @return bool|string Thumbnail path or false for errors
     */
    public function resizeOnTheFly($filename)
    {
        $path = $this->getSession()->getCurrentPath();
        if (!$path) {
            $path = $this->_cmsWysiwygImages->getCurrentPath();
        }
        return $this->resizeFile($path . '/' . $filename);
    }

    /**
     * Return thumbnails directory path for file/current directory
     *
     * @param bool|string $filePath Path to the file
     * @return string
     */
    public function getThumbsPath($filePath = false)
    {
        $mediaRootDir = $this->_cmsWysiwygImages->getStorageRoot();
        $thumbnailDir = $this->getThumbnailRoot();

        if ($filePath && strpos($filePath, $mediaRootDir) === 0) {
            $thumbnailDir .= dirname(substr($filePath, strlen($mediaRootDir)));
        }

        return $thumbnailDir;
    }

    /**
     * Storage session
     *
     * @return \Magento\Backend\Model\Session
     */
    public function getSession()
    {
        return $this->_session;
    }

    /**
     * Prepare allowed_extensions config settings
     *
     * @param string $type Type of storage, e.g. image, media etc.
     * @return array Array of allowed file extensions
     */
    public function getAllowedExtensions($type = null)
    {
        if (is_string($type) && array_key_exists("{$type}_allowed", $this->_extensions)) {
            $allowed = $this->_extensions["{$type}_allowed"];
        } else {
            $allowed = $this->_extensions['allowed'];
        }

        return array_keys(array_filter($allowed));
    }

    /**
     * Thumbnail root directory getter
     *
     * @return string
     */
    public function getThumbnailRoot()
    {
        return $this->_cmsWysiwygImages->getStorageRoot() . '/' . self::THUMBS_DIRECTORY_NAME;
    }

    /**
     * Simple way to check whether file is image or not based on extension
     *
     * @param string $filename
     * @return bool
     */
    public function isImage($filename)
    {
        if (!$this->hasData('_image_extensions')) {
            $this->setData('_image_extensions', $this->getAllowedExtensions('image'));
        }
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, $this->_getData('_image_extensions'));
    }

    /**
     * Get resize width
     *
     * @return int
     */
    public function getResizeWidth()
    {
        return $this->_resizeParameters['width'];
    }

    /**
     * Get resize height
     *
     * @return int
     */
    public function getResizeHeight()
    {
        return $this->_resizeParameters['height'];
    }

    /**
     * Get cms wysiwyg images helper
     *
     * @return Images|null
     */
    public function getCmsWysiwygImages()
    {
        return $this->_cmsWysiwygImages;
    }

    /**
     * Is path under storage root directory
     *
     * @param string $path
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _validatePath($path)
    {
        $root = $this->_sanitizePath($this->_cmsWysiwygImages->getStorageRoot());
        if ($root == $path) {
            throw new \Magento\Framework\Model\Exception(__('We cannot delete root directory %1.', $path));
        }
        if (strpos($path, $root) !== 0) {
            throw new \Magento\Framework\Model\Exception(__('Directory %1 is not under storage root path.', $path));
        }
    }

    /**
     * Sanitize path
     *
     * @param string $path
     * @return string
     */
    protected function _sanitizePath($path)
    {
        return rtrim(preg_replace('~[/\\\]+~', '/', $this->_directory->getDriver()->getRealPath($path)), '/');
    }

    /**
     * Get path in root storage dir
     *
     * @param string $path
     * @return string|bool
     */
    protected function _getRelativePathToRoot($path)
    {
        return substr(
            $this->_sanitizePath($path),
            strlen($this->_sanitizePath($this->_cmsWysiwygImages->getStorageRoot()))
        );
    }
}
