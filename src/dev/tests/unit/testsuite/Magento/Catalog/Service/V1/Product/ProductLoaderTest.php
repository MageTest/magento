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

namespace Magento\Catalog\Service\V1\Product;

class ProductLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductLoader
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $factoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var string
     */
    protected $productSku = 'simple-sku';

    protected function setUp()
    {
        $this->factoryMock = $this->getMock('\Magento\Catalog\Model\ProductFactory', ['create'], [], '', false);
        $this->productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $this->model = new ProductLoader($this->factoryMock);
    }

    public function testLoad()
    {
        $this->factoryMock->expects($this->once())->method('create')->will($this->returnValue($this->productMock));
        $this->productMock->expects($this->once())
            ->method('getIdBySku')
            ->with($this->productSku)
            ->will($this->returnValue(1));

        $this->productMock->expects($this->once())->method('load')->with(1);
        $this->assertEquals($this->productMock, $this->model->load($this->productSku));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage There is no product with provided SKU
     */
    public function testLoadWithNonExistedProduct()
    {
        $this->factoryMock->expects($this->once())->method('create')->will($this->returnValue($this->productMock));
        $this->productMock->expects($this->once())
            ->method('getIdBySku')
            ->with($this->productSku)
            ->will($this->returnValue(null));

        $this->productMock->expects($this->never())->method('load');

        $this->assertEquals($this->productMock, $this->model->load($this->productSku));
    }
}
