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
namespace Magento\Wishlist\Controller\Index;

use Magento\Wishlist\Controller\IndexInterface;
use Magento\Framework\App\Action;
use Magento\Framework\App\Action\NotFoundException;
use Magento\Framework\App\ResponseInterface;

class Send extends Action\Action implements IndexInterface
{
    /**
     * @var \Magento\Customer\Helper\View
     */
    protected $_customerHelperView;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Wishlist\Model\Config
     */
    protected $_wishlistConfig;

    /**
     * @var \Magento\Wishlist\Controller\WishlistProviderInterface
     */
    protected $wishlistProvider;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Core\App\Action\FormKeyValidator
     */
    protected $_formKeyValidator;

    /**
     * @param Action\Context $context
     * @param \Magento\Core\App\Action\FormKeyValidator $formKeyValidator
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider
     * @param \Magento\Wishlist\Model\Config $wishlistConfig
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Customer\Helper\View $customerHelperView
     */
    public function __construct(
        Action\Context $context,
        \Magento\Core\App\Action\FormKeyValidator $formKeyValidator,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider,
        \Magento\Wishlist\Model\Config $wishlistConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Customer\Helper\View $customerHelperView
    ) {
        $this->_formKeyValidator = $formKeyValidator;
        $this->_customerSession = $customerSession;
        $this->wishlistProvider = $wishlistProvider;
        $this->_wishlistConfig = $wishlistConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->_customerHelperView = $customerHelperView;
        parent::__construct($context);
    }

    /**
     * Share wishlist
     *
     * @return ResponseInterface|void
     * @throws NotFoundException
     */
    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->_redirect('*/*/');
        }

        $wishlist = $this->wishlistProvider->getWishlist();
        if (!$wishlist) {
            throw new NotFoundException();
        }

        $sharingLimit = $this->_wishlistConfig->getSharingEmailLimit();
        $textLimit = $this->_wishlistConfig->getSharingTextLimit();
        $emailsLeft = $sharingLimit - $wishlist->getShared();
        $emails = explode(',', $this->getRequest()->getPost('emails'));
        $error = false;
        $message = (string)$this->getRequest()->getPost('message');
        if (strlen($message) > $textLimit) {
            $error = __('Message length must not exceed %1 symbols', $textLimit);
        } else {
            $message = nl2br(htmlspecialchars($message));
            if (empty($emails)) {
                $error = __('Email address can\'t be empty.');
            } else {
                if (count($emails) > $emailsLeft) {
                    $error = __('This wishlist can be shared %1 more times.', $emailsLeft);
                } else {
                    foreach ($emails as $index => $email) {
                        $email = trim($email);
                        if (!\Zend_Validate::is($email, 'EmailAddress')) {
                            $error = __('Please input a valid email address.');
                            break;
                        }
                        $emails[$index] = $email;
                    }
                }
            }
        }

        if ($error) {
            $this->messageManager->addError($error);
            $this->_objectManager->get(
                'Magento\Wishlist\Model\Session'
            )->setSharingForm(
                $this->getRequest()->getPost()
            );
            $this->_redirect('*/*/share');
            return;
        }

        $this->inlineTranslation->suspend();

        $sent = 0;

        try {
            $customer = $this->_customerSession->getCustomerDataObject();
            $customerName = $this->_customerHelperView->getCustomerName($customer);
            /*if share rss added rss feed to email template*/
            if ($this->getRequest()->getParam('rss_url')) {
                $rss_url = $this->_view->getLayout()->createBlock(
                    'Magento\Wishlist\Block\Rss\EmailLink'
                )->setWishlistId(
                    $wishlist->getId()
                )->toHtml();
                $message .= $rss_url;
            }
            $wishlistBlock = $this->_view->getLayout()->createBlock(
                'Magento\Wishlist\Block\Share\Email\Items'
            )->toHtml();

            $emails = array_unique($emails);
            $sharingCode = $wishlist->getSharingCode();

            try {
                $scopeConfig = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
                $storeManager = $this->_objectManager->get('Magento\Framework\StoreManagerInterface');
                foreach ($emails as $email) {
                    $transport = $this->_transportBuilder->setTemplateIdentifier(
                        $scopeConfig->getValue(
                            'wishlist/email/email_template',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                        )
                    )->setTemplateOptions(
                        array(
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                            'store' => $storeManager->getStore()->getStoreId()
                        )
                    )->setTemplateVars(
                        array(
                            'customer' => $customer,
                            'customerName' => $customerName,
                            'salable' => $wishlist->isSalable() ? 'yes' : '',
                            'items' => $wishlistBlock,
                            'addAllLink' => $this->_url->getUrl('*/shared/allcart', array('code' => $sharingCode)),
                            'viewOnSiteLink' => $this->_url->getUrl('*/shared/index', array('code' => $sharingCode)),
                            'message' => $message,
                            'store' => $storeManager->getStore()
                        )
                    )->setFrom(
                        $scopeConfig->getValue(
                            'wishlist/email/email_identity',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                        )
                    )->addTo(
                        $email
                    )->getTransport();

                    $transport->sendMessage();

                    $sent++;
                }
            } catch (\Exception $e) {
                $wishlist->setShared($wishlist->getShared() + $sent);
                $wishlist->save();
                throw $e;
            }
            $wishlist->setShared($wishlist->getShared() + $sent);
            $wishlist->save();

            $this->inlineTranslation->resume();

            $this->_eventManager->dispatch('wishlist_share', array('wishlist' => $wishlist));
            $this->messageManager->addSuccess(__('Your wish list has been shared.'));
            $this->_redirect('*/*', array('wishlist_id' => $wishlist->getId()));
        } catch (\Exception $e) {
            $this->inlineTranslation->resume();
            $this->messageManager->addError($e->getMessage());
            $this->_objectManager->get(
                'Magento\Wishlist\Model\Session'
            )->setSharingForm(
                $this->getRequest()->getPost()
            );
            $this->_redirect('*/*/share');
        }
    }
}
