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
namespace Magento\Framework\View\Layout\ScheduledStructure;

use Magento\Framework\View\Layout;

class Helper
{
    /**#@+
     * Scheduled structure array indexes
     */
    const SCHEDULED_STRUCTURE_INDEX_TYPE = 0;
    const SCHEDULED_STRUCTURE_INDEX_ALIAS = 1;
    const SCHEDULED_STRUCTURE_INDEX_PARENT_NAME = 2;
    const SCHEDULED_STRUCTURE_INDEX_SIBLING_NAME = 3;
    const SCHEDULED_STRUCTURE_INDEX_IS_AFTER = 4;
    /**#@-*/

    /**
     * Anonymous block counter
     *
     * @var int
     */
    protected $counter = 0;

    /**
     * @var \Magento\Framework\Logger
     */
    protected $logger;

    /**
     * @param \Magento\Framework\Logger $logger
     */
    public function __construct(
        \Magento\Framework\Logger $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * Generate anonymous element name for structure
     *
     * @param string $class
     * @return string
     */
    protected function _generateAnonymousName($class)
    {
        $position = strpos($class, '\\Block\\');
        $key = $position !== false ? substr($class, $position + 7) : $class;
        $key = strtolower(trim($key, '_'));
        return $key . $this->counter++;
    }

    /**
     * Populate queue for generating structural elements
     *
     * @param Layout\ScheduledStructure $scheduledStructure
     * @param \Magento\Framework\View\Layout\Element $currentNode
     * @param \Magento\Framework\View\Layout\Element $parentNode
     * @return string
     * @see scheduleElement() where the scheduledStructure is used
     */
    public function scheduleStructure(
        Layout\ScheduledStructure $scheduledStructure,
        Layout\Element $currentNode,
        Layout\Element $parentNode
    ) {
        // if it hasn't a name it must be generated
        $path = $name = (string)$currentNode->getAttribute('name')
            ?: $this->_generateAnonymousName($parentNode->getElementName() . '_schedule_block');

        // Prepare scheduled element with default parameters [type, alias, parentName, siblingName, isAfter]
        $row = [
            self::SCHEDULED_STRUCTURE_INDEX_TYPE           => $currentNode->getName(),
            self::SCHEDULED_STRUCTURE_INDEX_ALIAS          => '',
            self::SCHEDULED_STRUCTURE_INDEX_PARENT_NAME    => '',
            self::SCHEDULED_STRUCTURE_INDEX_SIBLING_NAME   => null,
            self::SCHEDULED_STRUCTURE_INDEX_IS_AFTER       => true,
        ];

        $parentName = $parentNode->getElementName();
        //if this element has a parent element, there must be reset [alias, parentName, siblingName, isAfter]
        if ($parentName) {
            $row[self::SCHEDULED_STRUCTURE_INDEX_ALIAS] = (string)$currentNode->getAttribute('as');
            $row[self::SCHEDULED_STRUCTURE_INDEX_PARENT_NAME] = $parentName;

            list($row[self::SCHEDULED_STRUCTURE_INDEX_SIBLING_NAME],
                $row[self::SCHEDULED_STRUCTURE_INDEX_IS_AFTER]) = $this->_beforeAfterToSibling($currentNode);

            // materialized path for referencing nodes in the plain array of _scheduledStructure
            if ($scheduledStructure->hasPath($parentName)) {
                $path = $scheduledStructure->getPath($parentName) . '/' . $path;
            }
        }

        $this->_overrideElementWorkaround($scheduledStructure, $name, $path);
        $scheduledStructure->setPathElement($name, $path);
        $scheduledStructure->setStructureElement($name, $row);
        return $name;
    }

    /**
     * Destroy previous element with same name and all its children, if new element overrides it
     *
     * This is a workaround to handle situation, when an element emerges with name of element that already exists.
     * In this case we destroy entire structure of the former element and replace with the new one.
     *
     * @param Layout\ScheduledStructure $scheduledStructure
     * @param string $name
     * @param string $path
     * @return void
     */
    protected function _overrideElementWorkaround(Layout\ScheduledStructure $scheduledStructure, $name, $path)
    {
        if ($scheduledStructure->hasStructureElement($name)) {
            $scheduledStructure->setStructureElementData($name, []);
            foreach ($scheduledStructure->getPaths() as $potentialChild => $childPath) {
                if (0 === strpos($childPath, "{$path}/")) {
                    $scheduledStructure->unsetPathElement($potentialChild);
                    $scheduledStructure->unsetStructureElement($potentialChild);
                }
            }
        }
    }

    /**
     * Analyze "before" and "after" information in the node and return sibling name and whether "after" or "before"
     *
     * @param \Magento\Framework\View\Layout\Element $node
     * @return array
     */
    protected function _beforeAfterToSibling($node)
    {
        $result = array(null, true);
        if (isset($node['after'])) {
            $result[0] = (string)$node['after'];
        } elseif (isset($node['before'])) {
            $result[0] = (string)$node['before'];
            $result[1] = false;
        }
        return $result;
    }


    /**
     * Process queue of structural elements and actually add them to structure, and schedule elements for generation
     *
     * The catch is to populate parents first, if they are not in the structure yet.
     * Since layout updates could come in arbitrary order, a case is possible where an element is declared in reference,
     * while referenced element itself is not declared yet.
     *
     * @param \Magento\Framework\View\Layout\ScheduledStructure $scheduledStructure
     * @param Layout\Data\Structure $structure
     * @param string $key in _scheduledStructure represent element name
     * @return void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function scheduleElement(
        Layout\ScheduledStructure $scheduledStructure,
        Layout\Data\Structure $structure,
        $key
    ) {
        $row = $scheduledStructure->getStructureElement($key);
        $data = $scheduledStructure->getStructureElementData($key);
        // if we have reference container to not existed element
        if (!isset($row[self::SCHEDULED_STRUCTURE_INDEX_TYPE])) {
            $this->logger->log("Broken reference: missing declaration of the element '{$key}'.", \Zend_Log::CRIT);
            $scheduledStructure->unsetPathElement($key);
            $scheduledStructure->unsetStructureElement($key);
            return;
        }
        list($type, $alias, $parentName, $siblingName, $isAfter) = $row;
        $name = $this->_createStructuralElement($structure, $key, $type, $parentName . $alias);
        if ($parentName) {
            // recursively populate parent first
            if ($scheduledStructure->hasStructureElement($parentName)) {
                $this->scheduleElement($scheduledStructure, $structure, $parentName);
            }
            if ($structure->hasElement($parentName)) {
                try {
                    $structure->setAsChild($name, $parentName, $alias);
                } catch (\Exception $e) {
                    $this->logger->log($e->getMessage());
                }
            } else {
                $this->logger->log(
                    "Broken reference: the '{$name}' element cannot be added as child to '{$parentName}', " .
                    'because the latter doesn\'t exist',
                    \Zend_Log::CRIT
                );
            }
        }

        // Move from scheduledStructure to scheduledElement
        $scheduledStructure->unsetStructureElement($key);
        $scheduledStructure->setElement($name, [$type, $data]);

        /**
         * Some elements provide info "after" or "before" which sibling they are supposed to go
         * Make sure to populate these siblings as well and order them correctly
         */
        if ($siblingName) {
            if ($scheduledStructure->hasStructureElement($siblingName)) {
                $this->scheduleElement($scheduledStructure, $structure, $siblingName);
            }
            $structure->reorderChildElement($parentName, $name, $siblingName, $isAfter);
        }
    }

    /**
     * Register an element in structure
     *
     * Will assign an "anonymous" name to the element, if provided with an empty name
     *
     * @param Layout\Data\Structure $structure
     * @param string $name
     * @param string $type
     * @param string $class
     * @return string
     */
    protected function _createStructuralElement(Layout\Data\Structure $structure, $name, $type, $class)
    {
        if (empty($name)) {
            $name = $this->_generateAnonymousName($class);
        }
        $structure->createElement($name, array('type' => $type));
        return $name;
    }
}
