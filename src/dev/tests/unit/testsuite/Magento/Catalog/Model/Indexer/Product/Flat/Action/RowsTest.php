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
namespace Magento\Catalog\Model\Indexer\Product\Flat\Action;

use Magento\TestFramework\Helper\ObjectManager;

class RowsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Action\Rows
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_store;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_productIndexerHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_connection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_flatItemWriter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_flatItemEraser;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_flatTableBuilder;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->_connection = $this->getMock('\Magento\Framework\DB\Adapter\AdapterInterface');
        $this->_resource = $this->getMock('Magento\Framework\App\Resource', array(), array(), '', false);
        $this->_resource->expects($this->any())->method('getConnection')
            ->with('default')
            ->will($this->returnValue($this->_connection));
        $this->_storeManager = $this->getMock('Magento\Framework\StoreManagerInterface');
        $this->_store = $this->getMock('Magento\Store\Model\Store', array(), array(), '', false);
        $this->_store->expects($this->any())->method('getId')->will($this->returnValue('store_id_1'));
        $this->_storeManager->expects($this->any())->method('getStores')->will(
            $this->returnValue(array($this->_store))
        );
        $this->_productIndexerHelper = $this->getMock(
            'Magento\Catalog\Helper\Product\Flat\Indexer', array(), array(), '', false
        );
        $this->_flatItemEraser = $this->getMock(
            '\Magento\Catalog\Model\Indexer\Product\Flat\Action\Eraser', array(), array(), '', false
        );
        $this->_flatItemWriter = $this->getMock(
            '\Magento\Catalog\Model\Indexer\Product\Flat\Action\Indexer', array(), array(), '', false
        );
        $this->_flatTableBuilder = $this->getMock(
            '\Magento\Catalog\Model\Indexer\Product\Flat\FlatTableBuilder', array(), array(), '', false
        );

        $this->_model = $objectManager->getObject('Magento\Catalog\Model\Indexer\Product\Flat\Action\Rows', array(
            'resource' => $this->_resource,
            'storeManager' => $this->_storeManager,
            'productHelper' => $this->_productIndexerHelper,
            'flatItemEraser' => $this->_flatItemEraser,
            'flatItemWriter' => $this->_flatItemWriter,
            'flatTableBuilder' => $this->_flatTableBuilder
        ));
    }
    /**
     * @expectedException \Magento\Framework\Model\Exception
     * @expectedExceptionMessage Bad value was supplied.
     */
    public function testEmptyIds()
    {
        $this->_model->execute(null);
    }

    public function testExecuteWithNonExistingFlatTablesCreatesTables()
    {
        $this->_productIndexerHelper->expects($this->any())->method('getFlatTableName')
            ->will($this->returnValue('store_flat_table'));
        $this->_connection->expects($this->any())->method('isTableExists')->with('store_flat_table')
            ->will($this->returnValue(false));
        $this->_flatItemEraser->expects($this->never())->method('removeDeletedProducts');
        $this->_flatTableBuilder->expects($this->once())->method('build')->with('store_id_1', array(1, 2));
        $this->_model->execute(array(1, 2));
    }

    public function testExecuteWithExistingFlatTablesCreatesTables()
    {
        $this->_productIndexerHelper->expects($this->any())->method('getFlatTableName')
            ->will($this->returnValue('store_flat_table'));
        $this->_connection->expects($this->any())->method('isTableExists')->with('store_flat_table')
            ->will($this->returnValue(true));
        $this->_flatItemEraser->expects($this->once())->method('removeDeletedProducts');
        $this->_flatTableBuilder->expects($this->once())->method('build')->with('store_id_1', array(1, 2));
        $this->_model->execute(array(1, 2));
    }
}
