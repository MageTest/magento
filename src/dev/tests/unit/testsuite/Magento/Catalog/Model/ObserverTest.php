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

namespace Magento\Catalog\Model;

use Magento\TestFramework\Helper\ObjectManager;

class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Observer
     */
    protected $_observer;

    /**
     * @var \Magento\Catalog\Helper\Category
     */
    protected $_catalogCategory;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $_category;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $_childrenCategory;

    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Flat\State
     */
    protected $_categoryFlatState;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    public function setUp()
    {
        $this->_catalogCategory = $this->getMock(
            '\Magento\Catalog\Helper\Category',
            ['getStoreCategories', 'getCategoryUrl'],
            [],
            '',
            false
        );

        $this->_categoryFlatState = $this->getMock(
            '\Magento\Catalog\Model\Indexer\Category\Flat\State',
            ['isFlatEnabled'],
            [],
            '',
            false
        );

        $this->_storeManager = $this->getMockBuilder('Magento\Framework\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_observer = (new ObjectManager($this))->getObject('\Magento\Catalog\Model\Observer', [
            'urlFactory' => $this->_getCleanMock('\Magento\Catalog\Model\UrlFactory'),
            'categoryResource' => $this->_getCleanMock('\Magento\Catalog\Model\Resource\Category'),
            'catalogProduct' => $this->_getCleanMock('\Magento\Catalog\Model\Resource\Product'),
            'storeManager' => $this->_storeManager,
            'catalogLayer' => $this->_getCleanMock('\Magento\Catalog\Model\Layer\Category'),
            'indexIndexer' => $this->_getCleanMock('\Magento\Index\Model\Indexer'),
            'catalogCategory' => $this->_catalogCategory,
            'catalogData' => $this->_getCleanMock('\Magento\Catalog\Helper\Data'),
            'categoryFlatState' => $this->_categoryFlatState,
            'productResourceFactory' => $this->_getCleanMock('\Magento\Catalog\Model\Resource\ProductFactory'),
        ]);
    }

    /**
     * Get clean mock by class name
     *
     * @param string $className
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getCleanMock($className)
    {
        return $this->getMock($className, [], [], '', false);
    }

    protected function _preparationData()
    {
        $this->_childrenCategory = $this->getMock(
            '\Magento\Catalog\Model\Category',
            ['getIsActive', '__wakeup'],
            [],
            '',
            false
        );
        $this->_childrenCategory->expects($this->once())
            ->method('getIsActive')
            ->will($this->returnValue(false));

        $this->_category = $this->getMock(
            '\Magento\Catalog\Model\Category',
            ['getIsActive', '__wakeup', 'getName', 'getChildren', 'getUseFlatResource', 'getChildrenNodes'],
            [],
            '',
            false
        );
        $this->_category->expects($this->once())
            ->method('getIsActive')
            ->will($this->returnValue(true));
        $this->_category->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('Name'));

        $this->_catalogCategory->expects($this->once())
            ->method('getStoreCategories')
            ->will($this->returnValue(array($this->_category)));
        $this->_catalogCategory->expects($this->once())
            ->method('getCategoryUrl')
            ->will($this->returnValue('url'));

        $blockMock = $this->_getCleanMock('\Magento\Theme\Block\Html\Topmenu');

        $treeMock = $this->_getCleanMock('\Magento\Framework\Data\Tree');

        $menuMock = $this->getMock('\Magento\Framework\Data\Tree\Node', ['getTree', 'addChild'], [], '', false);
        $menuMock->expects($this->once())
            ->method('getTree')
            ->will($this->returnValue($treeMock));

        $eventMock = $this->getMock('\Magento\Framework\Event', ['getBlock'], [], '', false);
        $eventMock->expects($this->once())
            ->method('getBlock')
            ->will($this->returnValue($blockMock));

        $observerMock = $this->getMock('\Magento\Framework\Event\Observer', ['getEvent', 'getMenu'], [], '', false);
        $observerMock->expects($this->once())
            ->method('getEvent')
            ->will($this->returnValue($eventMock));
        $observerMock->expects($this->once())
            ->method('getMenu')
            ->will($this->returnValue($menuMock));

        return $observerMock;
    }

    public function testAddCatalogToTopMenuItemsWithoutFlat()
    {
        $observer = $this->_preparationData();

        $this->_category->expects($this->once())
            ->method('getChildren')
            ->will($this->returnValue(array($this->_childrenCategory)));

        $this->_observer->addCatalogToTopmenuItems($observer);
    }

    public function testAddCatalogToTopMenuItemsWithFlat()
    {
        $observer = $this->_preparationData();

        $this->_category->expects($this->once())
            ->method('getChildrenNodes')
            ->will($this->returnValue(array($this->_childrenCategory)));

        $this->_category->expects($this->once())
            ->method('getUseFlatResource')
            ->will($this->returnValue(true));

        $this->_categoryFlatState->expects($this->once())
            ->method('isFlatEnabled')
            ->will($this->returnValue(true));

        $this->_observer->addCatalogToTopmenuItems($observer);
    }
}
