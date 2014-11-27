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
namespace Magento\GiftMessage\Block\Message;

use Magento\Customer\Model\Context;
use Magento\GiftMessage\Model\Message;

/**
 * Gift message inline edit form
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Inline extends \Magento\Framework\View\Element\Template
{
    /**
     * @var mixed
     */
    protected $_entity = null;

    /**
     * @var string|null
     */
    protected $_type = null;

    /**
     * @var Message|null
     */
    protected $_giftMessage = null;

    /**
     * @var string
     */
    protected $_template = 'inline.phtml';

    /**
     * Gift message message
     *
     * @var \Magento\GiftMessage\Helper\Message|null
     */
    protected $_giftMessageMessage = null;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * Checkout type. 'onepage_checkout' and 'multishipping_address' are standard types
     *
     * @var string
     */
    protected $checkoutType;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\GiftMessage\Helper\Message $giftMessageMessage
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\GiftMessage\Helper\Message $giftMessageMessage,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = array()
    ) {
        $this->_imageHelper = $imageHelper;
        $this->_giftMessageMessage = $giftMessageMessage;
        $this->_customerSession = $customerSession;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
        $this->httpContext = $httpContext;
    }

    /**
     * Set entity
     *
     * @param mixed $entity
     * @return $this
     */
    public function setEntity($entity)
    {
        $this->_entity = $entity;
        return $this;
    }

    /**
     * Get entity
     *
     * @return mixed
     */
    public function getEntity()
    {
        return $this->_entity;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->_type = $type;
        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Define checkout type
     *
     * @param $type string
     * @return $this
     */
    public function setCheckoutType($type)
    {
        $this->checkoutType = $type;
        return $this;
    }

    /**
     * Return checkout type. Typical values are 'onepage_checkout' and 'multishipping_address'
     *
     * @return string|null
     */
    public function getCheckoutType()
    {
        return $this->checkoutType;
    }

    /**
     * Check if entity has gift message
     *
     * @return bool
     */
    public function hasGiftMessage()
    {
        return $this->getEntity()->getGiftMessageId() > 0;
    }

    /**
     * Init message
     *
     * @return $this
     */
    protected function _initMessage()
    {
        $this->_giftMessage = $this->_giftMessageMessage->getGiftMessage($this->getEntity()->getGiftMessageId());
        return $this;
    }

    /**
     * Get default value for From field
     *
     * @return string
     */
    public function getDefaultFrom()
    {
        if ($this->httpContext->getValue(Context::CONTEXT_AUTH)) {
            return $this->_customerSession->getCustomer()->getName();
        } else {
            return $this->getEntity()->getBillingAddress()->getName();
        }
    }

    /**
     * Get default value for To field
     *
     * @return string
     */
    public function getDefaultTo()
    {
        if ($this->getEntity()->getShippingAddress()) {
            return $this->getEntity()->getShippingAddress()->getName();
        } else {
            return $this->getEntity()->getName();
        }
    }

    /**
     * Retrieve message
     *
     * @param mixed $entity
     * @return string
     */
    public function getMessage($entity = null)
    {
        if (is_null($this->_giftMessage)) {
            $this->_initMessage();
        }

        if ($entity) {
            if (!$entity->getGiftMessage()) {
                $entity->setGiftMessage($this->_giftMessageMessage->getGiftMessage($entity->getGiftMessageId()));
            }
            return $entity->getGiftMessage();
        }

        return $this->_giftMessage;
    }

    /**
     * Retrieve items
     *
     * @return array
     */
    public function getItems()
    {
        if (!$this->getData('items')) {
            $items = array();

            $entityItems = $this->getEntity()->getAllItems();
            $this->_eventManager->dispatch('gift_options_prepare_items', array('items' => $entityItems));

            foreach ($entityItems as $item) {
                if ($item->getParentItem()) {
                    continue;
                }
                if ($this->isItemMessagesAvailable($item) || $item->getIsGiftOptionsAvailable()) {
                    $items[] = $item;
                }
            }
            $this->setData('items', $items);
        }
        return $this->getData('items');
    }

    /**
     * Check if gift messages for separate items are allowed
     *
     * @return bool
     */
    public function isItemsAvailable()
    {
        return count($this->getItems()) > 0;
    }

    /**
     * Return items count
     *
     * @return int
     */
    public function countItems()
    {
        return count($this->getItems());
    }

    /**
     * Check if items has messages
     *
     * @return bool
     */
    public function getItemsHasMesssages()
    {
        foreach ($this->getItems() as $item) {
            if ($item->getGiftMessageId()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if entity has message
     *
     * @return bool
     */
    public function getEntityHasMessage()
    {
        return $this->getEntity()->getGiftMessageId() > 0;
    }

    /**
     * Return escaped value
     *
     * @param string $value
     * @param string $defaultValue
     * @return string
     */
    public function getEscaped($value, $defaultValue = '')
    {
        return $this->escapeHtml(trim($value) != '' ? $value : $defaultValue);
    }

    /**
     * Check availability of giftmessages on order level
     *
     * @return bool
     */
    public function isMessagesAvailable()
    {
        return $this->_giftMessageMessage->isMessagesAvailable('quote', $this->getEntity());
    }

    /**
     * Check availability of giftmessages for specified entity item
     *
     * @param \Magento\Framework\Object $item
     * @return bool
     */
    public function isItemMessagesAvailable($item)
    {
        $type = substr($this->getType(), 0, 5) == 'multi' ? 'address_item' : 'item';
        return $this->_giftMessageMessage->isMessagesAvailable($type, $item);
    }

    /**
     * Product thumbnail image url getter
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getThumbnailUrl($product)
    {
        return (string)$this->_imageHelper->init($product, 'thumbnail')->resize($this->getThumbnailSize());
    }

    /**
     * Thumbnail image size getter
     *
     * @return int
     */
    public function getThumbnailSize()
    {
        return $this->getVar('product_thumbnail_image_size', 'Magento_Catalog');
    }

    /**
     * Render HTML code referring to config settings
     *
     * @return string
     */
    protected function _toHtml()
    {
        // render HTML when messages are allowed for order or for items only
        if ($this->isItemsAvailable() || $this->isMessagesAvailable()) {
            return parent::_toHtml();
        }
        return '';
    }
}
