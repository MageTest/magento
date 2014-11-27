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

namespace Magento\Review\Test\Block;

use Magento\Review\Test\Fixture\Rating;
use Magento\Review\Test\Fixture\ReviewInjectable;
use Mtf\Block\Form as AbstractForm;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;
use Mtf\Fixture\FixtureInterface;

/**
 * Class Form
 * Review form on frontend
 */
class Form extends AbstractForm
{
    /**
     * Legend selector
     *
     * @var string
     */
    protected $legendSelector = 'legend';

    /**
     * 'Submit' review button selector
     *
     * @var string
     */
    protected $submitButton = '.action.submit';

    /**
     * Single product rating selector
     *
     * @var string
     */
    protected $rating = './/*[@id="%s_rating_label"]/..[contains(@class,"rating")]';

    /**
     * Selector for label of rating vote
     *
     * @var string
     */
    protected $ratingVoteLabel = './div[contains(@class,"vote")]/label[contains(@id,"_%d_label")]';

    /**
     * Submit review form
     *
     * @return void
     */
    public function submit()
    {
        $this->_rootElement->find($this->submitButton, Locator::SELECTOR_CSS)->click();
    }

    /**
     * Get legend
     *
     * @return Element
     */
    public function getLegend()
    {
        return $this->_rootElement->find($this->legendSelector);
    }

    /**
     * Check rating element is visible
     *
     * @param Rating $rating
     * @return bool
     */
    public function isVisibleRating(Rating $rating)
    {
        return $this->getRating($rating)->isVisible();
    }

    /**
     * Get single product rating
     *
     * @param Rating $rating
     * @return Element
     */
    protected function getRating(Rating $rating)
    {
        return $this->_rootElement->find(sprintf($this->rating, $rating->getRatingCode()), Locator::SELECTOR_XPATH);
    }

    /**
     * Fill the review form
     *
     * @param FixtureInterface $review
     * @param Element|null $element
     * @return $this
     */
    public function fill(FixtureInterface $review, Element $element = null)
    {
        if ($review instanceof ReviewInjectable) {
            $this->fillRatings($review);
        }
        parent::fill($review, $element);
    }

    /**
     * Fill ratings on the review form
     *
     * @param ReviewInjectable $review
     * @return void
     */
    protected function fillRatings(ReviewInjectable $review)
    {
        if (!$review->hasData('ratings')) {
            return;
        }

        foreach ($review->getRatings() as $rating) {
            $this->setRating($rating['title'], $rating['rating']);
        }
    }

    /**
     * Set rating vote by rating code
     *
     * @param string $ratingCode
     * @param string $ratingVote
     * @return void
     */
    protected function setRating($ratingCode, $ratingVote)
    {
        $rating = $this->_rootElement->find(sprintf($this->rating, $ratingCode), Locator::SELECTOR_XPATH);
        $rating->find(sprintf($this->ratingVoteLabel, $ratingVote), Locator::SELECTOR_XPATH)->click();
    }
}
