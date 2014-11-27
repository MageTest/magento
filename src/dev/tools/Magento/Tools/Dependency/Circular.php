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
namespace Magento\Tools\Dependency;

use Magento\Framework\Data\Graph;

/**
 * Build circular dependencies by modules map
 */
class Circular
{
    /**
     * Map where the key is the vertex and the value are the adjacent vertices(dependencies) of this vertex
     *
     * @var array
     */
    protected $dependencies = array();

    /**
     * Modules circular dependencies map
     *
     * @var array
     */
    protected $circularDependencies = array();

    /**
     * Graph object
     *
     * @var \Magento\Framework\Data\Graph
     */
    protected $graph;

    /**
     * Build modules dependencies
     *
     * @param array $dependencies Key is the vertex and the value are the adjacent vertices(dependencies) of this vertex
     * @return array
     */
    public function buildCircularDependencies($dependencies)
    {
        $this->init($dependencies);

        foreach (array_keys($this->dependencies) as $vertex) {
            $this->expandDependencies($vertex);
        }

        $circulars = $this->graph->findCycle(null, false);
        foreach ($circulars as $circular) {
            array_shift($circular);
            $this->buildCircular($circular);
        }

        return $this->divideByModules($this->circularDependencies);
    }

    /**
     * Init data before building
     *
     * @param array $dependencies
     * @return void
     */
    protected function init($dependencies)
    {
        $this->dependencies = $dependencies;
        $this->circularDependencies = array();
        $this->graph = new Graph(array_keys($this->dependencies), array());
    }

    /**
     * Expand modules dependencies from chain
     *
     * @param string $vertex
     * @param array $path nesting path
     * @return void
     */
    protected function expandDependencies($vertex, $path = array())
    {
        if (!$this->dependencies[$vertex]) {
            return;
        }

        $path[] = $vertex;
        foreach ($this->dependencies[$vertex] as $dependency) {
            if (!isset($this->dependencies[$dependency])) {
                // dependency vertex is not described in basic definition
                continue;
            }
            $relations = $this->graph->getRelations();
            if (isset($relations[$vertex][$dependency])) {
                continue;
            }
            $this->graph->addRelation($vertex, $dependency);

            $searchResult = array_search($dependency, $path);

            if (false !== $searchResult) {
                $this->buildCircular(array_slice($path, $searchResult));
                break;
            } else {
                $this->expandDependencies($dependency, $path);
            }
        }
    }

    /**
     * Build all circular dependencies based on chain
     *
     * @param array $modules
     * @return void
     */
    protected function buildCircular($modules)
    {
        $path = '/' . implode('/', $modules);
        if (isset($this->circularDependencies[$path])) {
            return;
        }
        $this->circularDependencies[$path] = $modules;
        array_push($modules, array_shift($modules));
        $this->buildCircular($modules);
    }

    /**
     * Divide dependencies by modules
     *
     * @param array $circularDependencies
     * @return array
     */
    protected function divideByModules($circularDependencies)
    {
        $dependenciesByModule = array();
        foreach ($circularDependencies as $circularDependency) {
            $module = $circularDependency[0];
            array_push($circularDependency, $module);
            $dependenciesByModule[$module][] = $circularDependency;
        }

        return $dependenciesByModule;
    }
}
