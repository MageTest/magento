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

use Magento\Review\Test\Page\Adminhtml\RatingIndex;
use Magento\Review\Test\Page\Adminhtml\RatingNew;
use Magento\Review\Test\Page\Adminhtml\RatingEdit;
use Magento\Review\Test\Fixture\Rating;
use Mtf\Fixture\FixtureFactory;
use Mtf\TestCase\Injectable;

/**
 * Test Creation for Create Backend Product Rating
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Create simple product
 *
 * Steps:
 * 1. Login to backend
 * 2. Navigate Stores->Attributes->Rating
 * 3. Add New Rating
 * 4. Fill data according to dataset
 * 5. Save Rating
 * 6. Perform asserts
 *
 * @group Reviews_and_Ratings_(MX)
 * @ZephyrId MAGETWO-23331
 */
class CreateProductRatingEntityTest extends Injectable
{
    /**
     * @var Rating
     */
    protected $productRating;

    /**
     * @var RatingIndex
     */
    protected $ratingIndex;

    /**
     * @var RatingNew
     */
    protected $ratingNew;

    /**
     * @var RatingEdit
     */
    protected $ratingEdit;

    /**
     * Prepare data
     *
     * @param FixtureFactory $fixtureFactory
     * @return array
     */
    public function __prepare(FixtureFactory $fixtureFactory)
    {
        $product = $fixtureFactory->createByCode('catalogProductSimple', ['dataSet' => 'default']);
        $product->persist();

        return ['product' => $product];
    }

    /**
     * Injection data
     *
     * @param RatingIndex $ratingIndex
     * @param RatingNew $ratingNew
     * @param RatingEdit $ratingEdit
     * @return void
     */
    public function __inject(
        RatingIndex $ratingIndex,
        RatingNew $ratingNew,
        RatingEdit $ratingEdit
    ) {
        $this->ratingIndex = $ratingIndex;
        $this->ratingNew = $ratingNew;
        $this->ratingEdit = $ratingEdit;
    }

    /**
     * Run create backend Product Rating test
     *
     * @param Rating $productRating
     * @return void
     */
    public function testCreateProductRatingEntityTest(Rating $productRating)
    {
        // Prepare data for tear down
        $this->productRating = $productRating;

        // Steps
        $this->ratingIndex->open();
        $this->ratingIndex->getGridPageActions()->addNew();
        $this->ratingNew->getRatingForm()->fill($productRating);
        $this->ratingNew->getPageActions()->save();
    }

    /**
     * Clear data after test
     *
     * @return void
     */
    public function tearDown()
    {
        if (!($this->productRating instanceof Rating)) {
            return;
        }
        $filter = ['rating_code' => $this->productRating->getRatingCode()];
        $this->ratingIndex->open();
        $this->ratingIndex->getRatingGrid()->searchAndOpen($filter);
        $this->ratingEdit->getPageActions()->delete();
    }
}
