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
namespace Magento\Checkout\Service\V1\Address;

/** Quote shipping address validator service. */
class Validator
{
    /**
     * Address factory.
     *
     * @var \Magento\Sales\Model\Quote\AddressFactory
     */
    protected $quoteAddressFactory;

    /**
     * Customer factory.
     *
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * Constructs a quote shipping address validator service object.
     *
     * @param \Magento\Sales\Model\Quote\AddressFactory $quoteAddressFactory Address factory.
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory Customer factory.
     */
    public function __construct(
        \Magento\Sales\Model\Quote\AddressFactory $quoteAddressFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->customerFactory = $customerFactory;
    }

    /**
     * Validates the fields in a specified address data object.
     *
     * @param \Magento\Checkout\Service\V1\Data\Cart\Address $addressData The address data object.
     * @return bool
     * @throws \Magento\Framework\Exception\InputException The specified address belongs to another customer.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified customer ID or address ID is not valid.
     */
    public function validate($addressData)
    {
        //validate customer id
        if ($addressData->getCustomerId()) {
            $customer = $this->customerFactory->create();
            $customer->load($addressData->getCustomerId());
            if (!$customer->getId()) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(
                    'Invalid customer id ' . $addressData->getCustomerId()
                );
            }
        }

        // validate address id
        if ($addressData->getId()) {
            $address = $this->quoteAddressFactory->create();
            $address->load($addressData->getId());
            if (!$address->getId()) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(
                    'Invalid address id ' . $addressData->getId()
                );
            }

            // check correspondence between customer id and address id
            if ($addressData->getCustomerId()) {
                if ($address->getCustomerId() != $addressData->getCustomerId()) {
                    throw new \Magento\Framework\Exception\InputException(
                        'Address with id ' . $addressData->getId() . ' belongs to another customer'
                    );
                }
            }
        }
        return true;
    }
}
