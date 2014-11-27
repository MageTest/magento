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

namespace Magento\Framework\View\Design\FileResolution;

use Magento\Framework\App\Bootstrap as AppBootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Fallback Test
 *
 * @package Magento\View
 * @magentoDataFixture Magento/Framework/View/_files/fallback/themes_registration.php
 */
class FallbackTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Design\Theme\FlyweightFactory
     */
    private $themeFactory;

    protected function setUp()
    {
        Bootstrap::getInstance()->reinitialize(array(
            AppBootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS => array(
                DirectoryList::THEMES => array(
                    'path' => __DIR__ . '/../../_files/fallback/design'
                ),
                DirectoryList::LIB_WEB => array(
                    'path' => __DIR__ . '/../../_files/fallback/lib/web'
                ),
            )
        ));
        /** @var \Magento\Framework\View\Design\Theme\FlyweightFactory $themeFactory */
        $this->themeFactory = Bootstrap::getObjectManager()
            ->get('Magento\Framework\View\Design\Theme\FlyweightFactory');
    }

    /**
     * @param string $file
     * @param string $themePath
     * @param string|null $module
     * @param string|null $expectedFilename
     *
     * @dataProvider getTemplateFileDataProvider
     */
    public function testGetTemplateFile($file, $themePath, $module, $expectedFilename)
    {
        /** @var \Magento\Framework\View\Design\FileResolution\Fallback\TemplateFile $model */
        $model = Bootstrap::getObjectManager()
            ->create('Magento\Framework\View\Design\FileResolution\Fallback\TemplateFile');
        $themeModel = $this->themeFactory->create($themePath);

        $actualFilename = $model->getFile('frontend', $themeModel, $file, $module);
        if ($expectedFilename) {
            $this->assertInternalType('string', $actualFilename);
            $this->assertStringMatchesFormat($expectedFilename, $actualFilename);
            $this->assertFileExists($actualFilename);
        } else {
            $this->assertFalse($actualFilename);
        }
    }

    /**
     * @return array
     */
    public function getTemplateFileDataProvider()
    {
        return array(
            'non-modular: no default inheritance' => array(
                'fixture_template.phtml', 'vendor_standalone_theme', null,
                null,
            ),
            'non-modular: inherit parent theme' => array(
                'fixture_template.phtml', 'vendor_custom_theme', null,
                '%s/frontend/vendor_default/templates/fixture_template.phtml',
            ),
            'non-modular: inherit grandparent theme' => array(
                'fixture_template.phtml', 'vendor_custom_theme2', null,
                '%s/frontend/vendor_default/templates/fixture_template.phtml',
            ),
            'modular: no default inheritance' => array(
                'fixture_template.phtml', 'vendor_standalone_theme', 'Fixture_Module',
                null,
            ),
            'modular: no fallback to non-modular file' => array(
                'fixture_template.phtml', 'vendor_default', 'NonExisting_Module',
                null,
            ),
            'modular: inherit parent theme' => array(
                'fixture_template.phtml', 'vendor_custom_theme', 'Fixture_Module',
                '%s/frontend/vendor_default/Fixture_Module/templates/fixture_template.phtml',
            ),
            'modular: inherit grandparent theme' => array(
                'fixture_template.phtml', 'vendor_custom_theme2', 'Fixture_Module',
                '%s/frontend/vendor_default/Fixture_Module/templates/fixture_template.phtml',
            ),
        );
    }

    /**
     * @param string $themePath
     * @param string $locale
     * @param string|null $expectedFilename
     *
     * @dataProvider getLocaleFileDataProvider
     */
    public function testGetI18nCsvFile($themePath, $locale, $expectedFilename)
    {
        /** @var \Magento\Framework\View\Design\FileResolution\Fallback\File $model */
        $model = Bootstrap::getObjectManager()->create('Magento\Framework\View\Design\FileResolution\Fallback\File');
        $themeModel = $this->themeFactory->create($themePath);

        $actualFilename = $model->getFile('frontend', $themeModel, 'i18n/' . $locale . '.csv');

        if ($expectedFilename) {
            $this->assertInternalType('string', $actualFilename);
            $this->assertStringMatchesFormat($expectedFilename, $actualFilename);
            $this->assertFileExists($actualFilename);
        } else {
            $this->assertFalse($actualFilename);
        }
    }

    public function getLocaleFileDataProvider()
    {
        return array(
            'no default inheritance' => array(
                'vendor_standalone_theme', 'en_US',
                null,
            ),
            'inherit parent theme' => array(
                'vendor_custom_theme', 'en_US',
                '%s/frontend/vendor_custom_theme/i18n/en_US.csv',
            ),
            'inherit grandparent theme' => array(
                'vendor_custom_theme2', 'en_US',
                '%s/frontend/vendor_custom_theme/i18n/en_US.csv',
            ),
        );
    }

    /**
     * Test for the static files fallback according to the themes inheritance
     *
     * @param string $file
     * @param string $themePath
     * @param string $locale
     * @param string $module
     * @param string|null $expectedFilename
     *
     * @dataProvider getViewFileDataProvider
     */
    public function testGetViewFile($file, $themePath, $locale, $module, $expectedFilename)
    {
        /** @var \Magento\Framework\View\Design\FileResolution\Fallback\StaticFile $model */
        $model = Bootstrap::getObjectManager()
            ->create('Magento\Framework\View\Design\FileResolution\Fallback\StaticFile');
        $themeModel = $this->themeFactory->create($themePath);

        $actualFilename = $model->getFile('frontend', $themeModel, $locale, $file, $module);
        if ($expectedFilename) {
            $this->assertInternalType('string', $actualFilename);
            $this->assertStringMatchesFormat($expectedFilename, $actualFilename);
            $this->assertFileExists($actualFilename);
        } else {
            $this->assertFalse($actualFilename);
        }
    }

    public function getViewFileDataProvider()
    {
        return array(
            'non-modular: no default inheritance' => array(
                'fixture_script.js', 'vendor_standalone_theme', null, null,
                null,
            ),
            'non-modular: inherit same package & parent theme' => array(
                'fixture_script.js', 'vendor_custom_theme', null, null,
                '%s/frontend/vendor_default/web/fixture_script.js',
            ),
            'non-modular: inherit same package & grandparent theme' => array(
                'fixture_script.js', 'vendor_custom_theme2', null, null,
                '%s/frontend/vendor_default/web/fixture_script.js',
            ),
            'non-modular: fallback to non-localized file' => array(
                'fixture_script.js', 'vendor_default', 'en_US', null,
                '%s/frontend/vendor_default/web/fixture_script.js',
            ),
            'non-modular: localized file' => array(
                'fixture_script.js', 'vendor_default', 'ru_RU', null,
                '%s/frontend/vendor_default/web/i18n/ru_RU/fixture_script.js',
            ),
            'non-modular: override js lib file' => array(
                'mage/script.js', 'vendor_custom_theme', null, null,
                '%s/frontend/vendor_custom_theme/web/mage/script.js',
            ),
            'non-modular: inherit js lib file' => array(
                'mage/script.js', 'vendor_default', null, null,
                '%s/lib/web/mage/script.js',
            ),
            'modular: no default inheritance' => array(
                'fixture_script.js', 'vendor_standalone_theme', null, 'Fixture_Module',
                null,
            ),
            'modular: no fallback to non-modular file' => array(
                'fixture_script.js', 'vendor_default', null, 'NonExisting_Module',
                null,
            ),
            'modular: no fallback to js lib file' => array(
                'mage/script.js', 'vendor_default', null, 'Fixture_Module',
                null,
            ),
            'modular: no fallback to non-modular localized file' => array(
                'fixture_script.js', 'vendor_default', 'ru_RU', 'NonExisting_Module',
                null,
            ),
            'modular: inherit same package & parent theme' => array(
                'fixture_script.js', 'vendor_custom_theme', null, 'Fixture_Module',
                '%s/frontend/vendor_default/Fixture_Module/web/fixture_script.js',
            ),
            'modular: inherit same package & grandparent theme' => array(
                'fixture_script.js', 'vendor_custom_theme2', null, 'Fixture_Module',
                '%s/frontend/vendor_default/Fixture_Module/web/fixture_script.js',
            ),
            'modular: fallback to non-localized file' => array(
                'fixture_script.js', 'vendor_default', 'en_US', 'Fixture_Module',
                '%s/frontend/vendor_default/Fixture_Module/web/fixture_script.js',
            ),
            'modular: localized file' => array(
                'fixture_script.js', 'vendor_custom_theme2', 'ru_RU', 'Fixture_Module',
                '%s/frontend/vendor_default/Fixture_Module/web/i18n/ru_RU/fixture_script.js',
            ),
        );
    }
}
