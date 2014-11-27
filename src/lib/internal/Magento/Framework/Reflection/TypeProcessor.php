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
namespace Magento\Framework\Reflection;

use Zend\Code\Reflection\ClassReflection;
use Magento\Framework\Exception\SerializationException;

/**
 * Type processor of config reader properties
 */
class TypeProcessor
{
    /**#@+
     * Pre-normalized type constants
     */
    const STRING_TYPE = 'str';
    const INT_TYPE = 'integer';
    const BOOLEAN_TYPE = 'bool';
    const ANY_TYPE = 'mixed';
    /**#@-*/

    /**#@+
     * Normalized type constants
     */
    const NORMALIZED_STRING_TYPE = 'string';
    const NORMALIZED_INT_TYPE = 'int';
    const NORMALIZED_FLOAT_TYPE = 'float';
    const NORMALIZED_DOUBLE_TYPE = 'double';
    const NORMALIZED_BOOLEAN_TYPE = 'boolean';
    const NORMALIZED_ANY_TYPE = 'anyType';
    /**#@-*/

    /**
     * Array of types data.
     * <pre>array(
     *     $complexTypeName => array(
     *         'documentation' => $typeDocumentation
     *         'parameters' => array(
     *             $firstParameter => array(
     *                 'type' => $type,
     *                 'required' => $isRequired,
     *                 'default' => $defaultValue,
     *                 'documentation' => $parameterDocumentation
     *             ),
     *             ...
     *         )
     *     ),
     *     ...
     * )</pre>
     *
     * @var array
     */
    protected $_types = array();

    /**
     * Types class map.
     * <pre>array(
     *     $complexTypeName => $interfaceName,
     *     ...
     * )</pre>
     *
     * @var array
     */
    protected $_typeToClassMap = array();

    /**
     * Retrieve processed types data.
     *
     * @return array
     */
    public function getTypesData()
    {
        return $this->_types;
    }

    /**
     * Retrieve data type details for the given type name.
     *
     * @param string $typeName
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getTypeData($typeName)
    {
        if (!isset($this->_types[$typeName])) {
            throw new \InvalidArgumentException(sprintf('Data type "%s" is not declared.', $typeName));
        }
        return $this->_types[$typeName];
    }

    /**
     * Add or update type data in config.
     *
     * @param string $typeName
     * @param array $data
     * @return void
     */
    public function setTypeData($typeName, $data)
    {
        if (!isset($this->_types[$typeName])) {
            $this->_types[$typeName] = $data;
        } else {
            $this->_types[$typeName] = array_merge_recursive($this->_types[$typeName], $data);
        }
    }

    /**
     * Process type name. In case parameter type is a complex type (class) - process its properties.
     *
     * @param string $type
     * @return string
     * @throws \LogicException
     */
    public function process($type)
    {
        $typeName = $this->normalizeType($type);
        if (!$this->isTypeSimple($typeName) && !$this->isTypeAny($typeName)) {
            $typeSimple = $this->getArrayItemType($type);
            if (!(class_exists($typeSimple) || interface_exists($typeSimple))) {
                throw new \LogicException(
                    sprintf('Class "%s" does not exist. Please note that namespace must be specified.', $type)
                );
            }
            $complexTypeName = $this->translateTypeName($type);
            if (!isset($this->_types[$complexTypeName])) {
                $this->_processComplexType($type);
                if (!$this->isArrayType($complexTypeName)) {
                    $this->_typeToClassMap[$complexTypeName] = $type;
                }
            }
            $typeName = $complexTypeName;
        }

        return $typeName;
    }

