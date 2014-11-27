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

namespace Magento\Webapi\Model;

use Magento\Webapi\Model\Config\Reader;
use Zend\Code\Reflection\ClassReflection;
use Magento\Webapi\Model\Cache\Type as WebapiCache;

/**
 * Web API Config Model.
 *
 * This is a parent class for storing information about service configuration.
 */
class Config
{
    const CACHE_ID = 'webapi';

    /**
     * Pattern for Web API interface name.
     */
    const SERVICE_CLASS_PATTERN = '/^(.+?)\\\\(.+?)\\\\Service\\\\(V\d+)+(\\\\.+)Interface$/';

    const API_PATTERN = '/^(.+?)\\\\(.+?)\\\\Api(\\\\.+)Interface$/';

    /**
     * @var WebapiCache
     */
    protected $cache;

    /**
     * @var Reader
     */
    protected $configReader;

    /**
     * @var array
     */
    protected $services;

    /**
     * Initialize dependencies.
     *
     * @param WebapiCache $cache
     * @param Reader $configReader
     */
    public function __construct(WebapiCache $cache, Reader $configReader)
    {
        $this->cache = $cache;
        $this->configReader = $configReader;
    }

    /**
     * Return services loaded from cache if enabled or from files merged previously
     *
     * @return array
     */
    public function getServices()
    {
        if (null === $this->services) {
            $services = $this->cache->load(self::CACHE_ID);
            if ($services && is_string($services)) {
                $this->services = unserialize($services);
            } else {
                $this->services = $this->configReader->read();
                $this->cache->save(serialize($this->services), self::CACHE_ID);
            }
        }
        return $this->services;
    }
}
