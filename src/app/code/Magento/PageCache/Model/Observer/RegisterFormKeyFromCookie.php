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
namespace Magento\PageCache\Model\Observer;

class RegisterFormKeyFromCookie
{
    /**
     * @var \Magento\Framework\App\PageCache\FormKey
     */
    protected $_formKey;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $_session;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * @param \Magento\Framework\Session\Generic $session
     * @param \Magento\Framework\App\PageCache\FormKey $formKey
     * @param \Magento\Framework\Escaper $escaper
     */
    public function __construct(
        \Magento\Framework\App\PageCache\FormKey $formKey,
        \Magento\Framework\Session\Generic $session,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->_session = $session;
        $this->_formKey = $formKey;
        $this->_escaper = $escaper;
    }

    /**
     * Register form key in session from cookie value
     *
     * @return void
     */
    public function execute()
    {
        $formKeyFromCookie = $this->_formKey->get();
        if ($formKeyFromCookie) {
            $this->_session->setData(
                \Magento\Framework\Data\Form\FormKey::FORM_KEY,
                $this->_escaper->escapeHtml($formKeyFromCookie)
            );
        }
    }
}
