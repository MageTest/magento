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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Setup\Mvc\Bootstrap;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Bootstrap as AppBootstrap;
use Magento\Framework\App\State;
use Magento\Framework\App\Filesystem\DirectoryList;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\Application;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Mvc\MvcEvent;
use Zend\Console\Request;
use Zend\Stdlib\RequestInterface;
use Magento\Framework\Shell\ComplexParameter;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * A listener that injects relevant Magento initialization parameters and initializes Magento\Filesystem component
 */
class InitParamListener implements ListenerAggregateInterface, FactoryInterface
{
    /**
     * A CLI parameter for injecting bootstrap variables
     */
    const BOOTSTRAP_PARAM = 'magento_init_params';

    /**
     * List of ZF event listeners
     *
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    private $listeners = array();

    /**
     * Registers itself to every command in console routes
     *
     * @param array $config
     * @return array
     */
    public static function attachToConsoleRoutes($config)
    {
        foreach ($config['console']['router']['routes'] as &$route) {
            $route['options']['route'] .= ' [--' . self::BOOTSTRAP_PARAM . '=]';
        }
        return $config;
    }

    /**
     * Adds itself to CLI usage instructions
     *
     * @return array
     */
    public static function getConsoleUsage()
    {
        $result = [''];
        $result[] = [
            '[--' . self::BOOTSTRAP_PARAM . sprintf('=%s]', escapeshellarg('<query>')),
            'Add to any command to customize Magento initialization parameters'
        ];
        $mode = State::PARAM_MODE;
        $dirs = AppBootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS;
        $examples = [
            "{$mode}=developer",
            "{$dirs}[base][path]=/var/www/example.com",
            "{$dirs}[cache][path]=/var/tmp/cache",
        ];
        $result[] = ['', sprintf('For example: %s', escapeshellarg(implode('&', $examples)))];
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents = $events->getSharedManager();
        $this->listeners[] = $sharedEvents->attach(
            'Zend\Mvc\Application',
            MvcEvent::EVENT_BOOTSTRAP,
            array($this, 'onBootstrap')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    /**
     * An event subscriber that initializes DirectoryList and Filesystem objects in ZF application bootstrap
     *
     * @param MvcEvent $e
     * @return void
     */
    public function onBootstrap(\Zend\Mvc\MvcEvent $e)
    {
        /** @var Application $application */
        $application = $e->getApplication();
        $initParams = $application->getServiceManager()->get(self::BOOTSTRAP_PARAM);
        $directoryList = $this->createDirectoryList($initParams);
        $serviceManager = $application->getServiceManager();
        $serviceManager->setService('Magento\Framework\App\Filesystem\DirectoryList', $directoryList);
        $serviceManager->setService('Magento\Framework\Filesystem', $this->createFilesystem($directoryList));

    }

    /**
     * {@inheritdoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this->extractInitParameters($serviceLocator->get('Application'));
    }

    /**
     * Collects init params configuration from multiple sources
     *
     * Each next step overwrites previous, whenever data is available, in the following order:
     * 1: ZF application config
     * 2: environment
     * 3: CLI parameters (if the application is running in CLI mode)
     *
     * @param Application $application
     * @return array
     */
    private function extractInitParameters(Application $application)
    {
        $result = [];
        $config = $application->getConfig();
        if (isset($config[self::BOOTSTRAP_PARAM])) {
            $result = $config[self::BOOTSTRAP_PARAM];
        }
        foreach ([State::PARAM_MODE, AppBootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS] as $initKey) {
            if (isset($_SERVER[$initKey])) {
                $result[$initKey] = $_SERVER[$initKey];
            }
        }
        $result = array_replace_recursive($result, $this->extractFromCli($application->getRequest()));
        return $result;
    }

    /**
     * Extracts the directory paths from a CLI request
     *
     * Uses format of a URL query
     *
     * @param RequestInterface $request
     * @return array
     */
    private function extractFromCli(RequestInterface $request)
    {
        if (!($request instanceof Request)) {
            return [];
        }
        $bootstrapParam = new ComplexParameter(self::BOOTSTRAP_PARAM);
        foreach ($request->getContent() as $paramStr) {
            $result = $bootstrapParam->getFromString($paramStr);
            if (!empty($result)) {
                return $result;
            }
        }
        return [];
    }

    /**
     * Initializes DirectoryList service
     *
     * @param array $initParams
     * @return DirectoryList
     * @throws \LogicException
     */
    public function createDirectoryList($initParams)
    {
        if (!isset($initParams[AppBootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS][DirectoryList::ROOT])) {
            throw new \LogicException('Magento root directory is not specified.');
        }
        $config = $initParams[AppBootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS];
        $rootDir = $config[DirectoryList::ROOT][DirectoryList::PATH];
        return new DirectoryList($rootDir, $config);
    }

    /**
     * Initializes Filesystem service
     *
     * @param DirectoryList $directoryList
     * @return Filesystem
     */
    public function createFilesystem(DirectoryList $directoryList)
    {
        $driverPool = new Filesystem\DriverPool;
        return new Filesystem(
            $directoryList,
            new Filesystem\Directory\ReadFactory($driverPool),
            new Filesystem\Directory\WriteFactory($driverPool)
        );
    }
}
