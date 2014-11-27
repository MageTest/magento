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

namespace Magento\Review\Test\TestCase;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Review\Test\Fixture\ReviewInjectable;
use Magento\Review\Test\Fixture\Rating;
use Magento\Review\Test\Page\Adminhtml\RatingEdit;
use Magento\Review\Test\Page\Adminhtml\RatingIndex;
use Magento\Review\Test\Page\Adminhtml\ReviewEdit;
use Magento\Review\Test\Page\Adminhtml\ReviewIndex;
use Mtf\TestCase\Injectable;

/**
 * Test Creation for Create ProductReviewEntity Backend
 *
 * Test Flow:
 * Preconditions:
 * 1. Simple Product created
 *
 * Steps:
 * 1. Login to backend
 * 2. Navigate to Marketing -> User Content -> Reviews
 * 3. Click the "+" (Add New Review) button
 * 4. Select the product in the Products Grid
 * 5. Fill data according to DataSet
 * 6. Click "Save Review" button
 * 7. Perform Asserts
 *
 * @group Reviews_and_Ratings_(MX)
 * @ZephyrId MAGETWO-26476
 */
class CreateProductReviewBackendEntityTest extends Injectable
{
    /**
     * ReviewIndex page
     *
     * @var ReviewIndex
     */
    protected $reviewIndex;

    /**
     * ReviewEdit page
     *
     * @var ReviewEdit
     */
    protected $reviewEdit;

    /**
     * RatingIndex page
     *
     * @var RatingIndex
     */
    protected $ratingIndex;

    /**
     * RatingEdit page
     *
     * @var RatingEdit
     */
    protected $ratingEdit;

    /**
     * @var Rating
     */
    protected $productRating;

    /**
     * Review fixture
     *
     * @var ReviewInjectable
     */
    protected $review;

    /**
     * Inject pages into test
     *
     * @param ReviewIndex $reviewIndex
     * @param ReviewEdit $reviewEdit
     * @param RatingIndex $ratingIndex
     * @param RatingEdit $ratingEdit
     * @return void
     */
    public function __inject(
        ReviewIndex $reviewIndex,
        ReviewEdit $reviewEdit,
        RatingIndex $ratingIndex,
        RatingEdit $ratingEdit
    ) {
        $this->reviewIndex = $reviewIndex;
        $this->reviewEdit = $reviewEdit;
        $this->ratingIndex = $ratingIndex;
        $this->ratingEdit = $ratingEdit;
    }

    /**
     * Run Create Product Review Entity Backend Test
     *
     * @param ReviewInjectable $review
     * @return array
     */
    public function test(ReviewInjectable $review)
    {
        // Precondition:
        $product = $review->getDataFieldConfig('entity_id')['source']->getEntity();
        $filter = ['id' => $product->getId()];
        $this->review = $review;

        // Steps:
        $this->reviewIndex->open();
        $this->reviewIndex->getReviewActions()->addNew();
        $this->reviewEdit->getProductGrid()->searchAndOpen($filter);
        $this->reviewEdit->getReviewForm()->fill($this->review);
        $this->reviewEdit->getPageActions()->save();

        return ['product' => $product];
    }

    /**
     * Clear data after test
     *
     * @return void
     */
    public function tearDown()
    {
        $this->ratingIndex->open();
        if ($this->review instanceof ReviewInjectable) {
            foreach ($this->review->getRatings() as $rating) {
                $this->ratingIndex->getRatingGrid()->searchAndOpen(['rating_code' => $rating['title']]);
                $this->ratingEdit->getPageActions()->delete();
            }
        }
    }
}
