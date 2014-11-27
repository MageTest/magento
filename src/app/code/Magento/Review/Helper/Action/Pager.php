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
namespace Magento\Review\Helper\Action;

use Magento\Framework\Model\Exception;

/**
 * Action pager helper for iterating over search results
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Pager extends \Magento\Framework\App\Helper\AbstractHelper
{
    const STORAGE_PREFIX = 'search_result_ids';

    /**
     * Storage id
     *
     * @var int
     */
    protected $_storageId = null;

    /**
     * Array of items
     *
     * @var array
     */
    protected $_items = null;

    /**
     * Backend session model
     *
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendSession;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Backend\Model\Session $backendSession
     */
    public function __construct(\Magento\Framework\App\Helper\Context $context, \Magento\Backend\Model\Session $backendSession)
    {
        $this->_backendSession = $backendSession;
        parent::__construct($context);
    }

    /**
     * Set storage id
     *
     * @param int $storageId
     * @return void
     */
    public function setStorageId($storageId)
    {
        $this->_storageId = $storageId;
    }

    /**
     * Set items to storage
     *
     * @param array $items
     * @return $this
     */
    public function setItems(array $items)
    {
        $this->_items = $items;
        $this->_backendSession->setData($this->_getStorageKey(), $this->_items);

        return $this;
    }

    /**
     * Load stored items
     *
     * @return void
     */
    protected function _loadItems()
    {
        if (is_null($this->_items)) {
            $this->_items = (array)$this->_backendSession->getData($this->_getStorageKey());
        }
    }

    /**
     * Get next item id
     *
     * @param int $id
     * @return int|bool
     */
    public function getNextItemId($id)
    {
        $position = $this->_findItemPositionByValue($id);
        if ($position === false || $position == count($this->_items) - 1) {
            return false;
        }

        return $this->_items[$position + 1];
    }

    /**
     * Get previous item id
     *
     * @param int $id
     * @return int|bool
     */
    public function getPreviousItemId($id)
    {
        $position = $this->_findItemPositionByValue($id);
        if ($position === false || $position == 0) {
            return false;
        }

        return $this->_items[$position - 1];
    }

    /**
     * Return item position based on passed in value
     *
     * @param mixed $value
     * @return int|bool
     */
    protected function _findItemPositionByValue($value)
    {
        $this->_loadItems();
        return array_search($value, $this->_items);
    }

    /**
     * Get storage key
     *
     * @return string
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _getStorageKey()
    {
        if (!$this->_storageId) {
            throw new Exception(__('Storage key was not set'));
        }

        return self::STORAGE_PREFIX . $this->_storageId;
    }
}
