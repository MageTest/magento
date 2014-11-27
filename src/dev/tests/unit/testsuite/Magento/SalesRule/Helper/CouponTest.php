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

namespace Magento\SalesRule\Helper;

class CouponTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SalesRule\Helper\Coupon
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Config
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    protected $context;

    /**
     * @var array
     */
    protected $couponParameters;

    /**
     * @var string
     */
    protected $separator = '|';

    public function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->scopeConfig = $this->getMock('Magento\Framework\App\Config', [], [], '', false);
        $this->context = $this->getMock('Magento\Framework\App\Helper\Context', [], [], '', false);
        $this->couponParameters = [
            'separator' => $this->separator,
            'charset' => [
                'format' => 'abc'
            ]
        ];

        $this->helper = $objectManager->getObject(
            'Magento\SalesRule\Helper\Coupon',
            [
                'context' => $this->context,
                'scopeConfig' => $this->scopeConfig,
                'couponParameters' => $this->couponParameters
            ]
        );
    }

    public function testGetFormatsList()
    {
        $helper = $this->helper;
        $this->assertArrayHasKey(
            $helper::COUPON_FORMAT_ALPHABETICAL,
            $helper->getFormatsList(),
            'The returned list should contain COUPON_FORMAT_ALPHABETICAL constant value as a key'
        );
        $this->assertArrayHasKey(
            $helper::COUPON_FORMAT_ALPHANUMERIC,
            $helper->getFormatsList(),
            'The returned list should contain COUPON_FORMAT_ALPHANUMERIC constant value as a key'
        );
        $this->assertArrayHasKey(
            $helper::COUPON_FORMAT_NUMERIC,
            $helper->getFormatsList(),
            'The returned list should contain COUPON_FORMAT_NUMERIC constant value as a key'
        );
    }

    public function testGetDefaultLength()
    {
        $helper = $this->helper;
        $defaultLength = 100;
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with($helper::XML_PATH_SALES_RULE_COUPON_LENGTH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->will($this->returnValue($defaultLength));

        $this->assertEquals($defaultLength, $helper->getDefaultLength());
    }

    public function testGetDefaultFormat()
    {
        $helper = $this->helper;
        $defaultFormat = 'format';
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with($helper::XML_PATH_SALES_RULE_COUPON_FORMAT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->will($this->returnValue($defaultFormat));

        $this->assertEquals($defaultFormat, $helper->getDefaultFormat());
    }

    public function testGetDefaultPrefix()
    {
        $helper = $this->helper;
        $defaultPrefix = 'prefix';
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with($helper::XML_PATH_SALES_RULE_COUPON_PREFIX, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->will($this->returnValue($defaultPrefix));

        $this->assertEquals($defaultPrefix, $helper->getDefaultPrefix());
    }

    public function testGetDefaultSuffix()
    {
        $helper = $this->helper;
        $defaultSuffix = 'suffix';
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with($helper::XML_PATH_SALES_RULE_COUPON_SUFFIX, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->will($this->returnValue($defaultSuffix));

        $this->assertEquals($defaultSuffix, $helper->getDefaultSuffix());
    }

    public function testGetDefaultDashInterval()
    {
        $helper = $this->helper;
        $defaultDashInterval = 4;
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with($helper::XML_PATH_SALES_RULE_COUPON_DASH_INTERVAL, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->will($this->returnValue($defaultDashInterval));

        $this->assertEquals($defaultDashInterval, $helper->getDefaultDashInterval());
    }

    public function testGetCharset()
    {
        $format = 'format';
        $expected = ['a', 'b', 'c'];

        $this->assertEquals($expected, $this->helper->getCharset($format));
    }

    public function testGetSeparator()
    {
        $this->assertEquals($this->separator, $this->helper->getCodeSeparator());
    }
}
