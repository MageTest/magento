<?php
/**
 * An associative data structure, that features "nested set" parent-child relations
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\View\Layout\Data;

use Magento\Framework\Data\Structure as DataStructure;

class Structure extends DataStructure
{
    /**
     * Name increment counter
     *
     * @var array
     */
    protected $_nameIncrement = [];

    /**
     * @var \Magento\Framework\Logger
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Logger $logger
     * @param array $elements
     */
    public function __construct(
        \Magento\Framework\Logger $logger,
        array $elements = null
    ) {
        $this->logger = $logger;
        parent::__construct($elements);
    }

    /**
     * Register an element in structure
     *
     * Will assign an "anonymous" name to the element, if provided with an empty name
     *
     * @param string $name
     * @param string $type
     * @param string $class
     * @return string
     */
    public function createStructuralElement($name, $type, $class)
    {
        if (empty($name)) {
            $name = $this->_generateAnonymousName($class);
        }
        $this->createElement($name, array('type' => $type));
        return $name;
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

        if (!isset($this->_nameIncrement[$key])) {
            $this->_nameIncrement[$key] = 0;
        }

        do {
            $name = $key . '_' . $this->_nameIncrement[$key]++;
        } while ($this->hasElement($name));

        return $name;
    }

    /**
     * Reorder a child of a specified element
     *
     * If $offsetOrSibling is null, it will put the element to the end
     * If $offsetOrSibling is numeric (integer) value, it will put the element after/before specified position
     * Otherwise -- after/before specified sibling
     *
     * @param string $parentName
     * @param string $childName
     * @param string|int|null $offsetOrSibling
     * @param bool $after
     * @return void
     */
    public function reorderChildElement($parentName, $childName, $offsetOrSibling, $after = true)
    {
        if (is_numeric($offsetOrSibling)) {
            $offset = (int)abs($offsetOrSibling) * ($after ? 1 : -1);
            $this->reorderChild($parentName, $childName, $offset);
        } elseif (null === $offsetOrSibling) {
            $this->reorderChild($parentName, $childName, null);
        } else {
            $children = array_keys($this->getChildren($parentName));
            if ($this->getChildId($parentName, $offsetOrSibling) !== false) {
                $offsetOrSibling = $this->getChildId($parentName, $offsetOrSibling);
            }
            $sibling = $this->_filterSearchMinus($offsetOrSibling, $children, $after);
            if ($childName !== $sibling) {
                $siblingParentName = $this->getParentId($sibling);
                if ($parentName !== $siblingParentName) {
                    $this->logger->log(
                        "Broken reference: the '{$childName}' tries to reorder itself towards '{$sibling}', but " .
                        "their parents are different: '{$parentName}' and '{$siblingParentName}' respectively.",
                        \Zend_Log::CRIT
                    );
                    return;
                }
                $this->reorderToSibling($parentName, $childName, $sibling, $after ? 1 : -1);
            }
        }
    }

    /**
     * Search for an array element using needle, but needle may be '-', which means "first" or "last" element
     *
     * Returns first or last element in the haystack, or the $needle argument
     *
     * @param string $needle
     * @param array $haystack
     * @param bool $isLast
     * @return string
     */
    protected function _filterSearchMinus($needle, array $haystack, $isLast)
    {
        if ('-' === $needle) {
            if ($isLast) {
                return array_pop($haystack);
            }
            return array_shift($haystack);
        }
        return $needle;
    }
}
