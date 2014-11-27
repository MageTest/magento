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

namespace Magento\Checkout\Service\V1\Address;

use \Magento\Checkout\Service\V1\Data\Cart\Address;
use \Magento\Checkout\Service\V1\Data\Cart\Address\Region;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Checkout\Service\V1\Address\Validator
     */
    protected $model;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteAddressMock;

    /**
     * @var \Magento\Checkout\Service\V1\Data\Cart\AddressBuilder
     */
    protected $addressDataBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerMock;

    public function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->addressFactoryMock = $this->getMock(
            '\Magento\Sales\Model\Quote\AddressFactory', ['create', '__wakeup'], [], '', false
        );
        $this->quoteAddressMock = $this->getMock(
            '\Magento\Sales\Model\Quote\Address',
            ['getCustomerId', 'load', 'getId', '__wakeup'],
            [],
            '',
            false
        );
        $this->customerFactoryMock = $this->getMock(
            '\Magento\Customer\Model\CustomerFactory', ['create', '__wakeup'], [], '', false)
        ;
        $this->customerMock = $this->getMock('\Magento\Customer\Model\Customer', [], [], '', false);

        $builder = $this->getMock(
            '\Magento\Checkout\Service\V1\Data\Cart\Address\RegionBuilder', ['create'], [], '', false
        );

        $this->addressDataBuilder = $this->objectManager->getObject(
            '\Magento\Checkout\Service\V1\Data\Cart\AddressBuilder',
            ['regionBuilder' => $builder]
        );

        $this->model = $this->objectManager->getObject(
            '\Magento\Checkout\Service\V1\Address\Validator',
            [
                'quoteAddressFactory' => $this->addressFactoryMock,
                'customerFactory' => $this->customerFactoryMock,
            ]
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Invalid customer id 100
     */
    public function testValidateInvalidCustomer()
    {
        $customerId = 100;

        $this->customerFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->customerMock));

        $this->customerMock->expects($this->once())->method('load')->with($customerId);
        $this->customerMock->expects($this->once())->method('getId')->will($this->returnValue(null));

        $addressData = $this->addressDataBuilder
            ->setCustomerId($customerId)
            ->setCompany('eBay Inc')
            ->create();
        $this->model->validate($addressData);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Invalid address id 101
     */
    public function testValidateInvalidAddress()
    {
        $this->customerFactoryMock->expects($this->never())->method('create');
        $this->customerMock->expects($this->never())->method('load');

        $this->addressFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($this->quoteAddressMock));


        $addressData = $this->addressDataBuilder
            ->setId(101)
            ->setCompany('eBay Inc')
            ->create();
        $this->model->validate($addressData);
    }

    /**
     * Neither customer id used nor address id exists
     */
    public function testValidateNewAddress()
    {
        $this->customerFactoryMock->expects($this->never())->method('create');
        $this->addressFactoryMock->expects($this->never())->method('create');

        $addressData = $this->addressDataBuilder->setCompany('eBay Inc')->create();
        $this->assertTrue($this->model->validate($addressData));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Address with id 100 belongs to another customer
     */
    public function testValidateWithAddressOfOtherCustomer()
    {
        $addressCustomer = 100;
        $addressId = 100;

        /** Address data object */
        $addressData = $this->addressDataBuilder
            ->setId($addressId)
            ->setCompany('eBay Inc')
            ->setCustomerId($addressCustomer)
            ->create();

        /** Customer mock */
        $this->customerFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->customerMock));

        $this->customerMock->expects($this->once())->method('load')->with($addressCustomer);
        $this->customerMock->expects($this->once())->method('getId')->will($this->returnValue($addressCustomer));

        /** Quote address mock */
        $this->addressFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($this->quoteAddressMock));

        $this->quoteAddressMock->expects($this->once())->method('load')->with($addressId);
        $this->quoteAddressMock->expects($this->once())->method('getId')->will($this->returnValue($addressId));
        $this->quoteAddressMock->expects($this->any())->method('getCustomerId')
            ->will($this->returnValue(10));

        /** Validate */
        $this->model->validate($addressData);
    }


    public function testValidateWithValidAddress()
    {
        $addressCustomer = 100;
        $addressId = 100;

        /** Address data object */
        $addressData = $this->addressDataBuilder
            ->setId($addressId)
            ->setCompany('eBay Inc')
            ->setCustomerId($addressCustomer)
            ->create();

        /** Customer mock */
        $this->customerFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->customerMock));

        $this->customerMock->expects($this->once())->method('load')->with($addressCustomer);
        $this->customerMock->expects($this->once())->method('getId')->will($this->returnValue($addressCustomer));

        /** Quote address mock */
        $this->addressFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($this->quoteAddressMock));

        $this->quoteAddressMock->expects($this->once())->method('load')->with($addressId);
        $this->quoteAddressMock->expects($this->once())->method('getId')->will($this->returnValue($addressId));
        $this->quoteAddressMock->expects($this->any())->method('getCustomerId')
            ->will($this->returnValue($addressCustomer));

        /** Validate */
        $this->model->validate($addressData);
    }
}
