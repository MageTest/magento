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
namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\Customer\Controller\RegistryConstants;

class Cart extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Handle and then get cart grid contents
     *
     * @return void
     */
    public function execute()
    {
        $this->_initCustomer();
        $websiteId = $this->getRequest()->getParam('website_id');

        // delete an item from cart
        $deleteItemId = $this->getRequest()->getPost('delete');
        if ($deleteItemId) {
            /** @var \Magento\Sales\Model\QuoteRepository $quoteRepository */
            $quoteRepository = $this->_objectManager->create('Magento\Sales\Model\QuoteRepository');
            /** @var \Magento\Sales\Model\Quote $quote */
            try {
                $quote = $quoteRepository->getForCustomer(
                    $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID)
                );
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $quote = $quoteRepository->create();
            }
            $quote->setWebsite(
                $this->_objectManager->get('Magento\Framework\StoreManagerInterface')->getWebsite($websiteId)
            );
            $item = $quote->getItemById($deleteItemId);
            if ($item && $item->getId()) {
                $quote->removeItem($deleteItemId);
                $quoteRepository->save($quote->collectTotals());
            }
        }

        $this->_view->loadLayout();
        $this->_view->getLayout()->getBlock('admin.customer.view.edit.cart')->setWebsiteId($websiteId);
        $this->_view->renderLayout();
    }
}
