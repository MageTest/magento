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
namespace Magento\Test\Performance\Scenario\Handler;

class JmeterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Shell|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_shell;

    /**
     * @var \Magento\TestFramework\Performance\Scenario\Handler\Jmeter|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_object;

    /**
     * @var string
     */
    protected $_scenarioFile;

    /**
     * @var \Magento\TestFramework\Performance\Scenario
     */
    protected $_scenario;

    /**
     * @var string
     */
    protected $_reportFile;

    protected function setUp()
    {
        $this->_scenarioFile = realpath(__DIR__ . '/../../_files/scenario.jmx');
        $scenarioArgs = array(
            \Magento\TestFramework\Performance\Scenario::ARG_HOST => '127.0.0.1',
            \Magento\TestFramework\Performance\Scenario::ARG_PATH => '/',
            \Magento\TestFramework\Performance\Scenario::ARG_USERS => 2,
            \Magento\TestFramework\Performance\Scenario::ARG_LOOPS => 3
        );
        $this->_scenario = new \Magento\TestFramework\Performance\Scenario(
            'Scenario',
            $this->_scenarioFile,
            $scenarioArgs,
            array(),
            array()
        );

        $this->_reportFile = realpath(__DIR__ . '/../../_files') . '/scenario.jtl';
        $this->_shell = $this->getMock('Magento\Framework\Shell', array('execute'), array(), '', false);
        $this->_object = new \Magento\TestFramework\Performance\Scenario\Handler\Jmeter($this->_shell, false);
    }

    protected function tearDown()
    {
        $this->_shell = null;
        $this->_object = null;
        $this->_scenario = null;
    }

    public function testValidateScenarioExecutable()
    {
        $object = new \Magento\TestFramework\Performance\Scenario\Handler\Jmeter($this->_shell, true);

        $this->_shell->expects($this->at(0))->method('execute')->with('jmeter --version');
        $object->run($this->_scenario);

        // validation must be performed only once
        $this->_shell->expects(
            $this->any()
        )->method(
            'execute'
        )->with(
            $this->logicalNot($this->equalTo('jmeter --version'))
        );
        $object->run($this->_scenario);
    }

    public function testRunNoReport()
    {
        $this->_shell->expects(
            $this->once()
        )->method(
            'execute'
        )->with(
            'jmeter -n -t %s %s %s %s %s',
            array($this->_scenarioFile, '-Jhost=127.0.0.1', '-Jpath=/', '-Jusers=2', '-Jloops=3')
        );
        $this->_object->run($this->_scenario);
    }

    public function testRunReport()
    {
        $this->_shell->expects(
            $this->once()
        )->method(
            'execute'
        )->with(
            'jmeter -n -t %s -l %s %s %s %s %s',
            array($this->_scenarioFile, $this->_reportFile, '-Jhost=127.0.0.1', '-Jpath=/', '-Jusers=2', '-Jloops=3')
        );
        $this->_object->run($this->_scenario, $this->_reportFile);
    }

    /**
     * @param string $scenarioFile
     * @param string $reportFile
     * @param string $expectedException
     * @param string $expectedExceptionMsg
     * @dataProvider runExceptionDataProvider
     */
    public function testRunException($scenarioFile, $reportFile, $expectedException, $expectedExceptionMsg = '')
    {
        $this->setExpectedException($expectedException, $expectedExceptionMsg);
        $scenario = new \Magento\TestFramework\Performance\Scenario(
            'Scenario',
            $scenarioFile,
            array(),
            array(),
            array()
        );
        $this->_object->run($scenario, $reportFile);
    }

    public function runExceptionDataProvider()
    {
        $fixtureDir = realpath(__DIR__ . '/../../_files');
        return array(
            'no report created' => array(
                "{$fixtureDir}/scenario_without_report.jmx",
                "{$fixtureDir}/scenario_without_report.jtl",
                'Magento\Framework\Exception',
                "Report file '{$fixtureDir}/scenario_without_report.jtl' for 'Scenario' has not been created."
            ),
            'scenario failure in report' => array(
                "{$fixtureDir}/scenario_failure.jmx",
                "{$fixtureDir}/scenario_failure.jtl",
                'Magento\TestFramework\Performance\Scenario\FailureException',
                'fixture failure message'
            ),
            'scenario error in report' => array(
                "{$fixtureDir}/scenario_error.jmx",
                "{$fixtureDir}/scenario_error.jtl",
                'Magento\TestFramework\Performance\Scenario\FailureException',
                'fixture error message'
            )
        );
    }
}
