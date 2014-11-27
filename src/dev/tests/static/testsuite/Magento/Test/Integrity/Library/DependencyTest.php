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
namespace Magento\Test\Integrity\Library;

use Magento\TestFramework\Integrity\Library\Injectable;
use Magento\TestFramework\Integrity\Library\PhpParser\ParserFactory;
use Magento\TestFramework\Integrity\Library\PhpParser\Tokens;
use Magento\TestFramework\Utility\Files;
use Zend\Code\Reflection\FileReflection;

/**
 * Test check if Magento library components contain incorrect dependencies to application layer
 *
 */
class DependencyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Collect errors
     *
     * @var array
     */
    protected $errors = array();

    /**
     * Forbidden base namespaces
     *
     * @return array
     */
    protected function getForbiddenNamespaces()
    {
        return array('Magento');
    }

    public function testCheckDependencies()
    {
        $invoker = new \Magento\TestFramework\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $file
             */
            function ($file) {
                $fileReflection = new FileReflection($file);
                $tokens = new Tokens($fileReflection->getContents(), new ParserFactory());
                $tokens->parseContent();

                $dependencies = array_merge(
                    (new Injectable())->getDependencies($fileReflection),
                    $tokens->getDependencies()
                );

                $pattern = '#^(\\\\|)' . implode('|', $this->getForbiddenNamespaces()) . '\\\\#';
                foreach ($dependencies as $dependency) {
                    $filePath = BP . '/lib/internal/' . str_replace('\\', '/', $dependency) . '.php';
                    if (preg_match($pattern, $dependency) && !file_exists($filePath)) {
                        $this->errors[$fileReflection->getFileName()][] = $dependency;
                    }
                }

                if (!empty($this->errors)) {
                    $this->fail($this->getFailMessage());
                }
            },
            $this->libraryDataProvider()
        );
    }

    /**
     * @inheritdoc
     */
    public function tearDown()
    {
        $this->errors = array();
    }

    /**
     * Prepare failed message
     *
     * @return string
     */
    protected function getFailMessage()
    {
        $failMessage = '';
        foreach ($this->errors as $class => $dependencies) {
            $failMessage .= $class . ' depends for non-library ' . (count($dependencies) > 1 ? 'classes ' : 'class ');
            foreach ($dependencies as $dependency) {
                $failMessage .= $dependency . ' ';
            }
            $failMessage = trim($failMessage) . PHP_EOL;
        }
        return $failMessage;
    }

    /**
     * Contains all library files
     *
     * @return array
     */
    public function libraryDataProvider()
    {
        // @TODO: remove this code when class Magento\Framework\Data\Collection will fixed
        include_once BP . '/lib/internal/Magento/Framework/Option/ArrayInterface.php';
        $blackList = file(__DIR__ . '/_files/blacklist.txt', FILE_IGNORE_NEW_LINES);
        $dataProvider = Files::init()->getClassFiles(false, false, false, true, true);

        foreach ($dataProvider as $key => $data) {
            $file = str_replace(BP . '/', '', $data[0]);
            if (in_array($file, $blackList)) {
                unset($dataProvider[$key]);
            } else {
                include_once $data[0];
            }
        }
        return $dataProvider;
    }
}
