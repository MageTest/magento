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

namespace Magento\Framework\Api\Code\Generator;

use Magento\Framework\Code\Generator\CodeGenerator;
use Magento\Framework\Code\Generator\EntityAbstract;
use Magento\Framework\Code\Generator\Io;
use Magento\Framework\ObjectManager\ConfigInterface as ObjectManagerConfig;
use Zend\Code\Reflection\ClassReflection;

/**
 * Class Builder
 */
class DataBuilder extends EntityAbstract
{
    /**
     * Builder Entity, used for a builders built based on Data Objects
     */
    const ENTITY_TYPE = 'dataBuilder';

    /**
     * Builder Entity, used for a builders built based on API interfaces
     */
    const ENTITY_TYPE_BUILDER = 'builder';

    /**
     * Data Model property name
     */
    const DATA_PROPERTY_NAME = 'data';

    /**#@+
     * Constant which defines if builder is created for building data objects or data models.
     */
    const TYPE_DATA_OBJECT = 'data_object';
    const TYPE_DATA_MODEL = 'data_model';
    /**#@-*/

    /** @var string */
    protected $currentDataType;

    /** @var string[] */
    protected $extensibleInterfaceMethods;

    /**
     * Retrieve class properties
     *
     * @return array
     */
    protected function _getClassProperties()
    {
        return [];
    }

    /**
     * Get default constructor definition for generated class
     *
     * @return array
     */
    protected function _getDefaultConstructorDefinition()
    {
        $constructorDefinition = [
                'name' => '__construct',
                'parameters' => [
                    ['name' => 'objectFactory', 'type' => '\Magento\Framework\Api\ObjectFactory'],
                    ['name' => 'metadataService', 'type' => '\Magento\Framework\Api\MetadataServiceInterface'],
                    ['name' => 'attributeValueBuilder', 'type' => '\Magento\Framework\Api\AttributeDataBuilder'],
                    ['name' => 'objectProcessor', 'type' => '\Magento\Framework\Reflection\DataObjectProcessor'],
                    ['name' => 'typeProcessor', 'type' => '\Magento\Framework\Reflection\TypeProcessor'],
                    ['name' => 'dataBuilderFactory', 'type' => '\Magento\Framework\Serialization\DataBuilderFactory'],
                    ['name' => 'objectManagerConfig', 'type' => '\Magento\Framework\ObjectManager\ConfigInterface'],
                    [
                        'name' => 'modelClassInterface',
                        'type' => 'string',
                        'defaultValue' => $this->_getNullDefaultValue()
                    ]
                ],
                'docblock' => [
                    'shortDescription' => 'Initialize the builder',
                    'tags' => [
                        [
                            'name' => 'param',
                            'description' => '\Magento\Framework\Api\ObjectFactory $objectFactory'
                        ],
                        [
                            'name' => 'param',
                            'description' => '\Magento\Framework\Api\MetadataServiceInterface $metadataService'
                        ],
                        [
                            'name' => 'param',
                            'description' => '\Magento\Framework\Api\AttributeDataBuilder $attributeValueBuilder'
                        ],
                        [
                            'name' => 'param',
                            'description' => '\Magento\Framework\Reflection\DataObjectProcessor $objectProcessor'
                        ],
                        [
                            'name' => 'param',
                            'description' => '\Magento\Framework\Reflection\TypeProcessor $typeProcessor'
                        ],
                        [
                            'name' => 'param',
                            'description' => '\Magento\Framework\Serialization\DataBuilderFactory $dataBuilderFactory'
                        ],
                        [
                            'name' => 'param',
                            'description' => '\Magento\Framework\ObjectManager\ConfigInterface $objectManagerConfig'
                        ],
                        [
                            'name' => 'param',
                            'description' => 'string|null $modelClassInterface'
                        ]
                    ]
                ],
            'body' => "parent::__construct(\$objectFactory, \$metadataService, \$attributeValueBuilder, "
                . "\$objectProcessor, \$typeProcessor, \$dataBuilderFactory, \$objectManagerConfig, "
                . "'{$this->_getSourceClassName()}');"
        ];
        return $constructorDefinition;
    }

