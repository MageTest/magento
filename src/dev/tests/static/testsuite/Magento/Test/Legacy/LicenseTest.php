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
 * Tests to ensure that all license blocks are represented by placeholders
 */
namespace Magento\Test\Legacy;

class LicenseTest extends \PHPUnit_Framework_TestCase
{
    public function testLegacyComment()
    {
        $invoker = new \Magento\TestFramework\Utility\AggregateInvoker($this);
        $invoker(
            function ($filename) {
                $fileText = file_get_contents($filename);
                if (!preg_match_all('#/\*\*.+@copyright.+?\*/#s', $fileText, $matches)) {
                    return;
                }

                foreach ($matches[0] as $commentText) {
                    foreach (array('Irubin Consulting Inc', 'DBA Varien', 'Magento Inc') as $legacyText) {
                        $this->assertNotContains(
                            $legacyText,
                            $commentText,
                            "The license of file {$filename} contains legacy text."
                        );
                    }
                }
            },
            $this->legacyCommentDataProvider()
        );
    }

    public function legacyCommentDataProvider()
    {
        $root = \Magento\TestFramework\Utility\Files::init()->getPathToSource();
        $recursiveIterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS)
        );

        $rootFolderName = substr(strrchr($root, '/'), 1);
        $extensions = '(xml|css|php|phtml|js|dist|sample|additional)';
        $paths = array(
            $rootFolderName . '/[^/]+\.' . $extensions,
            $rootFolderName . '/app/.+\.' . $extensions,
            $rootFolderName . '/dev/(?!tests/integration/tmp|tests/functional).+\.' . $extensions,
            $rootFolderName . '/lib/internal/(Mage|Magento|Varien)/.+\.' . $extensions,
            $rootFolderName . '/pub/.+\.' . $extensions
        );
        $regexIterator = new \RegexIterator($recursiveIterator, '#(' . implode(' | ', $paths) . ')$#x');

        $result = array();
        foreach ($regexIterator as $fileInfo) {
            $filename = (string)$fileInfo;
            if (!file_exists($filename) || !is_readable($filename)) {
                continue;
            }
            $result[] = array($filename);
        }
        return $result;
    }
}
