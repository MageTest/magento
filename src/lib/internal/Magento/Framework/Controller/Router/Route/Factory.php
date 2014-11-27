<?php
/**
 * Router route factory.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Controller\Router\Route;

class Factory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create route instance.
     *
     * @param string $routeClass
     * @param string $route Map used to match with later submitted URL path
     * @param array $defaults Defaults for map variables with keys as variable names
     * @param array $reqs Regular expression requirements for variables (keys as variable names)
     * @param mixed $locale
     * @return \Zend_Controller_Router_Route_Interface
     * @throws \LogicException If specified route class does not implement proper interface.
     */
    public function createRoute($routeClass, $route, $defaults = array(), $reqs = array(), $locale = null)
    {
        $route = $this->_objectManager->create(
            $routeClass,
            array('route' => $route, 'defaults' => $defaults, 'regs' => $reqs, 'locale' => $locale)
        );
        if (!$route instanceof \Zend_Controller_Router_Route_Interface) {
            throw new \LogicException('Route must implement "Zend_Controller_Router_Route_Interface".');
        }
        return $route;
    }
}
