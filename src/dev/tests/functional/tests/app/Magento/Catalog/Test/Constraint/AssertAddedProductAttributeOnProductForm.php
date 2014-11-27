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

namespace Magento\Catalog\Test\Constraint;

use Mtf\Fixture\InjectableFixture;
use Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;

/**
 * Check attribute on product form.
 */
class AssertAddedProductAttributeOnProductForm extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'high';

    /**
     * Add this attribute to Default attribute Template. Create product and Assert that created attribute
     * is displayed on product form (Products > Inventory > Catalog).
     *
     * @param InjectableFixture $product
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductEdit $productEdit
     * @param CatalogProductAttribute $attribute
     * @param CatalogProductAttribute $productAttributeOriginal
     * @throws \Exception
     * @return void
     */
    public function processAssert(
        InjectableFixture $product,
        CatalogProductIndex $productGrid,
        CatalogProductEdit $productEdit,
        CatalogProductAttribute $attribute,
        CatalogProductAttribute $productAttributeOriginal = null
    ) {
        $filterProduct = [
            'sku' => $product->getSku(),
        ];
        $productGrid->open();
        $productGrid->getProductGrid()->searchAndOpen($filterProduct);

        $catalogProductAttribute = ($productAttributeOriginal !== null)
            ? array_merge($productAttributeOriginal->getData(), $attribute->getData())
            : $attribute->getData();

        \PHPUnit_Framework_Assert::assertTrue(
            $productEdit->getProductForm()->checkAttributeLabel($catalogProductAttribute),
            "Product Attribute is absent on Product form."
        );
    }

    /**
     * Text of Product Attribute is present on the Product form.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product Attribute is present on Product form.';
    }
}
