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
namespace Magento\Customer\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\StoreManagerInterface;
use Magento\Customer\Service\V1\CustomerAccountServiceInterface;
use Magento\Framework\UrlFactory;
use Magento\Framework\Exception\State\InvalidTransitionException;

class Confirmation extends \Magento\Customer\Controller\Account
{
    /** @var StoreManagerInterface */
    protected $storeManager;

    /** @var CustomerAccountServiceInterface  */
    protected $customerAccountService;

    /** @var \Magento\Framework\UrlInterface */
    protected $urlModel;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param CustomerAccountServiceInterface $customerAccountService
     * @param UrlFactory $urlFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        CustomerAccountServiceInterface $customerAccountService,
        UrlFactory $urlFactory
    ) {
        $this->storeManager = $storeManager;
        $this->customerAccountService = $customerAccountService;
        $this->urlModel = $urlFactory->create();
        parent::__construct($context, $customerSession);
    }

    /**
     * Send confirmation link to specified email
     *
     * @return void
     */
    public function execute()
    {
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }

        // try to confirm by email
        $email = $this->getRequest()->getPost('email');
        if ($email) {
            try {
                $this->customerAccountService->resendConfirmation(
                    $email,
                    $this->storeManager->getStore()->getWebsiteId()
                );
                $this->messageManager->addSuccess(__('Please, check your email for confirmation key.'));
            } catch (InvalidTransitionException $e) {
                $this->messageManager->addSuccess(__('This email does not require confirmation.'));
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Wrong email.'));
                $this->getResponse()->setRedirect(
                    $this->urlModel->getUrl('*/*/*', array('email' => $email, '_secure' => true))
                );
                return;
            }
            $this->_getSession()->setUsername($email);
            $this->getResponse()->setRedirect($this->urlModel->getUrl('*/*/index', array('_secure' => true)));
            return;
        }

        // output form
        $this->_view->loadLayout();

        $this->_view->getLayout()->getBlock(
            'accountConfirmation'
        )->setEmail(
            $this->getRequest()->getParam('email', $email)
        );

        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}
