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
namespace Magento\Framework\Image\Adapter;

use Magento\TestFramework\Helper\ObjectManager;

/**
 * Mocking global functions crucial for this adapter
 */

/**
 * @param $paramName
 * @throws \InvalidArgumentException
 * @return string
 */
function ini_get($paramName)
{
    if ('memory_limit' == $paramName) {
        return Gd2Test::$memoryLimit;
    }

    throw new \InvalidArgumentException('Unexpected parameter ' . $paramName);
}

/**
 * @param $file
 * @return mixed
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
function getimagesize($file)
{
    return Gd2Test::$imageData;
}

/**
 * @param $real
 * @return int
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
function memory_get_usage($real)
{
    return 1000000;
}

/**
 * @param $callable
 * @param $param
 * @return bool
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
function call_user_func($callable, $param)
{
    return false;
}
/**
 * \Magento\Framework\Image\Adapter\Gd2 class test
 */
class Gd2Test extends \PHPUnit_Framework_TestCase
{
    /**
     * Value to mock ini_get('memory_limit')
     *
     * @var string
     */
    public static $memoryLimit;

    /**
     * @var array simulation of getimagesize()
     */
    public static $imageData = [];

    /**
     * Adapter for testing
     * @var \Magento\Framework\Image\Adapter\Gd2
     */
    protected $adapter;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * Setup testing object
     */
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->adapter = $this->objectManager->getObject('\Magento\Framework\Image\Adapter\Gd2');
    }

    /**
     * Test parent class
     */
    public function testParentClass()
    {
        $this->assertInstanceOf('\Magento\Framework\Image\Adapter\AbstractAdapter', $this->adapter);
    }

    /**
     * Test open() method
     *
     * @param array $fileData
     * @param string|bool|null $exception
     * @param string $limit
     * @dataProvider filesProvider
     */
    public function testOpen($fileData, $exception, $limit)
    {
        self::$memoryLimit = $limit;
        self::$imageData = $fileData;

        if (!empty($exception)) {
            $this->setExpectedException($exception);
        }

        $this->adapter->open('file');
    }

    public function filesProvider()
    {
        $smallFile = [
            0 => 480,
            1 => 320,
            2 => 2,
            3 => 'width="480" height="320"',
            'bits' => 8,
            'channels' => 3,
            'mime' => 'image/jpeg'
        ];

        $bigFile = [
            0 => 3579,
            1 => 2398,
            2 => 2,
            3 => 'width="3579" height="2398"',
            'bits' => 8,
            'channels' => 3,
            'mime' => 'image/jpeg'
        ];

        return [
            'positive_M' => [$smallFile, false, '2M'],
            'positive_KB' => [$smallFile, false, '2048K'],
            'negative_KB' => [$bigFile, 'OverflowException', '2048K'],
            'negative_bytes' => [$bigFile, 'OverflowException', '2048000'],
            'no_limit' => [$bigFile, false, '-1'],
        ];
    }
}
