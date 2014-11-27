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
namespace Magento\Sales\Block\Adminhtml\Items\Column;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class DefaultColumnTest extends \PHPUnit_Framework_TestCase
{
    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn
     */
    protected $defaultColumn;

    /**
     * @var \Magento\Sales\Model\Order\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemMock;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->defaultColumn = $this->objectManagerHelper->getObject(
            'Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn'
        );
        $this->itemMock = $this->getMockBuilder('\Magento\Sales\Model\Order\Item')
            ->disableOriginalConstructor()
            ->setMethods(['getRowTotal', 'getDiscountAmount', 'getBaseRowTotal', 'getBaseDiscountAmount', '__wakeup'])
            ->getMock();
    }

    public function testGetTotalAmount()
    {
        $rowTotal = 10;
        $discountAmount = 2;
        $expectedResult = 8;
        $this->itemMock->expects($this->once())
            ->method('getRowTotal')
            ->will($this->returnValue($rowTotal));
        $this->itemMock->expects($this->once())
            ->method('getDiscountAmount')
            ->will($this->returnValue($discountAmount));
        $this->assertEquals($expectedResult, $this->defaultColumn->getTotalAmount($this->itemMock));
    }

    public function testGetBaseTotalAmount()
    {
        $baseRowTotal = 10;
        $baseDiscountAmount = 2;
        $expectedResult = 8;
        $this->itemMock->expects($this->once())
            ->method('getBaseRowTotal')
            ->will($this->returnValue($baseRowTotal));
        $this->itemMock->expects($this->once())
            ->method('getBaseDiscountAmount')
            ->will($this->returnValue($baseDiscountAmount));
        $this->assertEquals($expectedResult, $this->defaultColumn->getBaseTotalAmount($this->itemMock));
    }
}
