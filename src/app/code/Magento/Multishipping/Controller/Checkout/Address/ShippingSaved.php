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
namespace Magento\Multishipping\Controller\Checkout\Address;

use Magento\Framework\App\Action\Context;
use Magento\Customer\Service\V1\CustomerAddressServiceInterface;

class ShippingSaved extends \Magento\Multishipping\Controller\Checkout\Address
{
    /**
     * @var CustomerAddressServiceInterface
     */
    protected $_customerAddressService;

    /**
     * Initialize dependencies.
     *
     * @param Context $context
     * @param CustomerAddressServiceInterface $customerAddressService
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        CustomerAddressServiceInterface $customerAddressService
    ) {
        $this->_customerAddressService = $customerAddressService;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        /**
         * if we create first address we need reset emd init checkout
         */
        $customerId = $this->_getCheckout()->getCustomer()->getId();
        if (count($this->_customerAddressService->getAddresses($customerId)) == 1) {
            $this->_getCheckout()->reset();
        }
        $this->_redirect('*/checkout/addresses');
    }
}
