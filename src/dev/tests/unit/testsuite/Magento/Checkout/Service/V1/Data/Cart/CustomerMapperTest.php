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
namespace Magento\Checkout\Service\V1\Data\Cart;

class CustomerMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Checkout\Service\V1\Data\Cart\CustomerMapper
     */
    protected $mapper;

    protected function setUp()
    {
        $this->mapper = new \Magento\Checkout\Service\V1\Data\Cart\CustomerMapper();
    }

    public function testMap()
    {
        $methods = ['getCustomerId', 'getCustomerEmail', 'getCustomerGroupId', 'getCustomerTaxClassId',
            'getCustomerPrefix', 'getCustomerFirstname', 'getCustomerMiddlename', 'getCustomerLastname',
            'getCustomerSuffix', 'getCustomerDob', 'getCustomerNote', 'getCustomerNoteNotify',
            'getCustomerIsGuest', 'getCustomerGender', 'getCustomerTaxvat', '__wakeUp'];
        $quoteMock = $this->getMock('Magento\Sales\Model\Quote', $methods, [], '', false);
        $expected = [
            Customer::ID => 10,
            Customer::EMAIL => 'customer@example.com',
            Customer::GROUP_ID => '4',
            Customer::TAX_CLASS_ID => 10,
            Customer::PREFIX => 'prefix_',
            Customer::FIRST_NAME => 'First Name',
            Customer::MIDDLE_NAME => 'Middle Name',
            Customer::LAST_NAME => 'Last Name',
            Customer::SUFFIX => 'suffix',
            Customer::DOB => '1/1/1989',
            Customer::NOTE => 'customer_note',
            Customer::NOTE_NOTIFY => 'note_notify',
            Customer::IS_GUEST => false,
            Customer::GENDER => 'male',
            Customer::TAXVAT => 'taxvat',
        ];
        $expectedMethods = [
            'getCustomerId' => 10,
            'getCustomerEmail' => 'customer@example.com',
            'getCustomerGroupId' => 4,
            'getCustomerTaxClassId' => 10,
            'getCustomerPrefix' => 'prefix_',
            'getCustomerFirstname' => 'First Name',
            'getCustomerMiddlename' => 'Middle Name',
            'getCustomerLastname' => 'Last Name',
            'getCustomerSuffix' => 'suffix',
            'getCustomerDob' => '1/1/1989',
            'getCustomerNote' => 'customer_note',
            'getCustomerNoteNotify' => 'note_notify',
            'getCustomerIsGuest' => false,
            'getCustomerGender' => 'male',
            'getCustomerTaxvat' => 'taxvat',
        ];
        foreach ($expectedMethods as $method => $value) {
            $quoteMock->expects($this->once())->method($method)->will($this->returnValue($value));
        }
        $this->assertEquals($expected, $this->mapper->map($quoteMock));
    }
}
