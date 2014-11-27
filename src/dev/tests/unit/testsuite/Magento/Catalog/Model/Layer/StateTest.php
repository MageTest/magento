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
namespace Magento\Catalog\Model\Layer;

use Magento\TestFramework\Helper\ObjectManager;

class StateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Layer\State
     */
    private $model;

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    private $item;

    protected function setUp()
    {
        $this->item = $this->getMockBuilder('Magento\Catalog\Model\Layer\Filter\Item')
            ->disableOriginalConstructor()
            ->getMock();

        $helper = new ObjectManager($this);
        $this->model = $helper->getObject('Magento\Catalog\Model\Layer\State');
    }

    /**
     * @expectedException \Magento\Framework\Model\Exception
     */
    public function testSetFiltersException()
    {
        $this->model->setFilters($this->item);
    }

    public function testSetFilters()
    {
        $expect = [$this->item];

        $this->model->setFilters($expect);
        $this->assertEquals($expect, $this->model->getFilters());
    }

    public function testAddFilter()
    {
        $expect = [$this->item];

        $this->model->addFilter($this->item);

        $this->assertEquals($expect, $this->model->getFilters());
    }
} 