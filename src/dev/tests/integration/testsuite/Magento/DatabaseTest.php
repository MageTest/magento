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
namespace Magento;

use Magento\TestFramework\Helper\Bootstrap;

class DatabaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Assure that there are no redundant indexes declared in database
     */
    public function testDuplicateKeys()
    {
        if (!defined('PERCONA_TOOLKIT_BIN_DIR')) {
            $this->markTestSkipped('Path to Percona Toolkit is not specified.');
        }
        $checkerPath = PERCONA_TOOLKIT_BIN_DIR . '/pt-duplicate-key-checker';

        $db = Bootstrap::getInstance()->getBootstrap()->getApplication()->getDbInstance();
        $command = $checkerPath . ' -d ' . $db->getSchema()
            . ' h=' . $db->getHost()['db_host'] . ',u=' . $db->getUser() . ',p=' . $db->getPassword();

        exec($command, $output, $exitCode);
        $this->assertEquals(0, $exitCode);
        $output = implode(PHP_EOL, $output);
        if (preg_match('/Total Duplicate Indexes\s+(\d+)/', $output, $matches)) {
            $this->fail($matches[1] . ' duplicate indexes found.' . PHP_EOL . PHP_EOL . $output);
        }
    }
}
