<?php
/**
 *
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

namespace Magento\Customer\Api;

/**
 * Interface for managing customers accounts.
 */
interface AccountManagementInterface
{
    /**
     * Create customer account. Perform necessary business operations like sending email.
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param string $password
     * @param string $redirectUrl
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createAccount(
        \Magento\Customer\Api\Data\CustomerInterface $customer,
        $password = null,
        $redirectUrl = ''
    );

    /**
     * Create customer account using provided hashed password. Should not be exposed as a webapi.
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param string $hash Password hash that we can save directly
     * @param string $redirectUrl URL fed to welcome email templates. Can be used by templates to, for example, direct
     *                            the customer to a product they were looking at after pressing confirmation link.
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\InputException If bad input is provided
     * @throws \Magento\Framework\Exception\State\InputMismatchException If the provided email is already used
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createAccountWithPasswordHash(
        \Magento\Customer\Api\Data\CustomerInterface $customer,
        $hash,
        $redirectUrl = ''
    );

    /**
     * Validate customer data.
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return \Magento\Customer\Api\Data\ValidationResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validate(\Magento\Customer\Api\Data\CustomerInterface $customer);

    /**
     * Check if customer can be deleted.
     *
     * @param int $customerId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException If group is not found
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isReadonly($customerId);

    /**
     * Activate a customer account using a key that was sent in a confirmation e-mail.
     *
     * @param string $email
     * @param string $confirmationKey
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function activate($email, $confirmationKey);

    /**
     * Authenticate a customer by username and password
     *
     * @param string $email
     * @param string $password
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function authenticate($email, $password);

    /**
     * Change customer password.
     *
     * @param string $email
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function changePassword($email, $currentPassword, $newPassword);

    /**
     * Send an email to the customer with a password reset link.
     *
     * @param string $email
     * @param string $template
     * @param int $websiteId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function initiatePasswordReset($email, $template, $websiteId = null);

    /**
     * Reset customer password.
     *
     * @param string $email
     * @param string $resetToken
     * @param string $newPassword
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function resetPassword($email, $resetToken, $newPassword);

    /**
     * Check if password reset token is valid.
     *
     * @param int $customerId
     * @param string $resetPasswordLinkToken
     * @return bool True if the token is valid
     * @throws \Magento\Framework\Exception\State\InputMismatchException If token is mismatched
     * @throws \Magento\Framework\Exception\State\ExpiredException If token is expired
     * @throws \Magento\Framework\Exception\InputException If token or customer id is invalid
     * @throws \Magento\Framework\Exception\NoSuchEntityException If customer doesn't exist
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validateResetPasswordLinkToken($customerId, $resetPasswordLinkToken);

    /**
     * Gets the account confirmation status.
     *
     * @param int $customerId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getConfirmationStatus($customerId);

    /**
     * Resend confirmation email.
     *
     * @param string $email
     * @param int $websiteId
     * @param string $redirectUrl
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function resendConfirmation($email, $websiteId, $redirectUrl = '');

    /**
     * Check if given email is associated with a customer account in given website.
     *
     * @param string $customerEmail
     * @param int $websiteId If not set, will use the current websiteId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isEmailAvailable($customerEmail, $websiteId = null);

    /**
     * Check store availability for customer given the customerId.
     *
     * @param int $customerWebsiteId
     * @param int $storeId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isCustomerInStore($customerWebsiteId, $storeId);

    /**
     * Retrieve default billing address for the given customerId.
     *
     * @param int $customerId
     * @return \Magento\Customer\Api\Data\AddressInterface|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException If the customer Id is invalid
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDefaultBillingAddress($customerId);

    /**
     * Retrieve default shipping address for the given customerId.
     *
     * @param int $customerId
     * @return \Magento\Customer\Api\Data\AddressInterface|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException If the customer Id is invalid
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDefaultShippingAddress($customerId);
}
