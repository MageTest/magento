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
namespace Magento\SalesRule\Model\Coupon;

/**
 * Class CodegeneratorTest
 */
class CodegeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SalesRule\Model\Coupon\Codegenerator
     */
    protected $codegenerator;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->codegenerator = $objectManager->getObject('Magento\SalesRule\Model\Coupon\Codegenerator');
    }

    /**
     * Run test generateCode method
     */
    public function testGenerateCode()
    {
        $this->assertNotEmpty($this->codegenerator->generateCode());
    }

    /**
     * Run test getDelimiter method
     */
    public function testGetDelimiter()
    {
        $this->assertNotEmpty($this->codegenerator->getDelimiter());
    }
}
