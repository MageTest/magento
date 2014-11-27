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

use Magento\Review\Test\Page\Adminhtml\ReviewIndex;
use Magento\Review\Test\Page\Adminhtml\ReviewEdit;
use Magento\Review\Test\Page\Adminhtml\RatingIndex;
use Magento\Review\Test\Page\Adminhtml\RatingEdit;
use Magento\Review\Test\Fixture\ReviewInjectable;
use Mtf\TestCase\Injectable;

/**
 * Test Creation for Update Frontend Product Review
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Create simple product
 * 2. Create custom rating type
 * 3. Create review with rating
 *
 * Steps:
 * 1. Open backend
 * 2. Go to Marketing> Reviews
 * 3. Open created review
 * 4. Fill data according to dataset
 * 5. Click "Submit review"
 * 6. Perform all assertions
 *
 * @group Reviews_and_Ratings_(MX)
 * @ZephyrId MAGETWO-25604
 */
class UpdateProductReviewEntityTest extends Injectable
{
    /**
     * Backend review grid page
     *
     * @var ReviewIndex
     */
    protected $reviewIndex;

    /**
     * Backend review edit page
     *
     * @var ReviewEdit
     */
    protected $reviewEdit;

    /**
     * Backend rating grid page
     *
     * @var RatingIndex
     */
    protected $ratingIndex;

    /**
     * Backend rating edit page
     *
     * @var RatingEdit
     */
    protected $ratingEdit;

    /**
     * Fixture review
     *
     * @var ReviewInjectable
     */
    protected $review;

    /**
     * Injection data
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
     * Run create frontend product rating test
     *
     * @param ReviewInjectable $reviewInitial
     * @param ReviewInjectable $review
     * @return array
     */
    public function test(ReviewInjectable $reviewInitial, ReviewInjectable $review)
    {
        // Precondition
        $reviewInitial->persist();

        // Prepare for tear down
        $this->review = $reviewInitial;

        // Steps
        $this->reviewIndex->open();
        $this->reviewIndex->getReviewGrid()->searchAndOpen(['review_id' => $reviewInitial->getReviewId()]);
        $this->reviewEdit->getReviewForm()->fill($review);
        $this->reviewEdit->getPageActions()->save();

        // Prepare data for asserts
        $product = $reviewInitial->getDataFieldConfig('entity_id')['source']->getEntity();

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
