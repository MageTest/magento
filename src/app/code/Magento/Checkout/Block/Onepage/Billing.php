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
namespace Magento\Checkout\Block\Onepage;

use Magento\Customer\Service\V1\CustomerAccountServiceInterface as CustomerAccountService;
use Magento\Customer\Service\V1\CustomerAddressServiceInterface as CustomerAddressService;
use Magento\Customer\Model\Address\Config as AddressConfig;

/**
 * One page checkout status
 */
class Billing extends \Magento\Checkout\Block\Onepage\AbstractOnepage
{
    /**
     * Sales Qoute Billing Address instance
     *
     * @var \Magento\Sales\Model\Quote\Address
     */
    protected $_address;

    /**
     * Customer Taxvat Widget block
     *
     * @var \Magento\Customer\Block\Widget\Taxvat
     */
    protected $_taxvat;

    /**
     * @var \Magento\Sales\Model\Quote\AddressFactory
     */
    protected $_addressFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $resourceSession
     * @param \Magento\Directory\Model\Resource\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Directory\Model\Resource\Region\CollectionFactory $regionCollectionFactory
     * @param CustomerAccountService $customerAccountService
     * @param CustomerAddressService $customerAddressService
     * @param AddressConfig $addressConfig
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Sales\Model\Quote\AddressFactory $addressFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $resourceSession,
        \Magento\Directory\Model\Resource\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Directory\Model\Resource\Region\CollectionFactory $regionCollectionFactory,
        CustomerAccountService $customerAccountService,
        CustomerAddressService $customerAddressService,
        AddressConfig $addressConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Sales\Model\Quote\AddressFactory $addressFactory,
        array $data = array()
    ) {
        $this->_addressFactory = $addressFactory;
        parent::__construct(
            $context,
            $coreData,
            $configCacheType,
            $customerSession,
            $resourceSession,
            $countryCollectionFactory,
            $regionCollectionFactory,
            $customerAccountService,
            $customerAddressService,
            $addressConfig,
            $httpContext,
            $data
        );
        $this->_isScopePrivate = true;
    }

    /**
     * Initialize billing address step
     *
     * @return void
     */
    protected function _construct()
    {
        $this->getCheckout()->setStepData(
            'billing',
            array('label' => __('Billing Information'), 'is_show' => $this->isShow())
        );

        if ($this->isCustomerLoggedIn()) {
            $this->getCheckout()->setStepData('billing', 'allow', true);
        }
        parent::_construct();
    }

    /**
     * @return bool
     */
    public function isUseBillingAddressForShipping()
    {
        if ($this->getQuote()->getIsVirtual() || !$this->getQuote()->getShippingAddress()->getSameAsBilling()) {
            return false;
        }
        return true;
    }

    /**
     * Return country collection
     *
     * @return \Magento\Directory\Model\Resource\Country\Collection
     */
    public function getCountries()
    {
        return $this->_countryCollectionFactory->create()->loadByStore();
    }

    /**
     * Return checkout method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->getQuote()->getCheckoutMethod();
    }

    /**
     * Return Sales Quote Address model
     *
     * @return \Magento\Sales\Model\Quote\Address
     */
    public function getAddress()
    {
        if (is_null($this->_address)) {
            if ($this->isCustomerLoggedIn()) {
                $this->_address = $this->getQuote()->getBillingAddress();
                if (!$this->_address->getFirstname()) {
                    $this->_address->setFirstname($this->getQuote()->getCustomerData()->getFirstname());
                }
                if (!$this->_address->getLastname()) {
                    $this->_address->setLastname($this->getQuote()->getCustomerData()->getLastname());
                }
            } else {
                $this->_address = $this->_addressFactory->create();
            }
        }

        return $this->_address;
    }

    /**
     * Return Customer Address First Name
     * If Sales Quote Address First Name is not defined - return Customer First Name
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->getAddress()->getFirstname();
    }

    /**
     * Return Customer Address Last Name
     * If Sales Quote Address Last Name is not defined - return Customer Last Name
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->getAddress()->getLastname();
    }

    /**
     * Check is Quote items can ship to
     *
     * @return bool
     */
    public function canShip()
    {
        return !$this->getQuote()->isVirtual();
    }

    /**
     * @return void
     */
    public function getSaveUrl()
    {
    }

    /**
     * Get Customer Taxvat Widget block
     *
     * @return \Magento\Customer\Block\Widget\Taxvat
     */
    protected function _getTaxvat()
    {
        if (!$this->_taxvat) {
            $this->_taxvat = $this->getLayout()->createBlock('Magento\Customer\Block\Widget\Taxvat');
        }

        return $this->_taxvat;
    }

    /**
     * Check whether taxvat is enabled
     *
     * @return bool
     */
    public function isTaxvatEnabled()
    {
        return $this->_getTaxvat()->isEnabled();
    }

    /**
     * @return string
     */
    public function getTaxvatHtml()
    {
        return $this->_getTaxvat()->setTaxvat(
            $this->getQuote()->getCustomerTaxvat()
        )->setFieldIdFormat(
            'billing:%s'
        )->setFieldNameFormat(
            'billing[%s]'
        )->toHtml();
    }
}
