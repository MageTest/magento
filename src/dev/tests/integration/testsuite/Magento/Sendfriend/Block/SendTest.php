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
namespace Magento\Sendfriend\Block;

use Magento\TestFramework\Helper\Bootstrap;

class SendTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sendfriend\Block\Send
     */
    protected $_block;

    protected function setUp()
    {
        $this->_block = Bootstrap::getObjectManager()->create('Magento\Sendfriend\Block\Send');
    }

    /**
     * @param string $field
     * @param string $value
     * @dataProvider formDataProvider
     * @covers \Magento\Sendfriend\Block\Send::getUserName
     * @covers \Magento\Sendfriend\Block\Send::getEmail
     */
    public function testGetCustomerFieldFromFormData($field, $value)
    {
        $formData = ['sender' => [$field => $value]];
        $this->_block->setFormData($formData);
        $this->assertEquals(trim($value), $this->_callBlockMethod($field));
    }

    /**
     * @return array
     */
    public function formDataProvider()
    {
        return [
            ['name', 'Customer Form Name'],
            ['email', 'customer_form_email@example.com']
        ];
    }

    /**
     * @param string $field
     * @param string $value
     * @dataProvider customerSessionDataProvider
     * @covers \Magento\Sendfriend\Block\Send::getUserName
     * @covers \Magento\Sendfriend\Block\Send::getEmail
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetCustomerFieldFromSession($field, $value)
    {
        $logger = $this->getMock('Magento\Framework\Logger', [], [], '', false);
        /** @var $session \Magento\Customer\Model\Session */
        $session = Bootstrap::getObjectManager()->create('Magento\Customer\Model\Session', array($logger));
        /** @var \Magento\Customer\Service\V1\CustomerAccountService $service */
        $service = Bootstrap::getObjectManager()->create('Magento\Customer\Service\V1\CustomerAccountServiceInterface');
        $customer = $service->authenticate('customer@example.com', 'password');
        $session->setCustomerDataAsLoggedIn($customer);
        $this->assertEquals($value, $this->_callBlockMethod($field));
    }

    /**
     * @return array
     */
    public function customerSessionDataProvider()
    {
        return [
            ['name', 'Firstname Lastname'],
            ['email', 'customer@example.com']
        ];

    }

    /**
     * Call block method based on form field
     *
     * @param string $field
     * @return null|string
     */
    protected function _callBlockMethod($field)
    {
        switch ($field) {
            case 'name':
                return $this->_block->getUserName();
            case 'email':
                return $this->_block->getEmail();
            default:
                return null;
        }
    }
}
