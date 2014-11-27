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
namespace Magento\CatalogRule\Model\Indexer;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class RuleProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\Indexer\IndexBuilder
     */
    protected $indexBuilder;

    /**
     * @var \Magento\CatalogRule\Model\Resource\Rule
     */
    protected $resourceRule;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    protected function setUp()
    {
        $this->indexBuilder = Bootstrap::getObjectManager()->get('Magento\CatalogRule\Model\Indexer\IndexBuilder');
        $this->resourceRule = Bootstrap::getObjectManager()->get('Magento\CatalogRule\Model\Resource\Rule');
        $this->product = Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
    }

    /**
     * @magentoDataFixture Magento/CatalogRule/_files/attribute.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testReindexAfterRuleCreation()
    {
        $this->product->load(1)->setData('test_attribute', 'test_attribute_value')->save();
        $this->assertFalse($this->resourceRule->getRulePrice(true, 1, 1, 1));

        $this->saveRule();
        // apply all rules
        $this->indexBuilder->reindexFull();

        $this->assertEquals(9.8, $this->resourceRule->getRulePrice(true, 1, 1, 1));
    }

    protected function saveRule()
    {
        require 'Magento/CatalogRule/_files/rule_by_attribute.php';
    }
}
