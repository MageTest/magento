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
namespace Magento\Sales\Controller\Guest;

use Magento\Framework\App\Action\Context;

class PrintInvoice extends \Magento\Sales\Controller\AbstractController\PrintInvoice
{
    /**
     * @var OrderLoader
     */
    protected $orderLoader;

    /**
     * @param Context $context
     * @param OrderViewAuthorization $orderAuthorization
     * @param \Magento\Framework\Registry $registry
     * @param OrderLoader $orderLoader
     */
    public function __construct(
        Context $context,
        OrderViewAuthorization $orderAuthorization,
        \Magento\Framework\Registry $registry,
        OrderLoader $orderLoader
    ) {
        $this->orderLoader = $orderLoader;
        parent::__construct($context, $orderAuthorization, $registry);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if (!$this->orderLoader->load($this->_request, $this->_response)) {
            return;
        }

        $invoiceId = (int)$this->getRequest()->getParam('invoice_id');
        if ($invoiceId) {
            $invoice = $this->_objectManager->create('Magento\Sales\Model\Order\Invoice')->load($invoiceId);
            $order = $invoice->getOrder();
        } else {
            $order = $this->_coreRegistry->registry('current_order');
        }

        if ($this->orderAuthorization->canView($order)) {
            if (isset($invoice)) {
                $this->_coreRegistry->register('current_invoice', $invoice);
            }
            $this->_view->loadLayout('print');
            $this->_view->renderLayout();
        } else {
            $this->_redirect('sales/guest/form');
        }
    }
}
