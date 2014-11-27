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

namespace Magento\Framework\Search\Adapter\Mysql;

use Magento\Framework\Search\Adapter\Mysql\Dimensions as DimensionsBuilder;
use Magento\TestFramework\Helper\ObjectManager;

class DimensionsTest extends \PHPUnit_Framework_TestCase
{

    /** @var \Magento\TestFramework\Helper\ObjectManager */
    private $objectManager;

    /** @var \Magento\Framework\App\ScopeInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $scope;

    /** @var \Magento\Framework\App\ScopeResolverInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $scopeResolver;

    /** @var \Magento\Framework\Search\Request\Dimension|\PHPUnit_Framework_MockObject_MockObject */
    private $dimension;

    /** @var DimensionsBuilder */
    private $builder;

    /** @var  \Magento\Framework\Search\Adapter\Mysql\ConditionManager|\PHPUnit_Framework_MockObject_MockObject */
    private $conditionManager;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->scope = $this->getMockBuilder('\Magento\Framework\App\ScopeInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();

        $this->scopeResolver = $this->getMockBuilder('\Magento\Framework\App\ScopeResolverInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getScope'])
            ->getMockForAbstractClass();

        $this->dimension = $this->getMockBuilder('\Magento\Framework\Search\Request\Dimension')
            ->disableOriginalConstructor()
            ->setMethods(['getName', 'getValue'])
            ->getMock();

        $this->conditionManager = $this->getMockBuilder('\Magento\Framework\Search\Adapter\Mysql\ConditionManager')
            ->disableOriginalConstructor()
            ->setMethods(['generateCondition'])
            ->getMock();
        $this->conditionManager->expects($this->any())
            ->method('generateCondition')
            ->will(
                $this->returnCallback(
                    function ($field, $operator, $value) {
                        return sprintf('`%s` %s `%s`', $field, $operator, $value);
                    }
                )
            );

        $this->builder = $this->objectManager->getObject(
            '\Magento\Framework\Search\Adapter\Mysql\Dimensions',
            [
                'conditionManager' => $this->conditionManager,
                'scopeResolver' => $this->scopeResolver
            ]
        );
    }

    public function testBuildDimensionWithCustomScope()
    {
        $name = 'customScopeName';
        $value = 'customScopeId';

        $this->dimension->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($name));
        $this->dimension->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($value));

        $this->scope->expects($this->never())
            ->method('getId');

        $this->scopeResolver->expects($this->never())
            ->method('getScope');

        $query = $this->builder->build($this->dimension);
        $this->assertEquals(
            sprintf('`%s` = `%s`', $name, $value),
            $query
        );
    }

    public function testBuildDimensionWithDefaultScope()
    {
        $name = 'scope';
        $value = \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT;
        $scopeId = -123456;

        $this->dimension->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($name));
        $this->dimension->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($value));

        $this->scope->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($scopeId));

        $this->scopeResolver->expects($this->once())
            ->method('getScope')
            ->with($value)
            ->will($this->returnValue($this->scope));

        $query = $this->builder->build($this->dimension);
        $this->assertEquals(
            sprintf('`%s` = `%s`', \Magento\Framework\Search\Adapter\Mysql\Dimensions::STORE_FIELD_NAME, $scopeId),
            $query
        );
    }
}
