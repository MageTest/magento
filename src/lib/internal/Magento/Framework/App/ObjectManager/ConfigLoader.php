<?php
/**
 * ObjectManager configuration loader
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
namespace Magento\Framework\App\ObjectManager;

class ConfigLoader
{
    /**
     * Config reader
     *
     * @var \Magento\Framework\ObjectManager\Config\Reader\Dom
     */
    protected $_reader;

    /**
     * Config reader factory
     *
     * @var \Magento\Framework\ObjectManager\Config\Reader\DomFactory
     */
    protected $_readerFactory;

    /**
     * Cache
     *
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    protected $_cache;

    /**
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param \Magento\Framework\ObjectManager\Config\Reader\DomFactory $readerFactory
     */
    public function __construct(
        \Magento\Framework\Config\CacheInterface $cache,
        \Magento\Framework\ObjectManager\Config\Reader\DomFactory $readerFactory
    ) {
        $this->_cache = $cache;
        $this->_readerFactory = $readerFactory;
    }

    /**
     * Get reader instance
     *
     * @return \Magento\Framework\ObjectManager\Config\Reader\Dom
     */
    protected function _getReader()
    {
        if (empty($this->_reader)) {
            $this->_reader = $this->_readerFactory->create();
        }
        return $this->_reader;
    }

    /**
     * Load modules DI configuration
     *
     * @param string $area
     * @return array
     */
    public function load($area)
    {
        $cacheId = $area . '::DiConfig';
        $data = $this->_cache->load($cacheId);

        if (!$data) {
            $data = $this->_getReader()->read($area);
            $this->_cache->save(serialize($data), $cacheId);
        } else {
            $data = unserialize($data);
        }

        return $data;
    }
}
