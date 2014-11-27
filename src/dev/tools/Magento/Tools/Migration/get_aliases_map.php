<?php
/**
 * Automated replacement of factory names into real ones and put result information into file
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

define(
    'USAGE',
<<<USAGE
$>./get_aliases_map.php -- [-ph]
    Build Magento 1 Aliases To Magento 2 Classes Names.
    Additional parameters:
    -p          path to code scope of magento instance
    -h          print usage

USAGE
);

$options = getopt('p:h');

if (isset($options['h'])) {
    echo USAGE;
    exit(0);
}

require_once realpath(
    dirname(dirname(dirname(dirname(dirname(__DIR__)))))
) . '/dev/tests/static/framework/bootstrap.php';
require_once realpath(
    dirname(dirname(dirname(dirname(dirname(__DIR__)))))
) . '/dev/tests/static/framework/Magento/TestFramework/Utility/Classes.php';
require_once realpath(dirname(dirname(dirname(dirname(dirname(__DIR__)))))) . '/lib/internal/Zend/Json.php';

$magentoBaseDir = dirname(__DIR__) . '/../../../../';
if (isset($options['p'])) {
    $magentoBaseDir = $options['p'];
}

$utilityFiles = new Magento\TestFramework\Utility\Files($magentoBaseDir);
$map = array();
$compositeModules = getFilesCombinedArray(__DIR__ . '/aliases_map', '/^composite_modules_.*\.php$/');
// PHP code
foreach ($utilityFiles->getPhpFiles(true, true, true, false) as $file) {
    $content = file_get_contents($file);
    $classes = \Magento\TestFramework\Utility\Classes::collectPhpCodeClasses($content);
    if ($classes) {
        $factoryNames = array_filter($classes, 'isFactoryName');
        foreach ($factoryNames as $factoryName) {
            list($module, $name) = getModuleName($factoryName, $compositeModules);
            $patterns = array(
                '::getModel(\'%s\'' => 'Model',
                '::getSingleton(\'%s\'' => 'Model',
                '::getResourceModel(\'%s\'' => 'Model_Resource',
                '::getResourceSingleton(\'%s\'' => 'Model_Resource',
                'addBlock(\'%s\'' => 'Block',
                'createBlock(\'%s\'' => 'Block',
                'getBlockClassName(\'%s\'' => 'Block',
                'getBlockSingleton(\'%s\'' => 'Block'
            );

            foreach ($patterns as $pattern => $classType) {
                if (isPatternExist($content, $pattern, $factoryName)) {
                    if (!isset($map[$classType])) {
                        $map[$classType] = array();
                    }

                    $map[$classType][$factoryName] = getClassName($module, $classType, $name);
                }
            }
        }
    }
}

// layouts
$classType = 'Block';
$layouts = $utilityFiles->getLayoutFiles(array(), false);
foreach ($layouts as $file) {
    $xml = simplexml_load_file($file);
    $classes = \Magento\TestFramework\Utility\Classes::collectLayoutClasses($xml);
    $factoryNames = array_filter($classes, 'isFactoryName');
    if (!$factoryNames) {
        continue;
    }
    foreach ($factoryNames as $factoryName) {
        list($module, $name) = getModuleName($factoryName, $compositeModules);
        $map[$classType][$factoryName] = getClassName($module, $classType, $name);
    }
}

echo Zend_Json::prettyPrint(Zend_Json::encode($map));

/**
 * Get combined array from similar files by pattern
 *
 * @param string $dirPath
 * @param string $filePattern
 * @return array
 */
function getFilesCombinedArray($dirPath, $filePattern)
{
    $result = array();
    $directoryIterator = new DirectoryIterator($dirPath);
    $patternIterator = new RegexIterator($directoryIterator, $filePattern);

    foreach ($patternIterator as $fileInfo) {
        $arrayFromFile = include_once $fileInfo->getPathname();
        $result = array_merge($result, $arrayFromFile);
    }
    return $result;
}

/**
 * Check if pattern exist in file content
 *
 * @param string $content
 * @param string $pattern
 * @param string $alias
 * @return bool
 */
function isPatternExist($content, $pattern, $alias)
{
    $search = sprintf($pattern, $alias);
    return strpos($content, $search) !== false;
}

/**
 * Build class name supported in magento 2
 *
 * @param string $module
 * @param string $type
 * @param string $name
 * @return string|bool
 */
function getClassName($module, $type, $name = null)
{
    if (empty($name)) {
        if ('Helper' !== $type) {
            return false;
        }
        $name = 'data';
    }

    return implode('_', array_map('ucfirst', explode('_', $module . '_' . $type . '_' . $name)));
}

/**
 * Whether the given class name is a factory name
 *
 * @param string $class
 * @return bool
 */
function isFactoryName($class)
{
    return false !== strpos($class, '/') || preg_match('/^[a-z\d]+(_[A-Za-z\d]+)?$/', $class);
}

/**
 * Transform factory name into a pair of module and name
 *
 * @param string $factoryName
 * @param array $compositeModules
 * @return array
 */
function getModuleName($factoryName, $compositeModules = array())
{
    if (false !== strpos($factoryName, '/')) {
        list($module, $name) = explode('/', $factoryName);
    } else {
        $module = $factoryName;
        $name = false;
    }
    if (array_key_exists($module, $compositeModules)) {
        $module = $compositeModules[$module];
    } elseif (false === strpos($module, '_')) {
        $module = "Magento_{$module}";
    }
    return array($module, $name);
}
