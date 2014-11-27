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

namespace Magento\Customer\Controller;

use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\Helper\Bootstrap;

class AccountTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * Login the user
     *
     * @param string $customerId Customer to mark as logged in for the session
     * @return void
     */
    protected function login($customerId)
    {
        /** @var \Magento\Customer\Model\Session $session */
        $session = Bootstrap::getObjectManager()
            ->get('Magento\Customer\Model\Session');
        $session->loginById($customerId);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     */
    public function testIndexAction()
    {
        $this->login(1);
        $this->dispatch('customer/account/index');

        $body = $this->getResponse()->getBody();
        $this->assertContains('Green str, 67', $body);
    }

    public function testCreateAction()
    {
        $this->dispatch('customer/account/create');
        $body = $this->getResponse()->getBody();
        $this->assertContains('<input type="text" id="firstname"', $body);
        $this->assertContains('<input type="text" id="lastname"', $body);
        $this->assertContains('<input type="email" name="email" id="email_address"', $body);
        $this->assertContains('<input type="checkbox" name="is_subscribed"', $body);
        $this->assertContains('<input type="password" name="password" id="password"', $body);
        $this->assertContains('<input type="password" name="password_confirmation" title="Confirm Password"', $body);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testLogoutAction()
    {
        $this->login(1);
        $this->dispatch('customer/account/logout');
        $this->assertRedirect($this->stringContains('customer/account/logoutSuccess'));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testCreatepasswordAction()
    {
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = Bootstrap::getObjectManager()
            ->create('Magento\Customer\Model\Customer')->load(1);

        $token = Bootstrap::getObjectManager()->get('Magento\Framework\Math\Random')
            ->getUniqueHash();
        $customer->changeResetPasswordLinkToken($token);

        $this->getRequest()->setParam('token', $token);
        $this->getRequest()->setParam('id', $customer->getId());

        $this->dispatch('customer/account/createPassword');
        $text = $this->getResponse()->getBody();
        $this->assertTrue((bool)preg_match('/' . $token . '/m', $text));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testCreatepasswordActionInvalidToken()
    {
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = Bootstrap::getObjectManager()
            ->create('Magento\Customer\Model\Customer')->load(1);

        $token = Bootstrap::getObjectManager()->get('Magento\Framework\Math\Random')
            ->getUniqueHash();
        $customer->changeResetPasswordLinkToken($token);

        $this->getRequest()->setParam('token', 'INVALIDTOKEN');
        $this->getRequest()->setParam('id', $customer->getId());

        $this->dispatch('customer/account/createPassword');

        // should be redirected to forgotpassword page
        $response = $this->getResponse();
        $this->assertEquals(302, $response->getHttpResponseCode());
        $this->assertContains('customer/account/forgotpassword', $response->getHeader('Location')['value']);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testConfirmActionAlreadyActive()
    {
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = Bootstrap::getObjectManager()
            ->create('Magento\Customer\Model\Customer')->load(1);

        $this->getRequest()->setParam('key', 'abc');
        $this->getRequest()->setParam('id', $customer->getId());

        $this->dispatch('customer/account/confirm');
        $this->getResponse()->getBody();
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store customer/create_account/confirm 0
     */
    public function testNoConfirmCreatePostAction()
    {
        // Setting data for request
        $this->getRequest()
            ->setServer(array('REQUEST_METHOD' => 'POST'))
            ->setParam('firstname', 'firstname1')
            ->setParam('lastname', 'lastname1')
            ->setParam('company', '')
            ->setParam('email', 'test1@email.com')
            ->setParam('password', 'password')
            ->setParam('password_confirmation', 'password')
            ->setParam('telephone', '5123334444')
            ->setParam('street', array('1234 fake street', ''))
            ->setParam('city', 'Austin')
            ->setParam('region_id', 57)
            ->setParam('region', '')
            ->setParam('postcode', '78701')
            ->setParam('country_id', 'US')
            ->setParam('default_billing', '1')
            ->setParam('default_shipping', '1')
            ->setParam('is_subscribed', '0')
            ->setPost('create_address', true);

        $this->dispatch('customer/account/createPost');
        $this->assertRedirect($this->stringContains('customer/account/index/'));
        $this->assertSessionMessages(
            $this->equalTo(['Thank you for registering with Main Website Store.']),
            MessageInterface::TYPE_SUCCESS
        );
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store customer/create_account/confirm 1
     */
    public function testWithConfirmCreatePostAction()
    {
        // Setting data for request
        $email = 'test2@email.com';
        $this->getRequest()
            ->setServer(array('REQUEST_METHOD' => 'POST'))
            ->setParam('firstname', 'firstname2')
            ->setParam('lastname', 'lastname2')
            ->setParam('company', '')
            ->setParam('email', $email)
            ->setParam('password', 'password')
            ->setParam('password_confirmation', 'password')
            ->setParam('telephone', '5123334444')
            ->setParam('street', array('1234 fake street', ''))
            ->setParam('city', 'Austin')
            ->setParam('region_id', 57)
            ->setParam('region', '')
            ->setParam('postcode', '78701')
            ->setParam('country_id', 'US')
            ->setParam('default_billing', '1')
            ->setParam('default_shipping', '1')
            ->setParam('is_subscribed', '1')
            ->setPost('create_address', true);

        $this->dispatch('customer/account/createPost');
        $this->assertRedirect($this->stringContains('customer/account/index/'));
        $this->assertSessionMessages(
            $this->equalTo([
                'Account confirmation is required. Please, check your email for the confirmation link. ' .
                'To resend the confirmation email please ' .
                '<a href="http://localhost/index.php/customer/account/confirmation/email/' .
                $email . '/">click here</a>.'
            ]),
            MessageInterface::TYPE_SUCCESS
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testExistingEmailCreatePostAction()
    {
        // Setting data for request
        $this->getRequest()
            ->setServer(array('REQUEST_METHOD' => 'POST'))
            ->setParam('firstname', 'firstname')
            ->setParam('lastname', 'lastname')
            ->setParam('company', '')
            ->setParam('email', 'customer@example.com')
            ->setParam('password', 'password')
            ->setParam('password_confirmation', 'password')
            ->setParam('telephone', '5123334444')
            ->setParam('street', array('1234 fake street', ''))
            ->setParam('city', 'Austin')
            ->setParam('region_id', 57)
            ->setParam('region', '')
            ->setParam('postcode', '78701')
            ->setParam('country_id', 'US')
            ->setParam('default_billing', '1')
            ->setParam('default_shipping', '1')
            ->setParam('is_subscribed', '1')
            ->setPost('create_address', true);

        $this->dispatch('customer/account/createPost');
        $this->assertRedirect($this->stringContains('customer/account/create/'));
        $this->assertSessionMessages(
            $this->equalTo(['There is already an account with this email address. ' .
                'If you are sure that it is your email address, ' .
                '<a href="http://localhost/index.php/customer/account/forgotpassword/">click here</a>' .
                ' to get your password and access your account.']),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testOpenActionCreatePasswordAction()
    {
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = Bootstrap::getObjectManager()
            ->create('Magento\Customer\Model\Customer')->load(1);

        $token = Bootstrap::getObjectManager()->get('Magento\Framework\Math\Random')
            ->getUniqueHash();
        $customer->changeResetPasswordLinkToken($token);

        $this->getRequest()->setParam('token', $token);
        $this->getRequest()->setParam('id', $customer->getId());

        $this->dispatch('customer/account/createPassword');
        $this->assertNotEmpty($this->getResponse()->getBody());

        $headers = $this->getResponse()->getHeaders();
        $failed = false;
        foreach ($headers as $header) {
            if (preg_match('~customer\/account\/login~', $header['value'])) {
                $failed = true;
                break;
            }
        }
        $this->assertFalse($failed, 'Action is closed');
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/inactive_customer.php
     */
    public function testInactiveUserConfirmationAction()
    {
        $this->getRequest()
            ->setServer(['REQUEST_METHOD' => 'POST'])
            ->setPost(['email' => 'customer@needAconfirmation.com']);

        $this->dispatch('customer/account/confirmation');
        $this->assertRedirect($this->stringContains('customer/account/index'));
        $this->assertSessionMessages(
            $this->equalTo(['Please, check your email for confirmation key.']),
            MessageInterface::TYPE_SUCCESS
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testActiveUserConfirmationAction()
    {
        $this->getRequest()
            ->setServer(['REQUEST_METHOD' => 'POST'])
            ->setPost([
                'email' => 'customer@example.com'
            ]);

        $this->dispatch('customer/account/confirmation');
        $this->assertRedirect($this->stringContains('customer/account/index'));
        $this->assertSessionMessages(
            $this->equalTo(['This email does not require confirmation.']),
            MessageInterface::TYPE_SUCCESS
        );
    }

    public function testForgotPasswordPostAction()
    {
        $email = 'customer@example.com';

        $this->getRequest()
            ->setPost([
                'email' => $email
            ]);

        $this->dispatch('customer/account/forgotPasswordPost');
        $this->assertRedirect($this->stringContains('customer/account/'));
        $this->assertSessionMessages(
            $this->equalTo([
                "If there is an account associated with {$email} you will receive an email " .
                'with a link to reset your password.'
            ]),
            MessageInterface::TYPE_SUCCESS
        );
    }

    public function testForgotPasswordPostWithBadEmailAction()
    {
        $this->getRequest()
            ->setPost([
                'email' => 'bad@email'
            ]);

        $this->dispatch('customer/account/forgotPasswordPost');
        $this->assertRedirect($this->stringContains('customer/account/forgotpassword'));
        $this->assertSessionMessages(
            $this->equalTo(['Please correct the email address.']),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testResetPasswordPostNoTokenAction()
    {
        $this->getRequest()
            ->setParam('id', 1)
            ->setParam('token', '8ed8677e6c79e68b94e61658bd756ea5')
            ->setPost([
                'password' => 'new-password',
                'password_confirmation' => 'new-password'
            ]);

        $this->dispatch('customer/account/resetPasswordPost');
        $this->assertRedirect($this->stringContains('customer/account/'));
        $this->assertSessionMessages(
            $this->equalTo(['There was an error saving the new password.']),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_rp_token.php
     * @magentoConfigFixture customer/password/reset_link_expiration_period 10
     */
    public function testResetPasswordPostAction()
    {
        $this->getRequest()
            ->setQuery('id', 1)
            ->setQuery('token', '8ed8677e6c79e68b94e61658bd756ea5')
            ->setPost([
                'password' => 'new-password',
                'password_confirmation' => 'new-password'
            ]);

        $this->dispatch('customer/account/resetPasswordPost');
        $this->assertRedirect($this->stringContains('customer/account/login'));
        $this->assertSessionMessages(
            $this->equalTo(['Your password has been updated.']),
            MessageInterface::TYPE_SUCCESS
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testEditAction()
    {
        $this->login(1);

        $this->dispatch('customer/account/edit');

        $body = $this->getResponse()->getBody();
        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode(), $body);
        $this->assertContains('<div class="field name firstname required">', $body);
        // Verify the password check box is not checked
        $this->assertContains('<input type="checkbox" name="change_password" id="change-password" value="1" ' .
            'title="Change Password" class="checkbox"/>', $body);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testChangePasswordEditAction()
    {
        $this->login(1);

        $this->dispatch('customer/account/edit/changepass/1');

        $body = $this->getResponse()->getBody();
        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode(), $body);
        $this->assertContains('<div class="field name firstname required">', $body);
        // Verify the password check box is checked
        $this->assertContains('<input type="checkbox" name="change_password" id="change-password" value="1" ' .
            'title="Change Password" checked="checked" class="checkbox"/>', $body);
    }


    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testEditPostAction()
    {
        /** @var $customerAccountService \Magento\Customer\Service\V1\CustomerAccountServiceInterface */
        $customerAccountService = Bootstrap::getObjectManager()
            ->get('Magento\Customer\Service\V1\CustomerAccountServiceInterface');
        $customer = $customerAccountService->getCustomer(1);
        $this->assertEquals('Firstname', $customer->getFirstname());
        $this->assertEquals('Lastname', $customer->getLastname());
        $this->assertEquals('customer@example.com', $customer->getEmail());

        $this->login(1);
        $this->getRequest()
            ->setServer(['REQUEST_METHOD' => 'POST'])
            ->setPost([
                'form_key'  => $this->_objectManager->get('Magento\Framework\Data\Form\FormKey')->getFormKey(),
                'firstname' => 'John',
                'lastname'  => 'Doe',
                'email'     => 'johndoe@email.com'
            ]);

        $this->dispatch('customer/account/editPost');

        $this->assertRedirect($this->stringEndsWith('customer/account/'));
        $this->assertSessionMessages(
            $this->equalTo(['The account information has been saved.']),
            MessageInterface::TYPE_SUCCESS
        );

        $customer = $customerAccountService->getCustomer(1);
        $this->assertEquals('John', $customer->getFirstname());
        $this->assertEquals('Doe', $customer->getLastname());
        $this->assertEquals('johndoe@email.com', $customer->getEmail());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testChangePasswordEditPostAction()
    {
        /** @var $customerAccountService \Magento\Customer\Service\V1\CustomerAccountServiceInterface */
        $customerAccountService = Bootstrap::getObjectManager()
            ->get('Magento\Customer\Service\V1\CustomerAccountServiceInterface');
        $customer = $customerAccountService->getCustomer(1);
        $this->assertEquals('Firstname', $customer->getFirstname());
        $this->assertEquals('Lastname', $customer->getLastname());
        $this->assertEquals('customer@example.com', $customer->getEmail());

        /** @var $customerAccountService \Magento\Customer\Service\V1\CustomerAccountServiceInterface */
        $customerAccountService = Bootstrap::getObjectManager()
            ->get('Magento\Customer\Service\V1\CustomerAccountServiceInterface');

        $this->login(1);
        $this->getRequest()
            ->setServer(['REQUEST_METHOD' => 'POST'])
            ->setPost([
                'form_key'         => $this->_objectManager->get('Magento\Framework\Data\Form\FormKey')->getFormKey(),
                'firstname'        => 'John',
                'lastname'         => 'Doe',
                'email'            => 'johndoe@email.com',
                'change_password'  => 1,
                'current_password' => 'password',
                'password'         => 'new-password',
                'password_confirmation' => 'new-password'
            ]);

        $this->dispatch('customer/account/editPost');

        $this->assertRedirect($this->stringEndsWith('customer/account/'));
        $this->assertSessionMessages(
            $this->equalTo(['The account information has been saved.']),
            MessageInterface::TYPE_SUCCESS
        );

        $customer = $customerAccountService->getCustomer(1);
        $this->assertEquals('John', $customer->getFirstname());
        $this->assertEquals('Doe', $customer->getLastname());
        $this->assertEquals('johndoe@email.com', $customer->getEmail());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testMissingDataEditPostAction()
    {
        $this->login(1);
        $this->getRequest()
            ->setServer(['REQUEST_METHOD' => 'POST'])
            ->setPost([
                'form_key'  => $this->_objectManager->get('Magento\Framework\Data\Form\FormKey')->getFormKey(),
                'firstname' => 'John',
                'lastname'  => 'Doe',
                'email'     => 'bad-email'
            ]);

        $this->dispatch('customer/account/editPost');

        $this->assertRedirect($this->stringEndsWith('customer/account/edit/'));
        $this->assertSessionMessages(
            $this->equalTo(['Invalid input']),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testWrongPasswordEditPostAction()
    {
        $this->login(1);
        $this->getRequest()
            ->setServer(['REQUEST_METHOD' => 'POST'])
            ->setPost([
                'form_key'         => $this->_objectManager->get('Magento\Framework\Data\Form\FormKey')->getFormKey(),
                'firstname'        => 'John',
                'lastname'         => 'Doe',
                'email'            => 'johndoe@email.com',
                'change_password'  => 1,
                'current_password' => 'wrong-password',
                'password'         => 'new-password',
                'password_confirmation' => 'new-password'
            ]);

        $this->dispatch('customer/account/editPost');

        $this->assertRedirect($this->stringEndsWith('customer/account/edit/'));
        $this->assertSessionMessages(
            $this->equalTo(["Password doesn't match for this account."]),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testWrongConfirmationEditPostAction()
    {
        $this->login(1);
        $this->getRequest()
            ->setServer(['REQUEST_METHOD' => 'POST'])
            ->setPost([
                'form_key'         => $this->_objectManager->get('Magento\Framework\Data\Form\FormKey')->getFormKey(),
                'firstname'        => 'John',
                'lastname'         => 'Doe',
                'email'            => 'johndoe@email.com',
                'change_password'  => 1,
                'current_password' => 'password',
                'password'         => 'new-password',
                'password_confirmation' => 'new-password-no-match'
            ]);

        $this->dispatch('customer/account/editPost');

        $this->assertRedirect($this->stringEndsWith('customer/account/edit/'));
        $this->assertSessionMessages(
            $this->equalTo(['Confirm your new password']),
            MessageInterface::TYPE_ERROR
        );
    }
}
