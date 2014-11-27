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
namespace Magento\Paypal\Controller\Standard;

use \Magento\Sales\Model\Order;

class Cancel extends \Magento\Framework\App\Action\Action
{
    /**
     * When a customer cancel payment from paypal.
     *
     * @return void
     */
    public function execute()
    {
        /** @var \Magento\Checkout\Model\Session $session */
        $session = $this->_objectManager->get('Magento\Checkout\Model\Session');
        $session->setQuoteId($session->getPaypalStandardQuoteId(true));

        if ($session->getLastRealOrderId()) {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->_objectManager->create(
                'Magento\Sales\Model\Order'
            )->loadByIncrementId(
                $session->getLastRealOrderId()
            );
            if ($order->getId()) {
                $order->cancel()->save();
            }
            $session->restoreQuote();
        }
        $this->_redirect('checkout/cart');
    }
}
