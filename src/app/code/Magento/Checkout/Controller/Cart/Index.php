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
namespace Magento\Checkout\Controller\Cart;

class Index extends \Magento\Checkout\Controller\Cart
{
    /**
     * Shopping cart display action
     *
     * @return void
     */
    public function execute()
    {
        if ($this->cart->getQuote()->getItemsCount()) {
            $this->cart->init();
            $this->cart->save();

            if (!$this->cart->getQuote()->validateMinimumAmount()) {
                $currencyCode = $this->_objectManager->get(
                    'Magento\Framework\StoreManagerInterface'
                )->getStore()->getCurrentCurrencyCode();
                $minimumAmount = $this->_objectManager->get(
                    'Magento\Framework\Locale\CurrencyInterface'
                )->getCurrency(
                    $currencyCode
                )->toCurrency(
                    $this->_scopeConfig->getValue(
                        'sales/minimum_order/amount',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    )
                );

                $warning = $this->_scopeConfig->getValue(
                    'sales/minimum_order/description',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                ) ? $this->_scopeConfig->getValue(
                    'sales/minimum_order/description',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                ) : __(
                    'Minimum order amount is %1',
                    $minimumAmount
                );

                $this->messageManager->addNotice($warning);
            }
        }

        // Compose array of messages to add
        $messages = array();
        /** @var \Magento\Framework\Message\MessageInterface $message  */
        foreach ($this->cart->getQuote()->getMessages() as $message) {
            if ($message) {
                // Escape HTML entities in quote message to prevent XSS
                $message->setText($this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($message->getText()));
                $messages[] = $message;
            }
        }
        $this->messageManager->addUniqueMessages($messages);

        /**
         * if customer enteres shopping cart we should mark quote
         * as modified bc he can has checkout page in another window.
         */
        $this->_checkoutSession->setCartWasUpdated(true);

        \Magento\Framework\Profiler::start(__METHOD__ . 'cart_display');

        $this->_view->loadLayout();
        $layout = $this->_view->getLayout();
        $layout->initMessages();
        $this->_view->getPage()->getConfig()->setTitle(__('Shopping Cart'));
        $this->_view->renderLayout();
        \Magento\Framework\Profiler::stop(__METHOD__ . 'cart_display');
    }
}
