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
namespace Magento\Review\Controller\Adminhtml\Product;

class Delete extends \Magento\Review\Controller\Adminhtml\Product
{
    /**
     * @return void
     */
    public function execute()
    {
        $reviewId = $this->getRequest()->getParam('id', false);
        try {
            $this->_reviewFactory->create()->setId($reviewId)->aggregate()->delete();

            $this->messageManager->addSuccess(__('The review has been deleted.'));
            if ($this->getRequest()->getParam('ret') == 'pending') {
                $this->getResponse()->setRedirect($this->getUrl('review/*/pending'));
            } else {
                $this->getResponse()->setRedirect($this->getUrl('review/*/'));
            }
            return;
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong  deleting this review.'));
        }

        $this->_redirect('review/*/edit/', array('id' => $reviewId));
    }
}
