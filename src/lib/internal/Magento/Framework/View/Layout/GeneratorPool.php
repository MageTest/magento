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
namespace Magento\Framework\View\Layout;

use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Page;

/**
 * Pool of generators for structural elements
 */
class GeneratorPool
{
    /**
     * @var ScheduledStructure\Helper
     */
    protected $helper;

    /**
     * @var GeneratorInterface[]
     */
    protected $generators = [];

    /**
     * Constructor
     *
     * @param ScheduledStructure\Helper $helper
     * @param array $generators
     */
    public function __construct(
        ScheduledStructure\Helper $helper,
        array $generators = null
    ) {
        $this->helper = $helper;
        $this->addGenerators($generators);
    }

    /**
     * Get generator
     *
     * @param string $type
     * @return GeneratorInterface
     * @throws \InvalidArgumentException
     */
    public function getGenerator($type)
    {
        if (!isset($this->generators[$type])) {
            throw new \InvalidArgumentException("Invalid generator type '{$type}'");
        }
        return $this->generators[$type];
    }

    /**
     * Traverse through all generators and generate all scheduled elements
     *
     * @param Reader\Context $readerContext
     * @param Generator\Context $generatorContext
     * @return $this
     */
    public function process(Reader\Context $readerContext, Generator\Context $generatorContext)
    {
        $this->buildStructure($readerContext->getScheduledStructure(), $generatorContext->getStructure());
        foreach ($this->generators as $generator) {
            $generator->process($readerContext, $generatorContext);
        }
        return $this;
    }

    /**
     * Add generators to pool
     *
     * @param GeneratorInterface[] $generators
     * @return void
     */
    protected function addGenerators(array $generators)
    {
        foreach ($generators as $generator) {
            $this->generators[$generator->getType()] = $generator;
        }
    }

    /**
     * Build structure that is based on scheduled structure
     *
     * @param ScheduledStructure $scheduledStructure
     * @param Data\Structure $structure
     * @return $this
     */
    protected function buildStructure(ScheduledStructure $scheduledStructure, Data\Structure $structure)
    {
        //Schedule all element into nested structure
        while (false === $scheduledStructure->isStructureEmpty()) {
            $this->helper->scheduleElement($scheduledStructure, $structure, key($scheduledStructure->getStructure()));
        }
        $scheduledStructure->flushPaths();
        foreach ($scheduledStructure->getListToMove() as $elementToMove) {
            $this->moveElementInStructure($scheduledStructure, $structure, $elementToMove);
        }
        foreach ($scheduledStructure->getListToRemove() as $elementToRemove) {
            $this->removeElement($scheduledStructure, $structure, $elementToRemove);
        }
        return $this;
    }

    /**
     * Remove scheduled element
     *
     * @param ScheduledStructure $scheduledStructure
     * @param Data\Structure $structure
     * @param string $elementName
     * @param bool $isChild
     * @return $this
     */
    protected function removeElement(
        ScheduledStructure $scheduledStructure,
        Data\Structure $structure,
        $elementName,
        $isChild = false
    ) {
        $elementsToRemove = array_keys($structure->getChildren($elementName));
        $scheduledStructure->unsetElement($elementName);
        foreach ($elementsToRemove as $element) {
            $this->removeElement($scheduledStructure, $structure, $element, true);
        }
        if (!$isChild) {
            $structure->unsetElement($elementName);
            $scheduledStructure->unsetElementFromListToRemove($elementName);
        }
        return $this;
    }

    /**
     * Move element in scheduled structure
     *
     * @param ScheduledStructure $scheduledStructure
     * @param Data\Structure $structure
     * @param string $element
     * @return $this
     */
    protected function moveElementInStructure(
        ScheduledStructure $scheduledStructure,
        Data\Structure $structure,
        $element
    ) {
        list($destination, $siblingName, $isAfter, $alias) = $scheduledStructure->getElementToMove($element);
        $childAlias = $structure->getChildAlias($structure->getParentId($element), $element);
        if (!$alias && false === $structure->getChildId($destination, $childAlias)) {
            $alias = $childAlias;
        }
        $structure->unsetChild($element, $alias)->setAsChild($element, $destination, $alias);
        $structure->reorderChildElement($destination, $element, $siblingName, $isAfter);
        return $this;
    }
}
