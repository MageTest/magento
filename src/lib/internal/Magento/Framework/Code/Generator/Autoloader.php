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
namespace Magento\Framework\Code\Generator;

use \Magento\Framework\Code\Generator;
use \Magento\Framework\Autoload\AutoloaderRegistry;
use \Magento\Framework\Autoload\AutoloaderInterface;

class Autoloader
{
    /**
     * @var \Magento\Framework\Code\Generator
     */
    protected $_generator;

    /**
     * @param \Magento\Framework\Code\Generator $generator
     */
    public function __construct(
        \Magento\Framework\Code\Generator $generator
    ) {
        $this->_generator = $generator;
    }

    /**
     * Load specified class name and generate it if necessary
     *
     * @param string $className
     * @return bool True if class was loaded
     */
    public function load($className)
    {
        if (!class_exists($className)) {
            return Generator::GENERATION_ERROR != $this->_generator->generateClass($className);
        }
        return true;
    }
}
