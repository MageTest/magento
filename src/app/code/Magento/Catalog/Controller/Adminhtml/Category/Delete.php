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
namespace Magento\Catalog\Controller\Adminhtml\Category;

class Delete extends \Magento\Catalog\Controller\Adminhtml\Category
{
    /**
     * Delete category action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $categoryId = (int)$this->getRequest()->getParam('id');
        if ($categoryId) {
            try {
                $category = $this->_objectManager->create('Magento\Catalog\Model\Category')->load($categoryId);
                $this->_eventManager->dispatch('catalog_controller_category_delete', array('category' => $category));

                $this->_objectManager->get('Magento\Backend\Model\Auth\Session')->setDeletedPath($category->getPath());

                $category->delete();
                $this->messageManager->addSuccess(__('You deleted the category.'));
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('catalog/*/edit', ['_current' => true]);
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Something went wrong while trying to delete the category.'));
                return $resultRedirect->setPath('catalog/*/edit', ['_current' => true]);
            }
        }
        return $resultRedirect->setPath('catalog/*/', ['_current' => true, 'id' => null]);
    }
}
