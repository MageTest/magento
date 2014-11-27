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

/**
 * One page checkout status
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Payment extends \Magento\Checkout\Block\Onepage\AbstractOnepage
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->getCheckout()->setStepData(
            'payment',
            array('label' => __('Payment Information'), 'is_show' => $this->isShow())
        );
        parent::_construct();
    }

    /**
     * Getter
     *
     * @return float
     */
    public function getQuoteBaseGrandTotal()
    {
        return (double)$this->getQuote()->getBaseGrandTotal();
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions()
    {
        $registerParam = $this->getRequest()->getParam('register');
        return array(
            'quoteBaseGrandTotal' => $this->getQuoteBaseGrandTotal(),
            'progressUrl' => $this->getUrl('checkout/onepage/progress'),
            'reviewUrl' => $this->getUrl('checkout/onepage/review'),
            'failureUrl' => $this->getUrl('checkout/cart'),
            'getAddressUrl' => $this->getUrl('checkout/onepage/getAddress') . 'address/',
            'checkout' => array(
                'suggestRegistration' => $registerParam || $registerParam === '',
                'saveUrl' => $this->getUrl('checkout/onepage/saveMethod')
            ),
            'billing' => array('saveUrl' => $this->getUrl('checkout/onepage/saveBilling')),
            'shipping' => array('saveUrl' => $this->getUrl('checkout/onepage/saveShipping')),
            'shippingMethod' => array('saveUrl' => $this->getUrl('checkout/onepage/saveShippingMethod')),
            'payment' => array(
                'defaultPaymentMethod' => $this->getChildBlock('methods')->getSelectedMethodCode(),
                'saveUrl' => $this->getUrl('checkout/onepage/savePayment')
            ),
            'review' => array(
                'saveUrl' => $this->getUrl('checkout/onepage/saveOrder'),
                'successUrl' => $this->getUrl('checkout/onepage/success')
            )
        );
    }
}
