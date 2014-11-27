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
namespace Magento\Test\Integrity\Theme;

use Magento\Framework\App\Filesystem\DirectoryList;

class XmlFilesTest extends \PHPUnit_Framework_TestCase
{
    const NO_VIEW_XML_FILES_MARKER = 'no-view-xml';

    /**
     * @param string $file
     * @dataProvider viewConfigFileDataProvider
     */
    public function testViewConfigFile($file)
    {
        if ($file === self::NO_VIEW_XML_FILES_MARKER) {
            $this->markTestSkipped('No view.xml files in themes.');
        }
        $this->_validateConfigFile(
            $file,
            $this->getPath(DirectoryList::LIB_INTERNAL) . '/Magento/Framework/Config/etc/view.xsd'
        );
    }

    /**
     * @return array
     */
    public function viewConfigFileDataProvider()
    {
        $result = [];
        $files = glob($this->getPath(DirectoryList::THEMES) . '/*/*/view.xml');
        foreach ($files as $file) {
            $result[substr($file, strlen(BP))] = [$file];
        }
        return $result === [] ? [[self::NO_VIEW_XML_FILES_MARKER]] : $result;
    }

    /**
     * @param string $themeDir
     * @dataProvider themeConfigFileExistsDataProvider
     */
    public function testThemeConfigFileExists($themeDir)
    {
        $this->assertFileExists($themeDir . '/theme.xml');
    }

    /**
     * @return array
     */
    public function themeConfigFileExistsDataProvider()
    {
        $result = [];
        $files = glob($this->getPath(DirectoryList::THEMES) . '/*/*/*', GLOB_ONLYDIR);
        foreach ($files as $themeDir) {
            $result[substr($themeDir, strlen(BP))] = [$themeDir];
        }
        return $result;
    }

    /**
     * @param string $file
     * @dataProvider themeConfigFileDataProvider
     */
    public function testThemeConfigFileSchema($file)
    {
        $this->_validateConfigFile(
            $file,
            $this->getPath(DirectoryList::LIB_INTERNAL) . '/Magento/Framework/Config/etc/theme.xsd'
        );
    }

    /**
     * Configuration should declare a single package/theme that corresponds to the file system directories
     *
     * @param string $file
     * @dataProvider themeConfigFileDataProvider
     */
    public function testThemeConfigFileHasSingleTheme($file)
    {
        /** @var $configXml \SimpleXMLElement */
        $configXml = simplexml_load_file($file);
        $actualThemes = $configXml->xpath('/theme');
        $this->assertCount(1, $actualThemes, 'Single theme declaration is expected.');
    }

    /**
     * @return array
     */
    public function themeConfigFileDataProvider()
    {
        $result = [];
        $files = glob($this->getPath(DirectoryList::THEMES) . '/*/*/*/theme.xml');
        foreach ($files as $file) {
            $result[substr($file, strlen(BP))] = [$file];
        }
        return $result;
    }

    /**
     * Perform test whether a configuration file is valid
     *
     * @param string $file
     * @param string $schemaFile
     * @throws \PHPUnit_Framework_AssertionFailedError if file is invalid
     */
    protected function _validateConfigFile($file, $schemaFile)
    {
        $domConfig = new \Magento\Framework\Config\Dom(file_get_contents($file));
        $errors = array();
        $result = $domConfig->validate($schemaFile, $errors);
        $message = "Invalid XML-file: {$file}\n";
        foreach ($errors as $error) {
            $message .= "{$error->message} Line: {$error->line}\n";
        }
        $this->assertTrue($result, $message);
    }

    /**
     * Get directory path by code
     *
     * @param string $code
     * @return string
     */
    protected function getPath($code)
    {
        /** @var \Magento\Framework\Filesystem $filesystem */
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\Filesystem'
        );
        return $filesystem->getDirectoryRead($code)->getAbsolutePath();
    }
}
