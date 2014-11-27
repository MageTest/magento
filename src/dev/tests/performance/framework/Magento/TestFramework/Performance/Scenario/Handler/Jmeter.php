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
 * Handler for performance testing scenarios in format of Apache JMeter
 */
namespace Magento\TestFramework\Performance\Scenario\Handler;

class Jmeter implements \Magento\TestFramework\Performance\Scenario\HandlerInterface
{
    /**
     * @var \Magento\Framework\Shell
     */
    protected $_shell;

    /**
     * @var bool
     */
    protected $_validateExecutable;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Shell $shell
     * @param bool $validateExecutable
     */
    public function __construct(\Magento\Framework\Shell $shell, $validateExecutable = true)
    {
        $this->_shell = $shell;
        $this->_validateExecutable = $validateExecutable;
    }

    /**
     * Validate whether scenario executable is available in the environment
     */
    protected function _validateScenarioExecutable()
    {
        if ($this->_validateExecutable) {
            $this->_validateExecutable = false;
            // validate only once
            $this->_shell->execute('jmeter --version');
        }
    }

    /**
     * Run scenario and optionally write results to report file
     *
     * @param \Magento\TestFramework\Performance\Scenario $scenario
     * @param string|null $reportFile Report file to write results to, NULL disables report creation
     * @throws \Magento\Framework\Exception
     * @throws \Magento\TestFramework\Performance\Scenario\FailureException
     */
    public function run(\Magento\TestFramework\Performance\Scenario $scenario, $reportFile = null)
    {
        $this->_validateScenarioExecutable();

        $cmd = $this->_buildScenarioCmd($scenario, $reportFile);
        list($scenarioCmd, $scenarioCmdArgs) = $cmd;
        $this->_shell->execute($scenarioCmd, $scenarioCmdArgs);

        if ($reportFile) {
            if (!file_exists($reportFile)) {
                throw new \Magento\Framework\Exception(
                    "Report file '{$reportFile}' for '{$scenario->getTitle()}' has not been created."
                );
            }
            $reportErrors = $this->_getReportErrors($reportFile);
            if ($reportErrors) {
                throw new \Magento\TestFramework\Performance\Scenario\FailureException(
                    $scenario,
                    implode(PHP_EOL, $reportErrors)
                );
            }
        }
    }

    /**
     * Build and return scenario execution command and arguments for it
     *
     * @param \Magento\TestFramework\Performance\Scenario $scenario
     * @param string|null $reportFile
     * @return array
     */
    protected function _buildScenarioCmd(\Magento\TestFramework\Performance\Scenario $scenario, $reportFile = null)
    {
        $command = 'jmeter -n -t %s';
        $arguments = array($scenario->getFile());
        if ($reportFile) {
            $command .= ' -l %s';
            $arguments[] = $reportFile;
        }
        foreach ($scenario->getArguments() as $key => $value) {
            $command .= ' %s';
            $arguments[] = "-J{$key}={$value}";
        }
        return array($command, $arguments);
    }

    /**
     * Retrieve error/failure messages from the report file
     * @link http://wiki.apache.org/jmeter/JtlTestLog
     *
     * @param string $reportFile
     * @return array
     */
    protected function _getReportErrors($reportFile)
    {
        $result = array();
        $reportXml = simplexml_load_file($reportFile);
        $failedAssertions = $reportXml->xpath(
            '/testResults/*/assertionResult[failure[text()="true"] or error[text()="true"]]'
        );
        if ($failedAssertions) {
            foreach ($failedAssertions as $assertionResult) {
                if (isset($assertionResult->failureMessage)) {
                    $result[] = (string)$assertionResult->failureMessage;
                }
                if (isset($assertionResult->errorMessage)) {
                    $result[] = (string)$assertionResult->errorMessage;
                }
            }
        }
        return $result;
    }
}
