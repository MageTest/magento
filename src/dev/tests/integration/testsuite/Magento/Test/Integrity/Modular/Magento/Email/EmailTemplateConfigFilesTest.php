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
namespace Magento\Test\Integrity\Modular\Magento\Email;

class EmailTemplateConfigFilesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test that email template configuration file matches the format
     *
     * @param string $file
     * @dataProvider fileFormatDataProvider
     */
    public function testFileFormat($file)
    {
        $schemaFile = BP . '/app/code/Magento/Email/etc/email_templates.xsd';
        $dom = new \Magento\Framework\Config\Dom(file_get_contents($file));
        $result = $dom->validate($schemaFile, $errors);
        $this->assertTrue($result, print_r($errors, true));
    }

    /**
     * @return array
     */
    public function fileFormatDataProvider()
    {
        return \Magento\TestFramework\Utility\Files::init()->getConfigFiles('email_templates.xml');
    }

    /**
     * Test that email template configuration contains references to existing template files
     *
     * @param string $templateId
     * @dataProvider templateReferenceDataProvider
     */
    public function testTemplateReference($templateId)
    {
        /** @var \Magento\Email\Model\Template\Config $emailConfig */
        $emailConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Email\Model\Template\Config'
        );
        $templateFilename = $emailConfig->getTemplateFilename($templateId);
        $this->assertFileExists($templateFilename, 'Email template file, specified in the configuration, must exist');
    }

    /**
     * @return array
     */
    public function templateReferenceDataProvider()
    {
        $data = array();
        /** @var \Magento\Email\Model\Template\Config $emailConfig */
        $emailConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Email\Model\Template\Config'
        );
        foreach ($emailConfig->getAvailableTemplates() as $templateId) {
            $data[$templateId] = array($templateId);
        }
        return $data;
    }

    /**
     * Test that merged configuration of email templates matches the format
     */
    public function testMergedFormat()
    {
        $validationState = $this->getMock('Magento\Framework\Config\ValidationStateInterface');
        $validationState->expects($this->any())->method('isValidated')->will($this->returnValue(true));
        /** @var \Magento\Email\Model\Template\Config\Reader $reader */
        $reader = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Email\Model\Template\Config\Reader',
            array('validationState' => $validationState)
        );
        try {
            $reader->read();
        } catch (\Exception $e) {
            $this->fail('Merged email templates configuration does not pass XSD validation: ' . $e->getMessage());
        }
    }
}
