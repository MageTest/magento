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
namespace Magento\Shipping\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Shipping\Helper\Data
     */
    protected $_helper = null;

    protected function setUp()
    {
        $this->_helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Shipping\Helper\Data'
        );
    }

    /**
     * @param string $modelName
     * @param string $getIdMethod
     * @param int $entityId
     * @param string $code
     * @param string $expected
     * @dataProvider getTrackingPopupUrlBySalesModelDataProvider
     */
    public function testGetTrackingPopupUrlBySalesModel($modelName, $getIdMethod, $entityId, $code, $expected)
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $constructArgs = array();
        if ('Magento\Sales\Model\Order\Shipment' == $modelName) {
            $orderFactory = $this->_getMockOrderFactory($code);
            $constructArgs['orderFactory'] = $orderFactory;
        } elseif ('Magento\Sales\Model\Order\Shipment\Track' == $modelName) {
            $shipmentFactory = $this->_getMockShipmentFactory($code);
            $constructArgs['shipmentFactory'] = $shipmentFactory;
        }

        $model = $objectManager->create($modelName, $constructArgs);
        $model->{$getIdMethod}($entityId);

        if ('Magento\Sales\Model\Order' == $modelName) {
            $model->setProtectCode($code);
        }

        $actual = $this->_helper->getTrackingPopupUrlBySalesModel($model);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @param $code
     * @return \Magento\Sales\Model\OrderFactory
     */
    protected function _getMockOrderFactory($code)
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $order = $objectManager->create('Magento\Sales\Model\Order');
        $order->setProtectCode($code);
        $orderFactory = $this->getMock('Magento\Sales\Model\OrderFactory', array('create'), array(), '', false);
        $orderFactory->expects($this->atLeastOnce())->method('create')->will($this->returnValue($order));
        return $orderFactory;
    }

    /**
     * @param $code
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getMockShipmentFactory($code)
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $orderFactory = $this->_getMockOrderFactory($code);
        $shipmentArgs = array('orderFactory' => $orderFactory);

        $shipment = $objectManager->create('Magento\Sales\Model\Order\Shipment', $shipmentArgs);
        $shipmentFactory = $this->getMock(
            'Magento\Sales\Model\Order\ShipmentFactory',
            array('create'),
            array(),
            '',
            false
        );
        $shipmentFactory->expects($this->atLeastOnce())->method('create')->will($this->returnValue($shipment));
        return $shipmentFactory;
    }

    /**
     * @return array
     */
    public function getTrackingPopupUrlBySalesModelDataProvider()
    {
        return array(
            array(
                'Magento\Sales\Model\Order',
                'setId',
                42,
                'abc',
                'http://localhost/index.php/shipping/tracking/popup/hash/b3JkZXJfaWQ6NDI6YWJj/'
            ),
            array(
                'Magento\Sales\Model\Order\Shipment',
                'setId',
                42,
                'abc',
                'http://localhost/index.php/shipping/tracking/popup/hash/c2hpcF9pZDo0MjphYmM,/'
            ),
            array(
                'Magento\Sales\Model\Order\Shipment\Track',
                'setEntityId',
                42,
                'abc',
                'http://localhost/index.php/shipping/tracking/popup/hash/dHJhY2tfaWQ6NDI6YWJj/'
            )
        );
    }
}
