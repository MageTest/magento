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

use Mtf\Fixture\FixtureInterface;
use Magento\Cms\Test\Page\CmsIndex;
use Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Fixture\CatalogCategory;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;

/**
 * Class AssertProductInCategory
 */
class AssertProductInCategory extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Checking the product in the page of its price
     *
     * @param CatalogCategoryView $catalogCategoryView
     * @param CmsIndex $cmsIndex
     * @param FixtureInterface $product
     * @param CatalogCategory $category
     * @return void
     */
    public function processAssert(
        CatalogCategoryView $catalogCategoryView,
        CmsIndex $cmsIndex,
        FixtureInterface $product,
        CatalogCategory $category
    ) {
        // Open category view page and check visible product
        $categoryName = $category->getName();
        if ($product->hasData('category_ids')) {
            $categoryIds = $product->getCategoryIds();
            $categoryName = is_array($categoryIds) ? reset($categoryIds) : $categoryName;
        }
        $cmsIndex->open();
        $cmsIndex->getTopmenu()->selectCategoryByName($categoryName);

        $isProductVisible = $catalogCategoryView->getListProductBlock()->isProductVisible($product->getName());
        while (!$isProductVisible && $catalogCategoryView->getBottomToolbar()->nextPage()) {
            $isProductVisible = $catalogCategoryView->getListProductBlock()->isProductVisible($product->getName());
        }

        \PHPUnit_Framework_Assert::assertTrue(
            $isProductVisible,
            'Product is absent on category page.'
        );

        //Process price asserts
        $this->assertPrice($product, $catalogCategoryView);
    }

    /**
     * Verify product price on category view page
     *
     * @param FixtureInterface $product
     * @param CatalogCategoryView $catalogCategoryView
     * @return void
     */
    protected function assertPrice(FixtureInterface $product, CatalogCategoryView $catalogCategoryView)
    {
        $price = $catalogCategoryView->getListProductBlock()->getProductPriceBlock($product->getName())
            ->getRegularPrice();

        \PHPUnit_Framework_Assert::assertEquals(
            number_format($product->getPrice(), 2),
            $price,
            'Product regular price on category page is not correct.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Product price on category page correct.';
    }
}
