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
namespace Magento\ConfigurableProduct\Model\Attribute;

use Magento\TestFramework\Helper\ObjectManager;

class LockValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ConfigurableProduct\Model\Attribute\LockValidator
     */
    private $model;

    /**
     * @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $adapter;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    private $select;

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->resource = $this->getMockBuilder('Magento\Framework\App\Resource')
            ->setMethods(['getConnection', 'getTableName'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->adapter = $this->getMockBuilder('Magento\Framework\DB\Adapter\AdapterInterface')
            ->setMethods(['select', 'fetchOne'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->select = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->setMethods(['reset', 'from', 'join', 'where', 'group', 'limit'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $helper->getObject(
            'Magento\ConfigurableProduct\Model\Attribute\LockValidator',
            ['resource' => $this->resource]
        );
    }

    public function testValidate()
    {
        $this->validate(false);
    }

    /**
     * @expectedException \Magento\Framework\Model\Exception
     * @expectedExceptionMessage This attribute is used in configurable products.
     */
    public function testValidateException()
    {
        $this->validate(true);
    }

    public function validate($exception)
    {
        $attrTable = 'someAttributeTable';
        $productTable = 'someProductTable';
        $attributeId = 333;
        $attributeSet = 'attrSet';

        $bind = ['attribute_id' => $attributeId, 'attribute_set_id' => $attributeSet];

        /** @var \Magento\Framework\Model\AbstractModel|\PHPUnit_Framework_MockObject_MockObject $object */
        $object = $this->getMockBuilder('Magento\Framework\Model\AbstractModel')
            ->setMethods(['getAttributeId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $object->expects($this->once())->method('getAttributeId')->will($this->returnValue($attributeId));

        $this->resource->expects($this->once())->method('getConnection')->with($this->equalTo('read'))
            ->will($this->returnValue($this->adapter));
        $this->resource->expects($this->at(1))->method('getTableName')
            ->with($this->equalTo('catalog_product_super_attribute'))
            ->will($this->returnValue($attrTable));
        $this->resource->expects($this->at(2))->method('getTableName')
            ->with($this->equalTo('catalog_product_entity'))
            ->will($this->returnValue($productTable));

        $this->adapter->expects($this->once())->method('select')
            ->will($this->returnValue($this->select));
        $this->adapter->expects($this->once())->method('fetchOne')
            ->with($this->equalTo($this->select), $this->equalTo($bind))
            ->will($this->returnValue($exception));

        $this->select->expects($this->once())->method('reset')
            ->will($this->returnValue($this->select));
        $this->select->expects($this->once())->method('from')
            ->with(
                $this->equalTo(['main_table' => $attrTable]),
                $this->equalTo(['psa_count' => 'COUNT(product_super_attribute_id)'])
            )
            ->will($this->returnValue($this->select));
        $this->select->expects($this->once())->method('join')
            ->with(
                $this->equalTo(['entity' => $productTable]),
                $this->equalTo('main_table.product_id = entity.entity_id')
            )
            ->will($this->returnValue($this->select));
        $this->select->expects($this->any())->method('where')
            ->will($this->returnValue($this->select));
        $this->select->expects($this->once())->method('group')
            ->with($this->equalTo('main_table.attribute_id'))
            ->will($this->returnValue($this->select));
        $this->select->expects($this->once())->method('limit')
            ->with($this->equalTo(1))
            ->will($this->returnValue($this->select));

        $this->model->validate($object, $attributeSet);
    }
}
