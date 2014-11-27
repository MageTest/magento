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

namespace Magento\Search\Block;

use PHPUnit_Framework_MockObject_MockObject as MockObject;

class SearchDataTest extends \PHPUnit_Framework_TestCase
{

    /** @var  \Magento\Framework\View\Element\Template\Context|MockObject */
    private $context;

    /**
     * @var \Magento\Search\Model\QueryFactoryInterface|MockObject
     */
    private $queryFactory;

    /**
     * @var \Magento\Search\Model\Query|MockObject
     */
    private $searchQuery;

    /**
     * @var \Magento\Search\Model\SearchDataProviderInterface|MockObject
     */
    private $dataProvider;

    /**
     * @var \Magento\Search\Block\SearchData
     */
    private $block;

    protected function setUp()
    {
        $this->dataProvider = $this->getMockBuilder('\Magento\Search\Model\SearchDataProviderInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getSearchData', 'isCountResultsEnabled'])
            ->getMockForAbstractClass();

        $this->searchQuery = $this->getMockBuilder('\Magento\Search\Model\QueryInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getQueryText'])
            ->getMockForAbstractClass();
        $this->queryFactory = $this->getMockBuilder('\Magento\Search\Model\QueryFactoryInterface')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        $this->queryFactory->expects($this->once())
            ->method('get')
            ->will($this->returnValue($this->searchQuery));
        $this->context = $this->getMockBuilder('\Magento\Framework\View\Element\Template\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->block = $this->getMockBuilder('\Magento\Search\Block\SearchData')->setConstructorArgs(
            [
                $this->context,
                $this->dataProvider,
                $this->queryFactory,
                'Test Title',
                array(),
            ]
        )
            ->setMethods(['getUrl'])
            ->getMockForAbstractClass();
    }

    public function testGetSuggestions()
    {
        $value = [1, 2, 3, 100500,];

        $this->dataProvider->expects($this->once())
            ->method('getSearchData')
            ->with($this->searchQuery)
            ->will($this->returnValue($value));
        $actualValue = $this->block->getSearchData();
        $this->assertEquals($value, $actualValue);
    }

    public function testGetLink()
    {
        $searchQuery = 'Some test search query';
        $expectedResult = '?q=Some+test+search+query';
        $actualResult = $this->block->getLink($searchQuery);
        $this->assertEquals($expectedResult, $actualResult);
    }

    public function testIsCountResultsEnabled()
    {
        $value = 'qwertyasdfzxcv';
        $this->dataProvider->expects($this->once())
            ->method('isCountResultsEnabled')
            ->will($this->returnValue($value));
        $this->assertEquals($value, $this->block->isCountResultsEnabled());
    }
}
