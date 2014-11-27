<?php
/**
 * Class constructor validator. Validates call of parent construct
 *
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
namespace Magento\Framework\Code\Validator;

use Magento\Framework\Code\ValidatorInterface;

class ConstructorIntegrity implements ValidatorInterface
{
    /**
     * @var \Magento\Framework\Code\Reader\ArgumentsReader
     */
    protected $_argumentsReader;

    /**
     * @param \Magento\Framework\Code\Reader\ArgumentsReader $argumentsReader
     */
    public function __construct(\Magento\Framework\Code\Reader\ArgumentsReader $argumentsReader = null)
    {
        $this->_argumentsReader = $argumentsReader ?: new \Magento\Framework\Code\Reader\ArgumentsReader();
    }

    /**
     * Validate class
     *
     * @param string $className
     * @return bool
     * @throws \Magento\Framework\Code\ValidationException
     */
    public function validate($className)
    {
        $class = new \ReflectionClass($className);
        $parent = $class->getParentClass();

        /** Check whether parent class exists and has __construct method */
        if (!$parent) {
            return true;
        }

        /** Get parent class __construct arguments */
        $parentArguments = $this->_argumentsReader->getConstructorArguments($parent, true, true);
        if (empty($parentArguments)) {
            return true;
        }

        /** Check whether class has __construct */
        $classArguments = $this->_argumentsReader->getConstructorArguments($class);
        if (null === $classArguments) {
            return true;
        }

        /** Check whether class has parent::__construct call */
        $callArguments = $this->_argumentsReader->getParentCall($class, $classArguments);
        if (null === $callArguments) {
            return true;
        }

        /** Get parent class __construct arguments */
        $parentArguments = $this->_argumentsReader->getConstructorArguments($parent, true, true);

        foreach ($parentArguments as $index => $requiredArgument) {
            if (isset($callArguments[$index])) {
                $actualArgument = $callArguments[$index];
            } else {
                if ($requiredArgument['isOptional']) {
                    continue;
                }

                $classPath = str_replace('\\', '/', $class->getFileName());
                throw new \Magento\Framework\Code\ValidationException(
                    'Missed required argument ' .
                    $requiredArgument['name'] .
                    ' in parent::__construct call. File: ' .
                    $classPath
                );
            }

            $isCompatibleTypes = $this->_argumentsReader->isCompatibleType(
                $requiredArgument['type'],
                $actualArgument['type']
            );
            if (false == $isCompatibleTypes) {
                $classPath = str_replace('\\', '/', $class->getFileName());
                throw new \Magento\Framework\Code\ValidationException(
                    'Incompatible argument type: Required type: ' .
                    $requiredArgument['type'] .
                    '. Actual type: ' .
                    $actualArgument['type'] .
                    '; File: ' .
                    PHP_EOL .
                    $classPath .
                    PHP_EOL
                );
            }
        }

        /**
         * Try to detect unused arguments
         * Check whether count of passed parameters less or equal that count of count parent class arguments
         */
        if (count($callArguments) > count($parentArguments)) {
            $extraParameters = array_slice($callArguments, count($parentArguments));
            $names = array();
            foreach ($extraParameters as $param) {
                $names[] = '$' . $param['name'];
            }

            $classPath = str_replace('\\', '/', $class->getFileName());
            throw new \Magento\Framework\Code\ValidationException(
                'Extra parameters passed to parent construct: ' . implode(', ', $names) . '. File: ' . $classPath
            );
        }
        return true;
    }
}
