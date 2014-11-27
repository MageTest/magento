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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\Api\Code\Generator;

use Magento\Framework\Code\Generator\Io;
use Magento\TestFramework\Helper\ObjectManager;

/**
 * Class BuilderTest
 */
class DataBuilderTest extends EntityChildTestAbstract
{
    /*
     * The test is based on assumption that the classes will be injecting "DataBuilder" as dependency which will
     * indicate the compiler to identify and code generate based on ExtensibleSample implementations' interface
     */
    const SOURCE_CLASS_NAME = 'Magento\Framework\Api\Code\Generator\ExtensibleSample';
    const RESULT_CLASS_NAME = 'Magento\Framework\Api\Code\Generator\ExtensibleSampleDataBuilder';
    const GENERATOR_CLASS_NAME = 'Magento\Framework\Api\Code\Generator\DataBuilder';
    const OUTPUT_FILE_NAME = 'ExtensibleSampleDataBuilder.php';

    protected function getSourceClassName()
    {
        return self::SOURCE_CLASS_NAME;
    }

    protected function getResultClassName()
    {
        return self::RESULT_CLASS_NAME;
    }

    protected function getGeneratorClassName()
    {
        return self::GENERATOR_CLASS_NAME;
    }

    protected function getOutputFileName()
    {
        return self::OUTPUT_FILE_NAME;
    }

    protected function setUp()
    {
        parent::setUp();

        require_once __DIR__ . '/_files/ExtensibleSampleInterface.php';
        require_once __DIR__ . '/_files/ExtensibleSample.php';

    }

    protected function mockDefinedClassesCall()
    {
        $this->definedClassesMock->expects($this->at(0))
            ->method('classLoadable')
            ->with($this->getSourceClassName() . 'Interface')
            ->willReturn(true);
    }
}
