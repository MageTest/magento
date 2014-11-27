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
namespace Magento\Cms\Controller\Adminhtml\Wysiwyg;

class Directive extends \Magento\Backend\App\Action
{
    /**
     * Template directives callback
     *
     * @todo: move this to some model
     *
     * @return void
     */
    public function execute()
    {
        $directive = $this->getRequest()->getParam('___directive');
        $directive = $this->_objectManager->get('Magento\Core\Helper\Data')->urlDecode($directive);
        $url = $this->_objectManager->create('Magento\Email\Model\Template\Filter')->filter($directive);
        /** @var \Magento\Framework\Image\Adapter\AdapterInterface $image */
        $image = $this->_objectManager->get('Magento\Framework\Image\AdapterFactory')->create();
        $response = $this->getResponse();
        try {
            $image->open($url);
            $response->setHeader('Content-Type', $image->getMimeType())->setBody($image->getImage());
        } catch (\Exception $e) {
            $image->open($this->_objectManager->get('Magento\Cms\Model\Wysiwyg\Config')->getSkinImagePlaceholderUrl());
            $response->setHeader('Content-Type', $image->getMimeType())->setBody($image->getImage());
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }
    }
}
