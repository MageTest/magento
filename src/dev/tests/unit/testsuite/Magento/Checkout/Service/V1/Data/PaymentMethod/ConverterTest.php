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

namespace Magento\Checkout\Service\V1\Data\PaymentMethod;

use \Magento\Checkout\Service\V1\Data\PaymentMethod;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Converter
     */
    protected $converter;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMethodBuilderMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->paymentMethodBuilderMock = $this->getMock(
            '\Magento\Checkout\Service\V1\Data\PaymentMethodBuilder', ['populateWithArray', 'create'], [], '', false
        );

        $this->converter = $this->objectManager->getObject(
            '\Magento\Checkout\Service\V1\Data\PaymentMethod\Converter',
            [
                'builder' => $this->paymentMethodBuilderMock,
            ]
        );
    }

    public function testConvertQuotePaymentObjectToPaymentDataObject()
    {
        $methodMock = $this->getMock('\Magento\Payment\Model\Method\AbstractMethod', [], [], '', false);
        $methodMock->expects($this->once())->method('getCode')->will($this->returnValue('paymentCode'));
        $methodMock->expects($this->once())->method('getTitle')->will($this->returnValue('paymentTitle'));

        $data = [
            PaymentMethod::TITLE => 'paymentTitle',
            PaymentMethod::CODE => 'paymentCode'
        ];

        $this->paymentMethodBuilderMock->expects($this->once())
            ->method('populateWithArray')
            ->with($data)
            ->will($this->returnSelf());

        $paymentMethodMock = $this->getMock('\Magento\Checkout\Service\V1\Data\PaymentMethod', [], [], '', false);

        $this->paymentMethodBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($paymentMethodMock));

        $this->assertEquals($paymentMethodMock, $this->converter->toDataObject($methodMock));
    }
}
