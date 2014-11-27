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

namespace Magento\Framework\Pricing\Price;

/**
 * Test class for \Magento\Framework\Pricing\Factory
 */
class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Factory
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    public function setUp()
    {
        $this->objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject('Magento\Framework\Pricing\Price\Factory', array(
            'objectManager' => $this->objectManagerMock
        ));
    }

    public function testCreate()
    {
        $quantity = 2.2;
        $className = 'Magento\Framework\Pricing\Price\PriceInterface';
        $priceMock = $this->getMock($className);
        $saleableItem = $this->getMock('Magento\Framework\Pricing\Object\SaleableInterface');
        $arguments = [];

        $argumentsResult = array_merge($arguments, ['saleableItem' => $saleableItem, 'quantity' => $quantity]);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($className, $argumentsResult)
            ->will($this->returnValue($priceMock));

        $this->assertEquals($priceMock, $this->model->create($saleableItem, $className, $quantity, $arguments));
    }

    /**
     * @codingStandardsIgnoreStart
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Magento\Framework\Pricing\PriceInfo\Base doesn't implement \Magento\Framework\Pricing\Price\PriceInterface
     * @codingStandardsIgnoreEnd
     */
    public function testCreateWithException()
    {
        $quantity = 2.2;
        $className = 'Magento\Framework\Pricing\PriceInfo\Base';
        $priceMock = $this->getMockBuilder($className)->disableOriginalConstructor()->getMock();
        $saleableItem = $this->getMock('Magento\Framework\Pricing\Object\SaleableInterface');
        $arguments = [];

        $argumentsResult = array_merge($arguments, ['saleableItem' => $saleableItem, 'quantity' => $quantity]);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($className, $argumentsResult)
            ->will($this->returnValue($priceMock));

        $this->model->create($saleableItem, $className, $quantity, $arguments);
    }
}
