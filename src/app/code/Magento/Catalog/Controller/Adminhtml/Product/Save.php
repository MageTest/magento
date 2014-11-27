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
namespace Magento\Catalog\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Catalog\Controller\Adminhtml\Product;

class Save extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /**
     * @var Initialization\Helper
     */
    protected $initializationHelper;

    /**
     * @var \Magento\Catalog\Model\Product\Copier
     */
    protected $productCopier;

    /**
     * @var \Magento\Catalog\Model\Product\TypeTransitionManager
     */
    protected $productTypeManager;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @param Action\Context $context
     * @param Builder $productBuilder
     * @param Initialization\Helper $initializationHelper
     * @param \Magento\Catalog\Model\Product\Copier $productCopier
     * @param \Magento\Catalog\Model\Product\TypeTransitionManager $productTypeManager
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Product\Builder $productBuilder,
        Initialization\Helper $initializationHelper,
        \Magento\Catalog\Model\Product\Copier $productCopier,
        \Magento\Catalog\Model\Product\TypeTransitionManager $productTypeManager,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
    ) {
        $this->initializationHelper = $initializationHelper;
        $this->productCopier = $productCopier;
        $this->productTypeManager = $productTypeManager;
        parent::__construct($context, $productBuilder);
        $this->resultRedirectFactory = $resultRedirectFactory;
    }


    /**
     * Save product action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $storeId = $this->getRequest()->getParam('store');
        $redirectBack = $this->getRequest()->getParam('back', false);
        $productId = $this->getRequest()->getParam('id');

        $data = $this->getRequest()->getPost();
        if ($data) {
            try {
                $product = $this->initializationHelper->initialize($this->productBuilder->build($this->getRequest()));
                $this->productTypeManager->processProduct($product);

                if (isset($data['product'][$product->getIdFieldName()])) {
                    throw new \Magento\Framework\Model\Exception(__('Unable to save product'));
                }

                $originalSku = $product->getSku();
                $product->save();
                $productId = $product->getId();

                /**
                 * Do copying data to stores
                 */
                if (isset($data['copy_to_stores'])) {
                    foreach ($data['copy_to_stores'] as $storeTo => $storeFrom) {
                        $this->_objectManager->create('Magento\Catalog\Model\Product')
                            ->setStoreId($storeFrom)
                            ->load($productId)
                            ->setStoreId($storeTo)
                            ->save();
                    }
                }

                $this->messageManager->addSuccess(__('You saved the product.'));
                if ($product->getSku() != $originalSku) {
                    $this->messageManager->addNotice(
                        __(
                            'SKU for product %1 has been changed to %2.',
                            $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($product->getName()),
                            $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($product->getSku())
                        )
                    );
                }

                $this->_eventManager->dispatch(
                    'controller_action_catalog_product_save_entity_after',
                    array('controller' => $this)
                );

                if ($redirectBack === 'duplicate') {
                    $newProduct = $this->productCopier->copy($product);
                    $this->messageManager->addSuccess(__('You duplicated the product.'));
                }
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_session->setProductData($data);
                $redirectBack = true;
            } catch (\Exception $e) {
                $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
                $this->messageManager->addError($e->getMessage());
                $redirectBack = true;
            }
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        if ($redirectBack === 'new') {
            $resultRedirect->setPath(
                'catalog/*/new',
                ['set' => $product->getAttributeSetId(), 'type' => $product->getTypeId()]
            );
        } elseif ($redirectBack === 'duplicate' && isset($newProduct)) {
            $resultRedirect->setPath(
                'catalog/*/edit',
                ['id' => $newProduct->getId(), 'back' => null, '_current' => true]
            );
        } elseif ($redirectBack) {
            $resultRedirect->setPath('catalog/*/edit', ['id' => $productId, '_current' => true]);
        } else {
            $resultRedirect->setPath('catalog/*/', ['store' => $storeId]);
        }
        return $resultRedirect;
    }
}