    /**
     * Return a list of methods for class generator
     *
     * @return array
     */
    protected function _getClassMethods()
    {
        $methods = [];
        $className = $this->_getSourceClassName();
        $reflectionClass = new \ReflectionClass($className);
        $publicMethods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($publicMethods as $method) {
            if ($this->canUseMethodForGeneration($method)) {
                $methods[] = $this->_getMethodInfo($method);
            }
        }
        $defaultConstructorDefinition = $this->_getDefaultConstructorDefinition();
        if (!empty($defaultConstructorDefinition)) {
            $methods[] = $defaultConstructorDefinition;
        }
        return $methods;
    }

    /**
     * Check if the specified method should be used during generation builder generation.
     *
     * @param \ReflectionMethod $method
     * @return bool
     */
    protected function canUseMethodForGeneration($method)
    {
        $isGetter = substr($method->getName(), 0, 3) == 'get' || substr($method->getName(), 0, 2) == 'is';
        $isSuitableMethodType = !($method->isConstructor() || $method->isFinal()
            || $method->isStatic() || $method->isDestructor());
        $isMagicMethod = in_array($method->getName(), array('__sleep', '__wakeup', '__clone'));
        $isPartOfExtensibleInterface = in_array($method->getName(), $this->getExtensibleInterfaceMethods());
        return $isGetter && $isSuitableMethodType && !$isMagicMethod && !$isPartOfExtensibleInterface;
    }

    /**
     * Retrieve method info
     *
     * @param \ReflectionMethod $method
     * @return array
     */
    protected function _getMethodInfo(\ReflectionMethod $method)
    {
        if (substr($method->getName(), 0, 2) == 'is') {
            $propertyName = substr($method->getName(), 2);
        } else {
            $propertyName = substr($method->getName(), 3);
        }
        $returnType = (new ClassReflection($this->_getSourceClassName()))
            ->getMethod($method->getName())
            ->getDocBlock()
            ->getTag('return')
            ->getType();
        $fieldName = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $propertyName));
        $methodInfo = [
            'name' => 'set' . $propertyName,
            'parameters' => [
                ['name' => lcfirst($propertyName)]
            ],
            'body' => "\$this->_set('{$fieldName}', \$" . lcfirst($propertyName) . ");"
                . PHP_EOL . "return \$this;",
            'docblock' => [
                'tags' => [
                    ['name' => 'param', 'description' => $returnType . " \$" . lcfirst($propertyName)],
                    ['name' => 'return', 'description' => '$this'],
                ]
            ]
        ];
        return $methodInfo;
    }

    /**
     * Generate code
     *
     * @return string
     */
    protected function _generateCode()
    {
        $this->_classGenerator
            ->setName($this->_getResultClassName())
            ->addProperties($this->_getClassProperties())
            ->addMethods($this->_getClassMethods())
            ->setClassDocBlock($this->_getClassDocBlock())
            ->setExtendedClass('\Magento\Framework\Api\Builder');
        return $this->_getGeneratedCode();
    }

    /**
     * {@inheritdoc}
     */
    protected function _getSourceClassName()
    {
        return $this->_getDataObjectType();
    }

    /**
     * Get data object type based on suffix
     *
     * @return string
     */
    protected function _getDataObjectType()
    {
        $currentBuilderClass = $this->_getResultClassName();
        $dataBuilderSuffix = 'DataBuilder';
        if (substr($currentBuilderClass, -strlen($dataBuilderSuffix)) === $dataBuilderSuffix) {
            $dataObjectType = substr($currentBuilderClass, 0, -strlen($dataBuilderSuffix)) . 'Interface';
        } else {
            $builderSuffix = 'Builder';
            $dataObjectType = substr($currentBuilderClass, 0, -strlen($builderSuffix));
        }
        return $dataObjectType;
    }

    /**
     * Get a list of methods declared on extensible data interface.
     *
     * @return string[]
     */
    protected function getExtensibleInterfaceMethods()
    {
        if ($this->extensibleInterfaceMethods === null) {
            $interfaceReflection = new ClassReflection('Magento\Framework\Api\ExtensibleDataInterface');
            $methodsReflection = $interfaceReflection->getMethods();
            $this->extensibleInterfaceMethods = [];
            foreach ($methodsReflection as $methodReflection) {
                $this->extensibleInterfaceMethods[] = $methodReflection->getName();
            }
        }
        return $this->extensibleInterfaceMethods;
    }
}
