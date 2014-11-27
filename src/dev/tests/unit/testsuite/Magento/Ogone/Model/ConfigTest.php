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
namespace Magento\Ogone\Model;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    const EXPECTED_VALUE = 'abcdef1234567890';

    /**
     * @var Config
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfig;

    protected function setUp()
    {
        $this->_scopeConfig = $this->getMock('\Magento\Framework\App\Config\ScopeConfigInterface', [], [], '', false);
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $helper->getObject('Magento\Ogone\Model\Config', [
                'scopeConfig' => $this->_scopeConfig
            ]);
    }

    public function testGetShaInCode()
    {
        $this->_scopeConfig->expects($this->any())->method('getValue')->with('payment/ogone/secret_key_in')->will(
            $this->returnValue(self::EXPECTED_VALUE)
        );
        $this->assertEquals(self::EXPECTED_VALUE, $this->_model->getShaInCode());
    }

    public function testGetShaOutCode()
    {
        $this->_scopeConfig->expects($this->any())->method('getValue')->with('payment/ogone/secret_key_out')->will(
            $this->returnValue(self::EXPECTED_VALUE)
        );
        $this->assertEquals(self::EXPECTED_VALUE, $this->_model->getShaOutCode());
    }
}
