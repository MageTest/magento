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

/**
 * Sales report admin controller
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Controller\Adminhtml\Report;

class Sales extends AbstractReport
{
    /**
     * Add report/sales breadcrumbs
     *
     * @return $this
     */
    public function _initAction()
    {
        parent::_initAction();
        $this->_addBreadcrumb(__('Sales'), __('Sales'));
        return $this;
    }

    /**
     * Determine if action is allowed for reports module
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'sales':
                return $this->_authorization->isAllowed('Magento_Reports::salesroot_sales');
                break;
            case 'tax':
                return $this->_authorization->isAllowed('Magento_Reports::tax');
                break;
            case 'shipping':
                return $this->_authorization->isAllowed('Magento_Reports::shipping');
                break;
            case 'invoiced':
                return $this->_authorization->isAllowed('Magento_Reports::invoiced');
                break;
            case 'refunded':
                return $this->_authorization->isAllowed('Magento_Reports::refunded');
                break;
            case 'coupons':
                return $this->_authorization->isAllowed('Magento_Reports::coupons');
                break;
            case 'shipping':
                return $this->_authorization->isAllowed('Magento_Reports::shipping');
                break;
            case 'bestsellers':
                return $this->_authorization->isAllowed('Magento_Reports::bestsellers');
                break;
            default:
                return $this->_authorization->isAllowed('Magento_Reports::salesroot');
                break;
        }
    }
}