    /**
     * Retrieve complex type information from class public properties.
     *
     * @param string $class
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function _processComplexType($class)
    {
        $typeName = $this->translateTypeName($class);
        $this->_types[$typeName] = array();
        if ($this->isArrayType($class)) {
            $this->process($this->getArrayItemType($class));
        } else {
            if (!(class_exists($class) || interface_exists($class))) {
                throw new \InvalidArgumentException(
                    sprintf('Could not load the "%s" class as parameter type.', $class)
                );
            }
            $reflection = new ClassReflection($class);
            $docBlock = $reflection->getDocBlock();
            $this->_types[$typeName]['documentation'] = $docBlock ? $this->_getDescription($docBlock) : '';
            /** @var \Zend\Code\Reflection\MethodReflection $methodReflection */
            foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $methodReflection) {
                if ($methodReflection->class === "Magento\Framework\Model\AbstractModel") {
                    continue;
                }
                $this->_processMethod($methodReflection, $typeName);
            }
        }

        return $this->_types[$typeName];
    }

    /**
     * Collect metadata for virtual field corresponding to current method if it is a getter (used in WSDL generation).
     *
     * @param \Zend\Code\Reflection\MethodReflection $methodReflection
     * @param string $typeName
     * @return void
     */
    protected function _processMethod(\Zend\Code\Reflection\MethodReflection $methodReflection, $typeName)
    {
        $isGetter = (strpos($methodReflection->getName(), 'get') === 0)
            || (strpos($methodReflection->getName(), 'is') === 0)
            || (strpos($methodReflection->getName(), 'has') === 0);
        /** Field will not be added to WSDL if getter has params */
        if ($isGetter && !$methodReflection->getNumberOfParameters()) {
            $returnMetadata = $this->getGetterReturnType($methodReflection);
            $fieldName = $this->dataObjectGetterNameToFieldName($methodReflection->getName());
            $this->_types[$typeName]['parameters'][$fieldName] = array(
                'type' => $this->process($returnMetadata['type']),
                'required' => $returnMetadata['isRequired'],
                'documentation' => $returnMetadata['description']
            );
        }
    }

    /**
     * Get short and long description from docblock and concatenate.
     *
     * @param \Zend\Code\Reflection\DocBlockReflection $doc
     * @return string
     */
    protected function _getDescription(\Zend\Code\Reflection\DocBlockReflection $doc)
    {
        $shortDescription = $doc->getShortDescription();
        $longDescription = $doc->getLongDescription();

        $description = rtrim($shortDescription);
        $longDescription = str_replace(array("\n", "\r"), '', $longDescription);
        if (!empty($longDescription) && !empty($description)) {
            $description .= " ";
        }
        $description .= ltrim($longDescription);

        return $description;
    }

    /**
     * Convert Data Object getter name into field name.
     *
     * @param string $getterName
     * @return string
     */
    public function dataObjectGetterNameToFieldName($getterName)
    {
        if ((strpos($getterName, 'get') === 0)) {
            /** Remove 'get' prefix and make the first letter lower case */
            $fieldName = substr($getterName, strlen('get'));
        } elseif ((strpos($getterName, 'is') === 0)) {
            /** Remove 'is' prefix and make the first letter lower case */
            $fieldName = substr($getterName, strlen('is'));
        } elseif ((strpos($getterName, 'has') === 0)) {
            /** Remove 'has' prefix and make the first letter lower case */
            $fieldName = substr($getterName, strlen('has'));
        } else {
            $fieldName = $getterName;
        }
        return lcfirst($fieldName);
    }

    /**
     * Identify getter return type by its reflection.
     *
     * @param \Zend\Code\Reflection\MethodReflection $methodReflection
     * @return array <pre>array(
     *     'type' => <string>$type,
     *     'isRequired' => $isRequired,
     *     'description' => $description
     * )</pre>
     * @throws \InvalidArgumentException
     */
    public function getGetterReturnType($methodReflection)
    {
        $methodDocBlock = $methodReflection->getDocBlock();
        if (!$methodDocBlock) {
            throw new \InvalidArgumentException('Each getter must have description with @return annotation.');
        }
        $returnAnnotations = $methodDocBlock->getTags('return');
        if (empty($returnAnnotations)) {
            throw new \InvalidArgumentException('Getter return type must be specified using @return annotation.');
        }
        /** @var \Zend\Code\Reflection\DocBlock\Tag\ReturnTag $returnAnnotation */
        $returnAnnotation = current($returnAnnotations);
        $returnType = $returnAnnotation->getType();
        /*
         * Adding this code as a workaround since \Zend\Code\Reflection\DocBlock\Tag\ReturnTag::initialize does not
         * detect and return correct type for array of objects in annotation.
         * eg @return \Magento\Webapi\Service\Entity\SimpleData[] is returned with type
         * \Magento\Webapi\Service\Entity\SimpleData instead of \Magento\Webapi\Service\Entity\SimpleData[]
         */
        $escapedReturnType = str_replace('\\', '\\\\', $returnType);
        if (preg_match("/.*\\@return\\s+({$escapedReturnType}\\[\\]).*/i", $methodDocBlock->getContents(), $matches)) {
            $returnType = $matches[1];
        }
        $isRequired = preg_match("/.*\@return\s+\S+\|null.*/i", $methodDocBlock->getContents(), $matches)
            ? false
            : true;
        return [
            'type' => $returnType,
            'isRequired' => $isRequired,
            'description' => $returnAnnotation->getDescription(),
            'parameterCount' => $methodReflection->getNumberOfParameters()
        ];
    }

    /**
     * Normalize short type names to full type names.
     *
     * @param string $type
     * @return string
     */
    public function normalizeType($type)
    {
        $normalizationMap = array(
            self::STRING_TYPE => self::NORMALIZED_STRING_TYPE,
            self::INT_TYPE => self::NORMALIZED_INT_TYPE,
            self::BOOLEAN_TYPE => self::NORMALIZED_BOOLEAN_TYPE,
            self::ANY_TYPE => self::NORMALIZED_ANY_TYPE
        );

        return is_string($type) && isset($normalizationMap[$type]) ? $normalizationMap[$type] : $type;
    }

    /**
     * Check if given type is a simple type.
     *
     * @param string $type
     * @return bool
     */
    public function isTypeSimple($type)
    {
        $type = $this->normalizeType($type);
        if ($this->isArrayType($type)) {
            $type = $this->getArrayItemType($type);
        }

        return in_array(
            $type,
            array(
                self::NORMALIZED_STRING_TYPE,
                self::NORMALIZED_INT_TYPE,
                self::NORMALIZED_FLOAT_TYPE,
                self::NORMALIZED_DOUBLE_TYPE,
                self::NORMALIZED_BOOLEAN_TYPE
            )
        );
    }

    /**
     * Check if given type is any type.
     *
     * @param string $type
     * @return bool
     */
    public function isTypeAny($type)
    {
        $type = $this->normalizeType($type);
        if ($this->isArrayType($type)) {
            $type = $this->getArrayItemType($type);
        }

        return ($type == self::NORMALIZED_ANY_TYPE);
    }

    /**
     * Check if given type is an array of type items.
     * Example:
     * <pre>
     *  ComplexType[] -> array of ComplexType items
     *  string[] -> array of strings
     * </pre>
     *
     * @param string $type
     * @return bool
     */
    public function isArrayType($type)
    {
        return (bool)preg_match('/(\[\]$|^ArrayOf)/', $type);
    }

    /**
     * Get item type of the array.
     * Example:
     * <pre>
     *  ComplexType[] => ComplexType
     *  string[] => string
     *  int[] => integer
     * </pre>
     *
     * @param string $arrayType
     * @return string
     */
    public function getArrayItemType($arrayType)
    {
        return $this->normalizeType(str_replace('[]', '', $arrayType));
    }

    /**
     * Translate complex type class name into type name.
     *
     * Example:
     * <pre>
     *  \Magento\Customer\Service\V1\Data\Customer => CustomerV1DataCustomer
     *  \Magento\Catalog\Service\V2\Data\Product => CatalogV2DataProduct
     * </pre>
     *
     * @param string $class
     * @return string
     * @throws \InvalidArgumentException
     */
    public function translateTypeName($class)
    {
        if (preg_match('/\\\\?(.*)\\\\(.*)\\\\(Service|Api)\\\\\2?(.*)/', $class, $matches)) {
            $moduleNamespace = $matches[1] == 'Magento' ? '' : $matches[1];
            $moduleName = $matches[2];
            $typeNameParts = explode('\\', $matches[4]);

            return ucfirst($moduleNamespace . $moduleName . implode('', $typeNameParts));
        }
        throw new \InvalidArgumentException(sprintf('Invalid parameter type "%s".', $class));
    }

    /**
     * Translate array complex type name.
     *
     * Example:
     * <pre>
     *  ComplexTypeName[] => ArrayOfComplexTypeName
     *  string[] => ArrayOfString
     * </pre>
     *
     * @param string $type
     * @return string
     */
    public function translateArrayTypeName($type)
    {
        return 'ArrayOf' . ucfirst($this->getArrayItemType($type));
    }

    /**
     * Convert the value to the requested simple or any type
     *
     * @param int|string|float|int[]|string[]|float[] $value
     * @param string $type Convert given value to the this simple type
     * @return int|string|float|int[]|string[]|float[] Return the value which is converted to type
     * @throws SerializationException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function processSimpleAndAnyType($value, $type)
    {
        $isArrayType = $this->isArrayType($type);
        if ($isArrayType && is_array($value)) {
            $arrayItemType = $this->getArrayItemType($type);
            foreach (array_keys($value) as $key) {
                if ($value !== null && !settype($value[$key], $arrayItemType)) {
                    throw new SerializationException(
                        SerializationException::TYPE_MISMATCH,
                        ['value' => $value, 'type' => $type]
                    );
                }
            }
        } elseif ($isArrayType && is_null($value)) {
            return null;
        } elseif (!$isArrayType && !is_array($value)) {
            if ($value !== null && $type !== self::ANY_TYPE && !settype($value, $type)) {
                throw new SerializationException(
                    SerializationException::TYPE_MISMATCH,
                    ['value' => (string)$value, 'type' => $type]
                );
            }
        } else {
            throw new SerializationException(
                SerializationException::TYPE_MISMATCH,
                ['value' => (string)$value, 'type' => $type]
            );
        }
        return $value;
    }
}
