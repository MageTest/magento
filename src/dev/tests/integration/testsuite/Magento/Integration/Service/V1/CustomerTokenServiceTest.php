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

namespace Magento\Integration\Service\V1;

use Magento\Customer\Service\V1\CustomerAccountService;
use Magento\Framework\Exception\InputException;
use Magento\Integration\Model\Oauth\Token as TokenModel;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test class for \Magento\Integration\Service\V1\CustomerTokenService.
 */
class CustomerTokenServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $tokenService;

    /**
     * @var CustomerAccountService
     */
    private $customerAccountService;

    /**
     * @var TokenModel
     */
    private $tokenModel;

    /**
     * Setup CustomerTokenService
     */
    public function setUp()
    {
        $this->tokenService = Bootstrap::getObjectManager()->get('Magento\Integration\Service\V1\CustomerTokenService');
        $this->customerAccountService = Bootstrap::getObjectManager()->get(
            'Magento\Customer\Service\V1\CustomerAccountService'
        );
        $this->tokenModel = Bootstrap::getObjectManager()->get('Magento\Integration\Model\Oauth\Token');
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testCreateCustomerAccessToken()
    {
        $customerUserName = 'customer@example.com';
        $password = 'password';
        $accessToken = $this->tokenService->createCustomerAccessToken($customerUserName, $password);
        $customerData = $this->customerAccountService->authenticate($customerUserName, $password);
        /** @var $token TokenModel */
        $token = $this->tokenModel->loadByCustomerId($customerData->getId())->getToken();
        $this->assertEquals($accessToken, $token);
    }

    /**
     * @dataProvider validationDataProvider
     */
    public function testCreateCustomerAccessTokenEmptyOrNullCredentials($username, $password)
    {
        try {
            $this->tokenService->createCustomerAccessToken($username, $password);
        } catch (InputException $e) {
            $this->assertInputExceptionMessages($e);
        }
    }

    /**
     * @expectedException \Magento\Framework\Exception\InvalidEmailOrPasswordException
     * @expectedExceptionMessage Invalid login or password.
     */
    public function testCreateCustomerAccessTokenInvalidCustomer()
    {
        $customerUserName = 'invalid';
        $password = 'invalid';
        $this->tokenService->createCustomerAccessToken($customerUserName, $password);
    }

    /**
     * Provider to test input validation
     *
     * @return array
     */
    public function validationDataProvider()
    {
        return [
            'Check for empty credentials' => ['', ''],
            'Check for null credentials' => [null, null]
        ];
    }

    /**
     * Assert for presence of Input exception messages
     *
     * @param InputException $e
     */
    private function assertInputExceptionMessages($e)
    {
        $this->assertEquals(InputException::DEFAULT_MESSAGE, $e->getMessage());
        $errors = $e->getErrors();
        $this->assertCount(2, $errors);
        $this->assertEquals('username is a required field.', $errors[0]->getLogMessage());
        $this->assertEquals('password is a required field.', $errors[1]->getLogMessage());
    }
}
 
