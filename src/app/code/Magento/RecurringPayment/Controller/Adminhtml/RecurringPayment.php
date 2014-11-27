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
namespace Magento\RecurringPayment\Controller\Adminhtml;

use Magento\Framework\Model\Exception as CoreException;

/**
 * Recurring payments view/management controller
 *
 * TODO: implement ACL restrictions
 */
class RecurringPayment extends \Magento\Backend\App\Action
{
    /**#@+
     * Request parameter key
     */
    const PARAM_CUSTOMER_ID = 'id';

    const PARAM_PAYMENT = 'payment';

    const PARAM_ACTION = 'action';

    /**#@-*/

    /**#@+
     * Value for PARAM_ACTION request parameter
     */
    const ACTION_CANCEL = 'cancel';

    const ACTION_SUSPEND = 'suspend';

    const ACTION_ACTIVATE = 'activate';

    /**#@-*/

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\Logger
     */
    protected $_logger;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Logger $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Logger $logger
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_logger = $logger;
        parent::__construct($context);
    }

    /**
     * Load/set payment
     *
     * @return \Magento\RecurringPayment\Model\Payment
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _initPayment()
    {
        $payment = $this->_objectManager->create(
            'Magento\RecurringPayment\Model\Payment'
        )->load(
            $this->getRequest()->getParam(self::PARAM_PAYMENT)
        );
        if (!$payment->getId()) {
            throw new CoreException(__('The payment you specified does not exist.'));
        }
        $this->_coreRegistry->register('current_recurring_payment', $payment);
        return $payment;
    }
}
