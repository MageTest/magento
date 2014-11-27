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

use Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Fixture\CatalogAttributeSet;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductSetIndex;

/**
 * Class AssertProductTemplateNotInGrid
 * Assert that Product Template absence on grid
 */
class AssertProductTemplateNotInGrid extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert that product template is not displayed in Product Templates grid
     *
     * @param CatalogProductSetIndex $productSetPage
     * @param CatalogAttributeSet $productTemplate
     * @return void
     */
    public function processAssert(CatalogProductSetIndex $productSetPage, CatalogAttributeSet $productTemplate)
    {
        $filterAttributeSet = [
            'set_name' => $productTemplate->getAttributeSetName(),
        ];

        $productSetPage->open();
        \PHPUnit_Framework_Assert::assertFalse(
            $productSetPage->getGrid()->isRowVisible($filterAttributeSet),
            'Attribute Set with name "' . $filterAttributeSet['set_name'] . '" is present in Product Template grid.'
        );
    }

    /**
     * Text absent new product template in grid
     *
     * @return string
     */
    public function toString()
    {
        return 'Product template is absent in Product Templates grid';
    }
}
