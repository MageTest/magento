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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Rule\Model\Condition;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class CombineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Rule\Model\Condition\Combine | \PHPUnit_Framework_MockObject_MockObject
     */
    private $combine;

    /**
     * @var \Magento\Rule\Model\ConditionFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    private $conditionFactoryMock;

    /**
     * @var \Magento\Framework\Logger | \PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var  \Magento\SalesRule\Model\Rule\Condition\Product | \PHPUnit_Framework_MockObject_MockObject
     */
    private $conditionObjectMock;

    /**
     * Sets up the Mocks.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->conditionFactoryMock = $this->getMockBuilder('\Magento\Rule\Model\ConditionFactory')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->loggerMock = $this->getMockBuilder('\Magento\Framework\Logger')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->conditionObjectMock = $this->getMockBuilder('\Magento\SalesRule\Model\Rule\Condition\Product')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->combine = (new ObjectManagerHelper($this))->getObject(
            '\Magento\Rule\Model\Condition\Combine',
            [
                "conditionFactory"    => $this->conditionFactoryMock,
                "logger"    => $this->loggerMock,
            ]
        );
    }

    /**
     *
     * @covers \Magento\Rule\Model\Condition\AbstractCondition::getValueName
     *
     * @dataProvider optionValuesData
     *
     * @param string|array $value
     * @param string $expectingData
     */
    public function testGetValueName($value, $expectingData)
    {
        $this->combine
            ->setValueOption(['option_key' => 'option_value'])
            ->setValue($value);

        $this->assertEquals($expectingData, $this->combine->getValueName());
    }

    /**
     * @return array
     */
    public function optionValuesData()
    {
        return [
            ['option_key', 'option_value'],
            ['option_value', 'option_value'],
            [['option_key'], 'option_value'],
            ['', '...'],
        ];
    }

    public function testLoadArray()
    {
        $array['conditions'] = [
            [
                'type' => 'test',
                'attribute' => '',
                'operator' => '',
                'value' => '',
            ]
        ];

        $this->conditionObjectMock->expects($this->once())
            ->method('loadArray')
            ->with($array['conditions'][0], 'conditions');

        $this->conditionFactoryMock->expects($this->once())
            ->method('create')
            ->with($array['conditions'][0]['type'])
            ->willReturn($this->conditionObjectMock);

        $this->loggerMock->expects($this->never())
            ->method('logException');

        $result = $this->combine->loadArray($array);

        $this->assertInstanceOf('\Magento\Rule\Model\Condition\Combine', $result);
    }

    public function testLoadArrayLoggerCatchException()
    {
        $array['conditions'] = [
            [
                'type' => '',
                'attribute' => '',
                'operator' => '',
                'value' => '',
            ]
        ];

        $this->conditionObjectMock->expects($this->never())
            ->method('loadArray');

        $this->conditionFactoryMock->expects($this->once())
            ->method('create')
            ->with($array['conditions'][0]['type'])
            ->willThrowException(new \Exception('everything is fine, it is test'));

        $this->loggerMock->expects($this->once())
            ->method('logException')
            ->with();

        $result = $this->combine->loadArray($array);

        $this->assertInstanceOf('\Magento\Rule\Model\Condition\Combine', $result);
    }
}
