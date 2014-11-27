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
namespace Magento\GiftMessage\Model;

/**
 * Adminhtml giftmessage save model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Save extends \Magento\Framework\Object
{
    /**
     * @var bool
     */
    protected $_saved = false;

    /**
     * Gift message message
     *
     * @var \Magento\GiftMessage\Helper\Message|null
     */
    protected $_giftMessageMessage = null;

    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $_session;

    /**
     * @var \Magento\GiftMessage\Model\MessageFactory
     */
    protected $_messageFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\GiftMessage\Model\MessageFactory $messageFactory
     * @param \Magento\Backend\Model\Session\Quote $session
     * @param \Magento\GiftMessage\Helper\Message $giftMessageMessage
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\GiftMessage\Model\MessageFactory $messageFactory,
        \Magento\Backend\Model\Session\Quote $session,
        \Magento\GiftMessage\Helper\Message $giftMessageMessage
    ) {
        $this->_productFactory = $productFactory;
        $this->_messageFactory = $messageFactory;
        $this->_session = $session;
        $this->_giftMessageMessage = $giftMessageMessage;
    }

    /**
     * Save all seted giftmessages
     *
     * @return $this
     */
    public function saveAllInQuote()
    {
        $giftmessages = $this->getGiftmessages();

        if (!is_array($giftmessages)) {
            return $this;
        }

        foreach ($giftmessages as $entityId => $giftmessage) {
            $entityType = $this->getMappedType($giftmessage['type']);
            $this->_saveOne($entityId, $giftmessage, $entityType);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function getSaved()
    {
        return $this->_saved;
    }

    /**
     * @return $this
     */
    public function saveAllInOrder()
    {
        $giftMessages = $this->getGiftmessages();

        if (!is_array($giftMessages)) {
            return $this;
        }

        // types are 'quote', 'quote_item', etc
        foreach ($giftMessages as $type => $giftMessageEntities) {
            foreach ($giftMessageEntities as $entityId => $giftmessage) {
                $this->_saveOne($entityId, $giftmessage, $type);
            }
        }

        return $this;
    }

    /**
     * Save a single gift message
     *
     * @param int $entityId
     * @param array $giftmessage
     * @param string $entityType
     * @return $this
     */
    protected function _saveOne($entityId, $giftmessage, $entityType)
    {
        /* @var $giftmessageModel \Magento\GiftMessage\Model\Message */
        $giftmessageModel = $this->_messageFactory->create();

        switch ($entityType) {
            case 'quote':
                $entityModel = $this->_getQuote();
                break;

            case 'quote_item':
                $entityModel = $this->_getQuote()->getItemById($entityId);
                break;

            default:
                $entityModel = $giftmessageModel->getEntityModelByType($entityType)->load($entityId);
                break;
        }

        if (!$entityModel) {
            return $this;
        }

        if ($entityModel->getGiftMessageId()) {
            $giftmessageModel->load($entityModel->getGiftMessageId());
        }

        $giftmessageModel->addData($giftmessage);

        if ($giftmessageModel->isMessageEmpty() && $giftmessageModel->getId()) {
            // remove empty giftmessage
            $this->_deleteOne($entityModel, $giftmessageModel);
            $this->_saved = false;
        } elseif (!$giftmessageModel->isMessageEmpty()) {
            $giftmessageModel->save();
            $entityModel->setGiftMessageId($giftmessageModel->getId());
            if ($entityType != 'quote') {
                $entityModel->save();
            }
            $this->_saved = true;
        }

        return $this;
    }

    /**
     * Delete a single gift message from entity
     *
     * @param \Magento\Framework\Object $entityModel
     * @param \Magento\GiftMessage\Model\Message|null $giftmessageModel
     * @return $this
     */
    protected function _deleteOne($entityModel, $giftmessageModel = null)
    {
        if (is_null($giftmessageModel)) {
            $giftmessageModel = $this->_messageFactory->create()->load($entityModel->getGiftMessageId());
        }
        $giftmessageModel->delete();
        $entityModel->setGiftMessageId(0)->save();
        return $this;
    }

    /**
     * Set allowed quote items for gift messages
     *
     * @param array $items
     * @return $this
     */
    public function setAllowQuoteItems($items)
    {
        $this->_session->setAllowQuoteItemsGiftMessage($items);
        return $this;
    }

    /**
     * Add allowed quote item for gift messages
     *
     * @param int $item
     * @return $this
     */
    public function addAllowQuoteItem($item)
    {
        $items = $this->getAllowQuoteItems();
        if (!in_array($item, $items)) {
            $items[] = $item;
        }
        $this->setAllowQuoteItems($items);

        return $this;
    }

    /**
     * Retrieve allowed quote items for gift messages
     *
     * @return array
     */
    public function getAllowQuoteItems()
    {
        if (!is_array($this->_session->getAllowQuoteItemsGiftMessage())) {
            $this->setAllowQuoteItems(array());
        }

        return $this->_session->getAllowQuoteItemsGiftMessage();
    }

    /**
     * Retrieve allowed quote items products for gift messages
     *
     * @return array
     */
    public function getAllowQuoteItemsProducts()
    {
        $result = array();
        foreach ($this->getAllowQuoteItems() as $itemId) {
            $item = $this->_getQuote()->getItemById($itemId);
            if (!$item) {
                continue;
            }
            $result[] = $item->getProduct()->getId();
        }
        return $result;
    }

    /**
     * Checks allowed quote item for gift messages
     *
     * @param  \Magento\Framework\Object $item
     * @return bool
     */
    public function getIsAllowedQuoteItem($item)
    {
        if (!in_array($item->getId(), $this->getAllowQuoteItems())) {
            if ($item->getGiftMessageId() && $this->isGiftMessagesAvailable($item)) {
                $this->addAllowQuoteItem($item->getId());
                return true;
            }
            return false;
        }

        return true;
    }

    /**
     * Retrieve is gift message available for item (product)
     *
     * @param \Magento\Framework\Object $item
     * @return bool
     */
    public function isGiftMessagesAvailable($item)
    {
        return $this->_giftMessageMessage->getIsMessagesAvailable('item', $item, $item->getStore());
    }

    /**
     * Imports quote items for gift messages from products data
     *
     * @param mixed $products
     * @return $this
     */
    public function importAllowQuoteItemsFromProducts($products)
    {
        $allowedItems = $this->getAllowQuoteItems();
        $deleteAllowedItems = array();
        foreach ($products as $productId => $data) {
            $product = $this->_productFactory->create()->setStore($this->_session->getStore())->load($productId);
            $item = $this->_getQuote()->getItemByProduct($product);

            if (!$item) {
                continue;
            }

            if (in_array($item->getId(), $allowedItems) && !isset($data['giftmessage'])) {
                $deleteAllowedItems[] = $item->getId();
            } elseif (!in_array($item->getId(), $allowedItems) && isset($data['giftmessage'])) {
                $allowedItems[] = $item->getId();
            }
        }

        $allowedItems = array_diff($allowedItems, $deleteAllowedItems);

        $this->setAllowQuoteItems($allowedItems);
        return $this;
    }

    /**
     * @param mixed $items
     * @return $this
     */
    public function importAllowQuoteItemsFromItems($items)
    {
        $allowedItems = $this->getAllowQuoteItems();
        $deleteAllowedItems = array();
        foreach ($items as $itemId => $data) {

            $item = $this->_getQuote()->getItemById($itemId);

            if (!$item) {
                // Clean not exists items
                $deleteAllowedItems[] = $itemId;
                continue;
            }

            if (in_array($item->getId(), $allowedItems) && !isset($data['giftmessage'])) {
                $deleteAllowedItems[] = $item->getId();
            } elseif (!in_array($item->getId(), $allowedItems) && isset($data['giftmessage'])) {
                $allowedItems[] = $item->getId();
            }
        }

        $allowedItems = array_diff($allowedItems, $deleteAllowedItems);
        $this->setAllowQuoteItems($allowedItems);
        return $this;
    }

    /**
     * Retrieve mapped type for entity
     *
     * @param string $type
     * @return string|null
     */
    protected function getMappedType($type)
    {
        $map = [
            'main' => 'quote',
            'item' => 'quote_item',
            'order' => 'order',
            'order_item' => 'order_item'
        ];

        if (isset($map[$type])) {
            return $map[$type];
        }
        return null;
    }

    /**
     * Retrieve quote object
     *
     * @return \Magento\Sales\Model\Quote
     */
    protected function _getQuote()
    {
        return $this->_session->getQuote();
    }
}
