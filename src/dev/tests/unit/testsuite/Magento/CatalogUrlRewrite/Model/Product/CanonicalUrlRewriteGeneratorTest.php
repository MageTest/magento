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
namespace Magento\CatalogUrlRewrite\Model\Product;

use Magento\TestFramework\Helper\ObjectManager;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;

class CanonicalUrlRewriteGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\CatalogUrlRewrite\Model\Product\CanonicalUrlRewriteGenerator */
    protected $canonicalUrlRewriteGenerator;

    /** @var \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator|\PHPUnit_Framework_MockObject_MockObject */
    protected $productUrlPathGenerator;

    /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject */
    protected $product;

    /** @var \Magento\CatalogUrlRewrite\Model\ObjectRegistry|\PHPUnit_Framework_MockObject_MockObject */
    protected $categoryRegistry;

    /** @var \Magento\UrlRewrite\Service\V1\Data\UrlRewriteBuilder|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlRewriteBuilder;

    /** @var \Magento\UrlRewrite\Service\V1\Data\UrlRewrite|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlRewrite;

    protected function setUp()
    {
        $this->urlRewriteBuilder = $this->getMockBuilder('Magento\UrlRewrite\Service\V1\Data\UrlRewriteBuilder')
            ->disableOriginalConstructor()->getMock();
        $this->urlRewrite = $this->getMockBuilder('Magento\UrlRewrite\Service\V1\Data\UrlRewrite')
            ->disableOriginalConstructor()->getMock();
        $this->product = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()->getMock();
        $this->categoryRegistry = $this->getMockBuilder('\Magento\CatalogUrlRewrite\Model\ObjectRegistry')
            ->disableOriginalConstructor()->getMock();
        $this->productUrlPathGenerator = $this->getMockBuilder(
            'Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator'
        )->disableOriginalConstructor()->getMock();
        $this->canonicalUrlRewriteGenerator = (new ObjectManager($this))->getObject(
            'Magento\CatalogUrlRewrite\Model\Product\CanonicalUrlRewriteGenerator',
            [
                'productUrlPathGenerator' => $this->productUrlPathGenerator,
                'urlRewriteBuilder' => $this->urlRewriteBuilder
            ]
        );
    }

    public function testGenerate()
    {
        $requestPath = 'simple-product.html';
        $storeId = 10;
        $productId = 'product_id';
        $targetPath = 'catalog/product/view/id/' . $productId;

        $this->product->expects($this->any())->method('getId')->will($this->returnValue($productId));
        $this->productUrlPathGenerator->expects($this->any())->method('getUrlPathWithSuffix')
            ->will($this->returnValue($requestPath));
        $this->productUrlPathGenerator->expects($this->any())->method('getCanonicalUrlPath')
            ->will($this->returnValue($targetPath));
        $this->categoryRegistry->expects($this->any())->method('getList')->will($this->returnValue([]));

        $this->urlRewriteBuilder->expects($this->any())->method('setStoreId')->with($storeId)
            ->will($this->returnSelf());
        $this->urlRewriteBuilder->expects($this->any())->method('setEntityId')->with($productId)
            ->will($this->returnSelf());
        $this->urlRewriteBuilder->expects($this->any())->method('setEntityType')
            ->with(ProductUrlRewriteGenerator::ENTITY_TYPE)->will($this->returnSelf());
        $this->urlRewriteBuilder->expects($this->any())->method('setRequestPath')->with($requestPath)
            ->will($this->returnSelf());
        $this->urlRewriteBuilder->expects($this->any())->method('setTargetPath')->with($targetPath)
            ->will($this->returnSelf());
        $this->urlRewriteBuilder->expects($this->any())->method('create')->will($this->returnValue($this->urlRewrite));
        $this->assertEquals(
            [
                $this->urlRewrite,
            ],
            $this->canonicalUrlRewriteGenerator->generate($storeId, $this->product)
        );
    }
}
