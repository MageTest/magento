<?php
/**
 * Test constructions of layout files
 *
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
 * This test finds usages of modular view files, searched in non-modular context - it is obsolete and buggy
 * functionality, initially introduced in Magento 2.
 *
 * The test goes through modular calls of view files, and finds out, whether there are theme non-modular files
 * with the same path. Before fixing the bug, such call return theme files instead of  modular files, which is
 * incorrect. After fixing the bug, such calls will start returning modular files, which is not a file we got used
 * to see, so such cases are probably should be fixed. The test finds such suspicious places.
 *
 * The test is intended to be deleted before Magento 2 release. With the release, having non-modular files with the
 * same paths as modular ones, is legitimate.
 */
namespace Magento\Test\Integrity;

class ViewFileReferenceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Design\Fallback\Rule\RuleInterface
     */
    protected static $_fallbackRule;

    /**
     * @var \Magento\Framework\View\Design\FileResolution\Fallback\StaticFile
     */
    protected static $_viewFilesFallback;

    /**
     * @var \Magento\Framework\View\Design\FileResolution\Fallback\File
     */
    static protected $_filesFallback;

    /**
     * @var array
     */
    protected static $_checkThemeLocales = array();

    /**
     * @var \Magento\Core\Model\Theme\Collection
     */
    protected static $_themeCollection;

    public static function setUpBeforeClass()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->configure(
            array('preferences' => array('Magento\Core\Model\Theme' => 'Magento\Core\Model\Theme\Data'))
        );

        /** @var $fallbackPool \Magento\Framework\View\Design\Fallback\RulePool */
        $fallbackPool = $objectManager->get('Magento\Framework\View\Design\Fallback\RulePool');
        self::$_fallbackRule = $fallbackPool->getRule(
            \Magento\Framework\View\Design\Fallback\RulePool::TYPE_STATIC_FILE
        );

        self::$_viewFilesFallback = $objectManager->get(
            'Magento\Framework\View\Design\FileResolution\Fallback\StaticFile'
        );
        self::$_filesFallback = $objectManager->get('Magento\Framework\View\Design\FileResolution\Fallback\File');

        // Themes to be checked
        self::$_themeCollection = $objectManager->get('Magento\Core\Model\Theme\Collection');
        self::$_themeCollection->addDefaultPattern('*');

        // Compose list of locales, needed to be checked for themes
        self::$_checkThemeLocales = array();
        foreach (self::$_themeCollection as $theme) {
            $themeLocales = self::_getThemeLocales($theme);
            $themeLocales[] = null;
            // Default non-localized file will need to be checked as well
            self::$_checkThemeLocales[$theme->getFullPath()] = $themeLocales;
        }
    }

    /**
     * Return array of locales, supported by the theme
     *
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @return array
     */
    protected static function _getThemeLocales(\Magento\Framework\View\Design\ThemeInterface $theme)
    {
        $result = array();
        $patternDir = self::_getLocalePatternDir($theme);
        $localeModel = new \Zend_Locale();
        foreach (array_keys($localeModel->getLocaleList()) as $locale) {
            $dir = str_replace('<locale_placeholder>', $locale, $patternDir);
            if (is_dir($dir)) {
                $result[] = $locale;
            }
        }
        return $result;
    }

    /**
     * Return pattern for theme locale directories, where <locale_placeholder> is placed to mark a locale's location.
     *
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @return string
     * @throws \Exception
     */
    protected static function _getLocalePatternDir(\Magento\Framework\View\Design\ThemeInterface $theme)
    {
        $localePlaceholder = '<locale_placeholder>';
        $params = array('area' => $theme->getArea(), 'theme' => $theme, 'locale' => $localePlaceholder);
        $patternDirs = self::$_fallbackRule->getPatternDirs($params);
        $themePath = '/' . $theme->getFullPath() . '/';
        foreach ($patternDirs as $patternDir) {
            $patternPath = $patternDir . '/';
            if ((strpos($patternPath, $themePath) !== false) // It is theme's directory
                && (strpos($patternPath, $localePlaceholder) !== false) // It is localized directory
            ) {
                return $patternDir;
            }
        }
        throw new \Exception('Unable to determine theme locale path');
    }

    /**
     * @param string $modularCall
     * @param array $usages
     * @param null|string $area
     * @dataProvider modularFallbackDataProvider
     */
    public function testModularFallback($modularCall, array $usages, $area)
    {
        list(, $file) = explode(\Magento\Framework\View\Asset\Repository::FILE_ID_SEPARATOR, $modularCall);

        $wrongResolutions = array();
        foreach (self::$_themeCollection as $theme) {
            if ($area && $theme->getArea() != $area) {
                continue;
            }

            $found = $this->_getFileResolutions($theme, $file);
            $wrongResolutions = array_merge($wrongResolutions, $found);
        }

        if ($wrongResolutions) {
            // If file is found, then old functionality (find modular files in non-modular locations) is used
            $message = sprintf(
                "Found modular call:\n  %s in\n  %s\n  which may resolve to non-modular location(s):\n  %s",
                $modularCall,
                implode(', ', $usages),
                implode(', ', $wrongResolutions)
            );
            $this->fail($message);
        }
    }

    /**
     * Resolves file to find its fallback'ed paths
     *
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @param string $file
     * @return array
     */
    protected function _getFileResolutions(\Magento\Framework\View\Design\ThemeInterface $theme, $file)
    {
        $found = array();
        $fileResolved = self::$_filesFallback->getFile($theme->getArea(), $theme, $file);
        if (file_exists($fileResolved)) {
            $found[$fileResolved] = $fileResolved;
        }

        foreach (self::$_checkThemeLocales[$theme->getFullPath()] as $locale) {
            $fileResolved = self::$_viewFilesFallback->getFile($theme->getArea(), $theme, $locale, $file);
            if (file_exists($fileResolved)) {
                $found[$fileResolved] = $fileResolved;
            }
        }
        return $found;
    }

    /**
     * @return array
     */
    public static function modularFallbackDataProvider()
    {
        $result = array();
        foreach (self::_getFilesToProcess() as $file) {
            $file = (string)$file;

            $modulePattern = '[A-Z][a-z]+_[A-Z][a-z]+';
            $filePattern = '[[:alnum:]_/-]+\\.[[:alnum:]_./-]+';
            $pattern = '#' . $modulePattern
                . preg_quote(\Magento\Framework\View\Asset\Repository::FILE_ID_SEPARATOR)
                . $filePattern . '#S';
            if (!preg_match_all($pattern, file_get_contents($file), $matches)) {
                continue;
            }

            $area = self::_getArea($file);

            foreach ($matches[0] as $modularCall) {
                $dataSetKey = $modularCall . ' @ ' . ($area ?: 'any area');

                if (!isset($result[$dataSetKey])) {
                    $result[$dataSetKey] = array('modularCall' => $modularCall, 'usages' => array(), 'area' => $area);
                }
                $result[$dataSetKey]['usages'][$file] = $file;
            }
        }
        return $result;
    }

    /**
     * Return list of files, that must be processed, searching for modular calls to view files
     *
     * @return array
     */
    protected static function _getFilesToProcess()
    {
        $result = array();
        $rootDir = self::_getRootDir();
        foreach (array('app/code', 'app/design') as $subDir) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($rootDir . "/{$subDir}", \RecursiveDirectoryIterator::SKIP_DOTS)
            );
            $result = array_merge($result, iterator_to_array($iterator));
        }

        return $result;
    }

    /**
     * Return application root directory
     *
     * @return string
     */
    protected static function _getRootDir()
    {
        return realpath(__DIR__ . '/../../../../../../../');
    }

    /**
     * Get the area, where file is located.
     *
     * Null is returned, if the file is not within an area, e.g. it is a model/block/helper php-file.
     *
     * @param string $file
     * @return string|null
     */
    protected static function _getArea($file)
    {
        $file = str_replace('\\', '/', $file);
        $areaPatterns = array('#app/code/[^/]+/[^/]+/view/([^/]+)/#S', '#app/design/([^/]+)/#S');
        foreach ($areaPatterns as $pattern) {
            if (preg_match($pattern, $file, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }
}
