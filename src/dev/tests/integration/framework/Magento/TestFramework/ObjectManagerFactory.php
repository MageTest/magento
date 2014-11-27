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
namespace Magento\TestFramework;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\DriverPool;

/**
 * Class ObjectManagerFactory
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ObjectManagerFactory extends \Magento\Framework\App\ObjectManagerFactory
{
    /**
     * Locator class name
     *
     * @var string
     */
    protected $_locatorClassName = 'Magento\TestFramework\ObjectManager';

    /**
     * Config class name
     *
     * @var string
     */
    protected $_configClassName = 'Magento\TestFramework\ObjectManager\Config';

    /**
     * @var array
     */
    protected $_primaryConfigData = null;

    /**
     * Proxy over arguments instance, used by the application and all the DI stuff
     *
     * @var App\Arguments\Proxy
     */
    protected $appArgumentsProxy;

    /**
     * Override the parent method and return proxied instance instead, so that we can reset the actual app arguments
     * instance for all its clients at any time
     *
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param array $arguments
     * @return App\Arguments\Proxy
     * @throws \Magento\Framework\Exception
     */
    protected function createAppArguments(
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        array $arguments
    ) {
        if ($this->appArgumentsProxy) {
            // Framework constraint: this is ambiguous situation, because it is not clear what to do with older instance
            throw new \Magento\Framework\Exception('Only one creation of application arguments is supported');
        }
        $appArguments = parent::createAppArguments($directoryList, $arguments);
        $this->appArgumentsProxy = new App\Arguments\Proxy($appArguments);
        return $this->appArgumentsProxy;
    }

    /**
     * Restore locator instance
     *
     * @param ObjectManager $objectManager
     * @param DirectoryList $directoryList
     * @param array $arguments
     * @return ObjectManager
     */
    public function restore(ObjectManager $objectManager, $directoryList, array $arguments)
    {
        \Magento\TestFramework\ObjectManager::setInstance($objectManager);
        $this->directoryList = $directoryList;
        $objectManager->configure($this->_primaryConfigData);
        $objectManager->addSharedInstance($this->directoryList, 'Magento\Framework\App\Filesystem\DirectoryList');
        $objectManager->addSharedInstance($this->directoryList, 'Magento\Framework\Filesystem\DirectoryList');
        $appArguments = parent::createAppArguments($this->directoryList, $arguments);
        $this->appArgumentsProxy->setSubject($appArguments);
        $this->factory->setArguments($appArguments->get());
        $objectManager->addSharedInstance($appArguments, 'Magento\Framework\App\Arguments');

        $objectManager->get('Magento\Framework\Interception\PluginListInterface')->reset();
        $objectManager->configure(
            $objectManager->get('Magento\Framework\App\ObjectManager\ConfigLoader')->load('global')
        );

        return $objectManager;
    }

    /**
     * Load primary config
     *
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param DriverPool $driverPool
     * @param mixed $argumentMapper
     * @param string $appMode
     * @return array
     */
    protected function _loadPrimaryConfig(DirectoryList $directoryList, $driverPool, $argumentMapper, $appMode)
    {
        if (null === $this->_primaryConfigData) {
            $this->_primaryConfigData = array_replace(
                parent::_loadPrimaryConfig($directoryList, $driverPool, $argumentMapper, $appMode),
                array(
                    'default_setup' => array('type' => 'Magento\TestFramework\Db\ConnectionAdapter')
                )
            );
            $this->_primaryConfigData['preferences'] = array_replace(
                $this->_primaryConfigData['preferences'],
                [
                    'Magento\Framework\Stdlib\CookieManagerInterface' => 'Magento\TestFramework\CookieManager',
                    'Magento\Framework\ObjectManager\DynamicConfigInterface' =>
                        '\Magento\TestFramework\ObjectManager\Configurator',
                    'Magento\Framework\Stdlib\Cookie' => 'Magento\TestFramework\Cookie',
                    'Magento\Framework\App\RequestInterface' => 'Magento\TestFramework\Request',
                    'Magento\Framework\App\Request\Http' => 'Magento\TestFramework\Request',
                    'Magento\Framework\App\ResponseInterface' => 'Magento\TestFramework\Response',
                    'Magento\Framework\App\Response\Http' => 'Magento\TestFramework\Response',
                    'Magento\Framework\Interception\PluginListInterface'
                        => 'Magento\TestFramework\Interception\PluginList',
                    'Magento\Framework\Interception\ObjectManager\Config' =>
                        'Magento\TestFramework\ObjectManager\Config',
                    'Magento\Framework\View\LayoutInterface' => 'Magento\TestFramework\View\Layout',
                    'Magento\Framework\Model\Resource\Type\Db\Pdo\Mysql' =>
                        'Magento\TestFramework\Db\ConnectionAdapter',
                ]
            );
        }
        return $this->_primaryConfigData;
    }
}
