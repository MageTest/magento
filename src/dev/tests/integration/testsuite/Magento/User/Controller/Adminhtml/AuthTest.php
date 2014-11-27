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
namespace Magento\User\Controller\Adminhtml;

/**
 * Test class for \Magento\User\Controller\Adminhtml\Auth
 *
 * @magentoAppArea adminhtml
 */
class AuthTest extends \Magento\Backend\Utility\Controller
{
    /**
     * Test form existence
     * @covers \Magento\User\Controller\Adminhtml\Auth\Forgotpassword::execute
     */
    public function testFormForgotpasswordAction()
    {
        $this->dispatch('backend/admin/auth/forgotpassword');
        $expected = 'Forgot your user name or password?';
        $this->assertContains($expected, $this->getResponse()->getBody());
    }

    /**
     * Test redirection to startup page after success password recovering posting
     *
     * @covers \Magento\User\Controller\Adminhtml\Auth\Forgotpassword::execute
     */
    public function testForgotpasswordAction()
    {
        $this->getRequest()->setPost('email', 'test@test.com');
        $this->dispatch('backend/admin/auth/forgotpassword');
        $this->assertRedirect(
            $this->equalTo(
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                    'Magento\Backend\Helper\Data'
                )->getHomePageUrl()
            )
        );
    }

    /**
     * Test reset password action
     *
     * @covers \Magento\User\Controller\Adminhtml\Auth\ResetPassword::execute
     * @covers \Magento\User\Controller\Adminhtml\Auth\ResetPassword::_validateResetPasswordLinkToken
     * @magentoDataFixture Magento/User/_files/dummy_user.php
     */
    public function testResetPasswordAction()
    {
        /** @var $user \Magento\User\Model\User */
        $user = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\User\Model\User'
        )->loadByUsername(
            'dummy_username'
        );
        $this->assertNotEmpty($user->getId(), 'Broken fixture');
        $resetPasswordToken = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\User\Helper\Data'
        )->generateResetPasswordLinkToken();
        $user->changeResetPasswordLinkToken($resetPasswordToken);
        $user->save();

        $this->getRequest()->setQuery('token', $resetPasswordToken)->setQuery('id', $user->getId());
        $this->dispatch('backend/admin/auth/resetpassword');

        $this->assertEquals('adminhtml', $this->getRequest()->getRouteName());
        $this->assertEquals('auth', $this->getRequest()->getControllerName());
        $this->assertEquals('resetpassword', $this->getRequest()->getActionName());
        $this->assertTrue((bool)strpos($this->getResponse()->getBody(), $resetPasswordToken));
    }

    /**
     * @covers \Magento\User\Controller\Adminhtml\Auth\ResetPassword::execute
     * @covers \Magento\User\Controller\Adminhtml\Auth\ResetPassword::_validateResetPasswordLinkToken
     */
    public function testResetPasswordActionWithDummyToken()
    {
        $this->getRequest()->setQuery('token', 'dummy')->setQuery('id', 1);
        $this->dispatch('backend/admin/auth/resetpassword');
        $this->assertSessionMessages(
            $this->equalTo(array('Your password reset link has expired.')),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
        $this->assertRedirect();
    }

    /**
     * @dataProvider resetPasswordDataProvider
     * @covers \Magento\User\Controller\Adminhtml\Auth\ResetPasswordPost::execute
     * @covers \Magento\User\Controller\Adminhtml\Auth\ResetPasswordPost::_validateResetPasswordLinkToken
     * @magentoDataFixture Magento/User/_files/dummy_user.php
     */
    public function testResetPasswordPostAction($password, $passwordConfirmation, $isPasswordChanged)
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var $user \Magento\User\Model\User */
        $user = $objectManager->create('Magento\User\Model\User');
        $user->loadByUsername('dummy_username');
        $this->assertNotEmpty($user->getId(), 'Broken fixture');

        /** @var \Magento\User\Helper\Data $helper */
        $helper = $objectManager->get('Magento\User\Helper\Data');

        $resetPasswordToken = $helper->generateResetPasswordLinkToken();
        $user->changeResetPasswordLinkToken($resetPasswordToken);
        $user->save();
        $oldPassword = $user->getPassword();

        $this->getRequest()->setQuery(
            'token',
            $resetPasswordToken
        )->setQuery(
            'id',
            $user->getId()
        )->setPost(
            'password',
            $password
        )->setPost(
            'confirmation',
            $passwordConfirmation
        );

        $this->dispatch('backend/admin/auth/resetpasswordpost');

        /** @var \Magento\Backend\Helper\Data $backendHelper */
        $backendHelper = $objectManager->get('Magento\Backend\Helper\Data');
        if ($isPasswordChanged) {
            $this->assertRedirect($this->equalTo($backendHelper->getHomePageUrl()));
        } else {
            $this->assertRedirect(
                $this->stringContains('backend/admin/auth/resetpassword')
            );
        }

        /** @var $user \Magento\User\Model\User */
        $user = $objectManager->create('Magento\User\Model\User');
        $user->loadByUsername('dummy_username');

        if ($isPasswordChanged) {
            /** @var \Magento\Framework\Encryption\EncryptorInterface $encryptor */
            $encryptor = $objectManager->get('Magento\Framework\Encryption\EncryptorInterface');
            $this->assertTrue($encryptor->validateHash($password, $user->getPassword()));
        } else {
            $this->assertEquals($oldPassword, $user->getPassword());
        }
    }

    public function resetPasswordDataProvider()
    {
        $password = uniqid('123q');
        return array(
            array($password, $password, true),
            array($password, '', false),
            array($password, $password . '123', false),
            array('', '', false),
            array('', $password, false)
        );
    }

    /**
     * @covers \Magento\User\Controller\Adminhtml\Auth\ResetPasswordPost::execute
     * @covers \Magento\User\Controller\Adminhtml\Auth\ResetPasswordPost::_validateResetPasswordLinkToken
     * @magentoDataFixture Magento/User/_files/dummy_user.php
     */
    public function testResetPasswordPostActionWithDummyToken()
    {
        $this->getRequest()->setQuery('token', 'dummy')->setQuery('id', 1);
        $this->dispatch('backend/admin/auth/resetpasswordpost');
        $this->assertSessionMessages(
            $this->equalTo(array('Your password reset link has expired.')),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\Backend\Helper\Data $backendHelper */
        $backendHelper = $objectManager->get('Magento\Backend\Helper\Data');

        $this->assertRedirect($this->equalTo($backendHelper->getHomePageUrl()));
    }

    /**
     * @covers \Magento\User\Controller\Adminhtml\Auth\ResetPasswordPost::execute
     * @covers \Magento\User\Controller\Adminhtml\Auth\ResetPasswordPost::_validateResetPasswordLinkToken
     * @magentoDataFixture Magento/User/_files/dummy_user.php
     */
    public function testResetPasswordPostActionWithInvalidPassword()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $user = $objectManager->create('Magento\User\Model\User');
        $user->loadByUsername('dummy_username');
        $resetPasswordToken = null;
        if ($user->getId()) {
            /** @var \Magento\User\Helper\Data $userHelper */
            $userHelper = $objectManager->get('Magento\User\Helper\Data');

            $resetPasswordToken = $userHelper->generateResetPasswordLinkToken();
            $user->changeResetPasswordLinkToken($resetPasswordToken);
            $user->save();
        }

        $newDummyPassword = 'new_dummy_password2';

        $this->getRequest()->setQuery(
            'token',
            $resetPasswordToken
        )->setQuery(
            'id',
            $user->getId()
        )->setPost(
            'password',
            $newDummyPassword
        )->setPost(
            'confirmation',
            'invalid'
        );

        $this->dispatch('backend/admin/auth/resetpasswordpost');

        $this->assertSessionMessages(
            $this->equalTo(array('Your password confirmation must match your password.')),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
        $this->assertRedirect();
    }
}
