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
namespace Magento\Framework\Api;

/**
 * Base Builder Class for simple data Objects
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class AbstractSimpleObjectBuilder implements SimpleBuilderInterface
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @var ObjectFactory
     */
    protected $objectFactory;

    /**
     * @param ObjectFactory $objectFactory
     */
    public function __construct(ObjectFactory $objectFactory)
    {
        $this->data = array();
        $this->objectFactory = $objectFactory;
    }

    /**
     * Initializes Data Object with the data from array
     *
     * @param array $data
     * @return $this
     */
    protected function _setDataValues(array $data)
    {
        $dataObjectMethods = get_class_methods($this->_getDataObjectType());
        foreach ($data as $key => $value) {
            /* First, verify is there any getter for the key on the Service Data Object */
            $possibleMethods = array(
                'get' . \Magento\Framework\Api\SimpleDataObjectConverter::snakeCaseToUpperCamelCase($key),
                'is' . \Magento\Framework\Api\SimpleDataObjectConverter::snakeCaseToUpperCamelCase($key)
            );
            if (array_intersect($possibleMethods, $dataObjectMethods)) {
                $this->data[$key] = $value;
            }
        }
        return $this;
    }

    /**
     * Builds the Data Object
     *
     * @return AbstractSimpleObject
     */
    public function create()
    {
        $dataObjectType = $this->_getDataObjectType();
        $dataObject = $this->objectFactory->create($dataObjectType, ['builder' => $this]);
        $this->data = array();
        return $dataObject;
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return $this
     */
    protected function _set($key, $value)
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * Return the Data type class name
     *
     * @return string
     */
    protected function _getDataObjectType()
    {
        $currentClass = get_class($this);
        $dataBuilderSuffix = 'DataBuilder';
        if (substr($currentClass, -strlen($dataBuilderSuffix)) === $dataBuilderSuffix) {
            $dataObjectType = substr($currentClass, 0, -strlen($dataBuilderSuffix)) . 'Interface';
        } else {
            $builderSuffix = 'Builder';
            $dataObjectType = substr($currentClass, 0, -strlen($builderSuffix));
        }
        return $dataObjectType;
    }

    /**
     * Return data Object data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}
