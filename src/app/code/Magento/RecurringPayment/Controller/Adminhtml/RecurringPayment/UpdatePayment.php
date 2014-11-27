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
namespace Magento\RecurringPayment\Controller\Adminhtml\RecurringPayment;

use \Magento\Framework\Model\Exception as CoreException;

class UpdatePayment extends \Magento\RecurringPayment\Controller\Adminhtml\RecurringPayment
{
    /**
     * Payment information updater action
     *
     * @return void
     */
    public function execute()
    {
        $payment = null;
        try {
            $payment = $this->_initPayment();
            $payment->fetchUpdate();
            if ($payment->hasDataChanges()) {
                $payment->save();
                $this->messageManager->addSuccess(__('You updated the payment.'));
            } else {
                $this->messageManager->addNotice(__('The payment has no changes.'));
            }
        } catch (CoreException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We could not update the payment.'));
            $this->_logger->logException($e);
        }
        if ($payment) {
            $this->_redirect('sales/*/view', array(self::PARAM_PAYMENT => $payment->getId()));
        } else {
            $this->_redirect('sales/*/');
        }
    }
}
