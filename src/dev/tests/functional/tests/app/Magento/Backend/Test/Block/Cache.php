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

namespace Magento\Backend\Test\Block;

use Mtf\Block\Block;
use Mtf\Factory\Factory;
use Mtf\Client\Element\Locator;

/**
 * Class Actions
 * Cache actions block
 *
 */
class Cache extends Block
{
    /**
     * 'Flush Magento Cache' button
     *
     * @var string
     */
    protected $flushMagentoCacheButton = '[data-ui-id="adminhtml-cache-container-flush-magento-button"]';

    /**
     * 'Flush Cache Storage' button
     *
     * @var string
     */
    protected $flushCacheStorageButton = '[data-ui-id="adminhtml-cache-container-flush-system-button"]';

    /**
     * Selector for messages block
     *
     * @var string
     */
    protected $messagesSelector = '//ancestor::div//div[@id="messages"]';

    /**
     * Messages texts
     *
     * @var array
     */
    protected $messagesText = array(
        'cache_storage_flushed' => 'You flushed the cache storage.',
        'cache_magento_flushed' => 'The Magento cache storage has been flushed.',
    );

    /**
     * Flush magento cache
     */
    public function flushMagentoCache()
    {
        $this->_rootElement->find($this->flushMagentoCacheButton)->click();
    }

    /**
     * Flush cache storage
     */
    public function flushCacheStorage()
    {
        $this->_rootElement->find($this->flushCacheStorageButton)->click();
        $this->_rootElement->acceptAlert();
    }

    /**
     * Is storage cache flushed successfully
     *
     * @return bool
     */
    public function isStorageCacheFlushed()
    {
        return $this->getMessagesBlock()->getSuccessMessages() == $this->messagesText['cache_storage_flushed'];
    }

    /**
     * Is magento cache flushed successfully
     *
     * @return bool
     */
    public function isMagentoCacheFlushed()
    {
        return $this->getMessagesBlock()->getSuccessMessages() == $this->messagesText['cache_magento_flushed'];
    }

    /**
     * Get messages block
     *
     * @return \Magento\Core\Test\Block\Messages
     */
    protected function getMessagesBlock()
    {
        return Factory::getBlockFactory()->getMagentoCoreMessages(
            $this->_rootElement->find($this->messagesSelector, Locator::SELECTOR_XPATH)
        );
    }
}
