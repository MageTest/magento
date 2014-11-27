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
namespace Magento\Framework\Validator;

class ObjectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Validator\Object
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\Framework\Validator\Object();

        $fieldOneExactValue = new \Zend_Validate_Identical('field_one_value');
        $fieldOneExactValue->setMessage("'field_one' does not match expected value");
        $fieldOneLength = new \Zend_Validate_StringLength(array('min' => 10));

        $fieldTwoExactValue = new \Zend_Validate_Identical('field_two_value');
        $fieldTwoExactValue->setMessage("'field_two' does not match expected value");
        $fieldTwoLength = new \Zend_Validate_StringLength(array('min' => 5));

        $entityValidity = new \Zend_Validate_Callback(array($this, 'isEntityValid'));
        $entityValidity->setMessage('Entity is not valid.');

        $this->_model->addRule(
            $fieldOneLength,
            'field_one'
        )->addRule(
            $fieldOneExactValue,
            'field_one'
        )->addRule(
            $fieldTwoLength,
            'field_two'
        )->addRule(
            $fieldTwoExactValue,
            'field_two'
        )->addRule(
            $entityValidity
        );
    }

    protected function tearDown()
    {
        $this->_model = null;
    }

    /**
     * Entity validation routine to be used as a callback
     *
     * @param \Magento\Framework\Object $entity
     * @return bool
     */
    public function isEntityValid(\Magento\Framework\Object $entity)
    {
        return (bool)$entity->getData('is_valid');
    }

    public function testAddRule()
    {
        $actualResult = $this->_model->addRule(new \Zend_Validate_Identical('field_one_value'), 'field_one');
        $this->assertSame($this->_model, $actualResult, 'Methods chaining is broken.');
    }

    public function testGetMessages()
    {
        $messages = $this->_model->getMessages();
        $this->assertInternalType('array', $messages);
    }

    /**
     * @param array $inputEntityData
     * @param array $expectedErrors
     * @dataProvider validateDataProvider
     */
    public function testIsValid(array $inputEntityData, array $expectedErrors)
    {
        $entity = new \Magento\Framework\Object($inputEntityData);
        $isValid = $this->_model->isValid($entity);
        $this->assertFalse($isValid, 'Validation is expected to fail.');

        $actualMessages = $this->_model->getMessages();
        $this->assertCount(count($expectedErrors), $actualMessages, 'Number of messages does not meet expectations.');
        foreach ($expectedErrors as $errorIndex => $expectedErrorMessage) {
            /** @var $actualMessage \Magento\Framework\Message\AbstractMessage */
            $actualMessage = $actualMessages[$errorIndex];
            $this->assertEquals($expectedErrorMessage, $actualMessage);
        }
    }

    public function validateDataProvider()
    {
        return array(
            'only "field_one" is invalid' => array(
                array('field_one' => 'one_value', 'field_two' => 'field_two_value', 'is_valid' => true),
                array("'one_value' is less than 10 characters long", "'field_one' does not match expected value")
            ),
            'only "field_two" is invalid' => array(
                array('field_one' => 'field_one_value', 'field_two' => 'two_value', 'is_valid' => true),
                array("'field_two' does not match expected value")
            ),
            'entity as a whole is invalid' => array(
                array('field_one' => 'field_one_value', 'field_two' => 'field_two_value'),
                array('Entity is not valid.')
            ),
            'errors aggregation' => array(
                array('field_one' => 'one_value', 'field_two' => 'two'),
                array(
                    "'one_value' is less than 10 characters long",
                    "'field_one' does not match expected value",
                    "'two' is less than 5 characters long",
                    "'field_two' does not match expected value",
                    'Entity is not valid.'
                )
            )
        );
    }
}
