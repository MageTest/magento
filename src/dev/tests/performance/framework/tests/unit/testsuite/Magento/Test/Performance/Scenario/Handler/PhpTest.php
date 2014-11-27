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

class PhpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Shell|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_shell;

    /**
     * @var \Magento\TestFramework\Performance\Scenario\Handler\Php|PHPUnit_Framework_MockObject_MockObject
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
        $this->_scenarioFile = realpath(__DIR__ . '/../../_files/scenario.php');
        $scenarioArgs = array(
            \Magento\TestFramework\Performance\Scenario::ARG_USERS => 2,
            \Magento\TestFramework\Performance\Scenario::ARG_LOOPS => 3,
            'custom' => 'custom_value'
        );
        $this->_scenario = new \Magento\TestFramework\Performance\Scenario(
            'Scenario',
            $this->_scenarioFile,
            $scenarioArgs,
            array(),
            array()
        );

        $this->_reportFile = realpath(__DIR__ . '/../../_files/scenario.jtl');
        $this->_shell = $this->getMock('Magento\Framework\Shell', array('execute'), array(), '', false);
        $this->_object = new \Magento\TestFramework\Performance\Scenario\Handler\Php($this->_shell, false);
    }

    protected function tearDown()
    {
        $this->_shell = null;
        $this->_object = null;
        $this->_scenario = null;
    }

    public function testValidateScenarioExecutable()
    {
        $object = new \Magento\TestFramework\Performance\Scenario\Handler\Php($this->_shell);

        $this->_shell->expects($this->at(0))->method('execute')->with('php --version');
        $object->run($this->_scenario);

        // validation must be performed only once
        $this->_shell->expects(
            $this->any()
        )->method(
            'execute'
        )->with(
            $this->logicalNot($this->equalTo('php --version'))
        );
        $object->run($this->_scenario);
    }

    public function testRunNoReport()
    {
        $this->_shell->expects(
            $this->exactly(3)
        )->method(
            'execute'
        )->with(
            'php -f %s -- --users %s --loops %s --custom %s',
            array($this->_scenarioFile, 2, 3, 'custom_value')
        );
        $this->_object->run($this->_scenario);
    }

    public function testRunReport()
    {
        $this->expectOutputRegex('/.+/');
        // prevent displaying output
        $this->_object->run($this->_scenario, 'php://output');
        $expectedDom = new \DOMDocument();
        $expectedDom->loadXML(
            '
            <testResults version="1.2">
            <httpSample t="100" lt="0" ts="1349212263" s="true" lb="Scenario" rc="0" rm="" tn="1" dt="text"/>
            <httpSample t="150" lt="0" ts="1349212263" s="true" lb="Scenario" rc="0" rm="" tn="2" dt="text"/>
            <httpSample t="125" lt="0" ts="1349212263" s="true" lb="Scenario" rc="0" rm="" tn="3" dt="text"/>
            </testResults>
        '
        );
        $actualDom = new \DOMDocument();
        $actualDom->loadXML($this->getActualOutput());
        $this->assertEqualXMLStructure($expectedDom->documentElement, $actualDom->documentElement, true);
    }

    /**
     * @expectedException \Magento\TestFramework\Performance\Scenario\FailureException
     * @expectedExceptionMessage command failure message
     */
    public function testRunException()
    {
        $failure = new \Magento\Framework\Exception(
            'Command returned non-zero exit code.',
            0,
            new \Exception('command failure message', 1)
        );
        $this->_shell->expects($this->any())->method('execute')->will($this->throwException($failure));
        $this->_object->run($this->_scenario);
    }
}
