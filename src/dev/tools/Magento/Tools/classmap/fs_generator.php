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
$basePath = realpath(__DIR__ . '/../../../../../') . DIRECTORY_SEPARATOR;
set_include_path(get_include_path() . PATH_SEPARATOR . $basePath);

echo
'Magento classmap generator', PHP_EOL,
PHP_EOL,
'Usage:', PHP_EOL,
'   php -f ', str_replace(dirname(__FILE__), __FILE__, ''),
' -- -t classmap.ser -i app/code;lib/internal -p ";"', PHP_EOL,
PHP_EOL,
'Parameters:', PHP_EOL,
'   -t   - Target file [optional, default - "{magento_root}/var/classmap.ser"]', PHP_EOL,
'   -i   - Include path [optional, default - "{magento_root}/app/code;{magento_root}/lib/internal"]', PHP_EOL,
'   -p   - Paths separator for include path [optional, default - ";"]', PHP_EOL;

$args = getopt('t:i::p::');
$includePath = isset($args['i']) ? $args['i'] : "{$basePath}app/code;{$basePath}lib/internal";
$pathSeparator = isset($args['p']) ? $args['p'] : ';';
$targetFile = isset($args['i']) ? $args['i'] : "{$basePath}var/classmap.ser";
$map = array();

foreach (array_reverse(explode($pathSeparator, $includePath)) as $path) {
    echo 'Scanning: ' . $path . PHP_EOL;
    $directory = new RecursiveDirectoryIterator($path);
    $iterator = new RecursiveIteratorIterator($directory);
    $regex = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

    foreach ($regex as $file) {
        $filePath = str_replace('\\', '/', str_replace($basePath, '', $file[0]));
        if (strpos($filePath, 'dev') === 0 || strpos($filePath, 'shell') === 0) {
            continue;
        }

        $code = file_get_contents($file[0]);
        $tokens = token_get_all($code);

        $count = count($tokens);
        $i = 0;
        $namespace = '';
        while ($i < $count) {
            $token = $tokens[$i];

            if (!is_array($token)) {
                $i++;
                continue;
            }

            list($id, $content, $line) = $token;

            switch ($id) {
                case T_NAMESPACE:
                    $namespace = '';
                    do {
                        ++$i;
                        if (isset($tokens[$i])) {
                            $token = $tokens[$i];
                            if (is_string($token)) {
                                continue;
                            }
                            list($type, $content, $line) = $token;
                            switch ($type) {
                                case T_STRING:
                                case T_NS_SEPARATOR:
                                    $namespace .= $content;
                                    break;
                            }
                        }
                    } while ($token !== ';' && $i < $count);
                    break;
                case T_CLASS:
                case T_INTERFACE:
                    $class = '';
                    do {
                        ++$i;
                        if (isset($tokens[$i])) {
                            $token = $tokens[$i];
                            if (is_string($token)) {
                                continue;
                            }
                            list($type, $content, $line) = $token;
                            switch ($type) {
                                case T_STRING:
                                    $class = $content;
                                    break;
                            }
                        }
                    } while (empty($class) && $i < $count);

                    // If a classname was found, set it in the object, and
                    // return boolean true (found)
                    if (!empty($class)) {
                        $map[(empty($namespace) ? '' : ($namespace . '\\')) . $class] = $filePath;
                    }
                    break;
                default:
                    break;
            }
            ++$i;
        }
    }
}

file_put_contents($targetFile, serialize($map));

echo 'Done!';
