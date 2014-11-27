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
namespace Magento\Eav\Model\Entity\Attribute\Source;

use Magento\TestFramework\Helper\ObjectManager;

class BooleanTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\Entity\Attribute\Source\Boolean
     */
    protected $_model;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->_model = $objectManager->getObject('Magento\Eav\Model\Entity\Attribute\Source\Boolean');
    }

    public function testGetFlatColumns()
    {
        $abstractAttributeMock = $this->getMock(
            '\Magento\Eav\Model\Entity\Attribute\AbstractAttribute',
            array('getAttributeCode', '__wakeup'),
            array(),
            '',
            false
        );

        $abstractAttributeMock->expects($this->any())->method('getAttributeCode')->will($this->returnValue('code'));

        $this->_model->setAttribute($abstractAttributeMock);

        $flatColumns = $this->_model->getFlatColumns();

        $this->assertTrue(is_array($flatColumns), 'FlatColumns must be an array value');
        $this->assertTrue(!empty($flatColumns), 'FlatColumns must be not empty');
        foreach ($flatColumns as $result) {
            $this->assertArrayHasKey('unsigned', $result, 'FlatColumns must have "unsigned" column');
            $this->assertArrayHasKey('default', $result, 'FlatColumns must have "default" column');
            $this->assertArrayHasKey('extra', $result, 'FlatColumns must have "extra" column');
            $this->assertArrayHasKey('type', $result, 'FlatColumns must have "type" column');
            $this->assertArrayHasKey('nullable', $result, 'FlatColumns must have "nullable" column');
            $this->assertArrayHasKey('comment', $result, 'FlatColumns must have "comment" column');
            $this->assertArrayHasKey('length', $result, 'FlatColumns must have "length" column');
        }
    }

    /**
     * @covers \Magento\Eav\Model\Entity\Attribute\Source\Boolean::addValueSortToCollection
     *
     * @dataProvider addValueSortToCollectionDataProvider
     * @param string $direction
     * @param bool $isScopeGlobal
     * @param array $expectedJoinCondition
     * @param string $expectedOrder
     */
    public function testAddValueSortToCollection(
        $direction, $isScopeGlobal, $expectedJoinCondition, $expectedOrder
    ) {
        $attributeMock = $this->getAttributeMock();
        $attributeMock->expects($this->any())->method('isScopeGlobal')->will($this->returnValue($isScopeGlobal));

        $selectMock = $this->getMock('\Magento\Framework\DB\Select', [], [], '', false);

        $collectionMock = $this->getCollectionMock();
        $collectionMock->expects($this->any())->method('getSelect')->will($this->returnValue($selectMock));

        foreach ($expectedJoinCondition as $step => $data) {
            $selectMock->expects($this->at($step))->method('joinLeft')
                ->with($data['requisites'], $data['condition'], [])->will($this->returnSelf());
        }

        $selectMock->expects($this->once())->method('order')->with($expectedOrder);

        $this->_model->setAttribute($attributeMock);
        $this->_model->addValueSortToCollection($collectionMock, $direction);
    }

    /**
     * @return array
     */
    public function addValueSortToCollectionDataProvider()
    {
        return  [
            [
                'direction' => 'ASC',
                'isScopeGlobal' => false,
                'expectedJoinCondition' => [
                    0 => [
                        'requisites' => ['code_t1' => "table"],
                        'condition' =>
                            "e.entity_id=code_t1.entity_id AND code_t1.attribute_id='123' AND code_t1.store_id='0'",
                    ],
                    1 => [
                        'requisites' => ['code_t2' => "table"],
                        'condition' =>
                            "e.entity_id=code_t2.entity_id AND code_t2.attribute_id='123' AND code_t2.store_id='12'"
                    ]
                ],
                'expectedOrder' => 'IF(code_t2.value_id > 0, code_t2.value, code_t1.value) ASC',
            ],
            [
                'direction' => 'DESC',
                'isScopeGlobal' => false,
                'expectedJoinCondition' => [
                    0 => [
                        'requisites' => ['code_t1' => "table"],
                        'condition' =>
                            "e.entity_id=code_t1.entity_id AND code_t1.attribute_id='123' AND code_t1.store_id='0'",
                    ],
                    1 => [
                        'requisites' => ['code_t2' => "table"],
                        'condition' =>
                            "e.entity_id=code_t2.entity_id AND code_t2.attribute_id='123' AND code_t2.store_id='12'"
                    ]
                ],
                'expectedOrder' => 'IF(code_t2.value_id > 0, code_t2.value, code_t1.value) DESC',
            ],
            [
                'direction' => 'DESC',
                'isScopeGlobal' => true,
                'expectedJoinCondition' => [
                    0 => [
                        'requisites' => ['code_t' => "table"],
                        'condition' =>
                            "e.entity_id=code_t.entity_id AND code_t.attribute_id='123' AND code_t.store_id='0'",
                    ]
                ],
                'expectedOrder' => 'code_t.value DESC',
            ],
            [
                'direction' => 'ASC',
                'isScopeGlobal' => true,
                'expectedJoinCondition' => [
                    0 => [
                        'requisites' => ['code_t' => "table"],
                        'condition' =>
                            "e.entity_id=code_t.entity_id AND code_t.attribute_id='123' AND code_t.store_id='0'",
                    ]
                ],
                'expectedOrder' => 'code_t.value ASC',
            ],
        ];
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getCollectionMock()
    {
        $collectionMethods = ['getSelect', 'getStoreId', 'getConnection'];
        $collectionMock = $this->getMock(
            '\Magento\Eav\Model\Entity\Collection\AbstractCollection', $collectionMethods, [], '', false
        );

        $connectionMock = $this->getMock('\Magento\Framework\DB\Adapter\Pdo\Mysql', ['method'], [], '', false);

        $collectionMock->expects($this->any())->method('getConnection')->will($this->returnValue($connectionMock));
        $collectionMock->expects($this->any())->method('getStoreId')->will($this->returnValue('12'));

        return $collectionMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getAttributeMock()
    {
        $attributeMockMethods = ['getAttributeCode', 'getId', 'getBackend', 'isScopeGlobal', '__wakeup'];
        $attributeMock = $this->getMock(
            '\Magento\Eav\Model\Entity\Attribute\AbstractAttribute', $attributeMockMethods, [], '', false
        );
        $backendMock = $this->getMock('\Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend', [], [], '', false);

        $attributeMock->expects($this->any())->method('getAttributeCode')->will($this->returnValue('code'));
        $attributeMock->expects($this->any())->method('getId')->will($this->returnValue('123'));
        $attributeMock->expects($this->any())->method('getBackend')->will($this->returnValue($backendMock));
        $backendMock->expects($this->any())->method('getTable')->will($this->returnValue('table'));

        return $attributeMock;
    }
}
