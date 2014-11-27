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
namespace Magento\Review\Block\Adminhtml\Rating;

use Magento\Review\Model\Rating;
use Magento\Review\Model\Rating\Option;
use Magento\Review\Model\Resource\Rating\Collection as RatingCollection;
use Magento\Review\Model\Resource\Rating\Option\Vote\Collection as VoteCollection;

/**
 * Adminhtml detailed rating stars
 */
class Detailed extends \Magento\Backend\Block\Template
{
    /**
     * Vote collection
     *
     * @var VoteCollection
     */
    protected $_voteCollection = false;

    /**
     * Rating detail template name
     *
     * @var string
     */
    protected $_template = 'Magento_Review::rating/detailed.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Rating resource model
     *
     * @var \Magento\Review\Model\Resource\Rating\CollectionFactory
     */
    protected $_ratingsFactory;

    /**
     * Rating resource option model
     *
     * @var \Magento\Review\Model\Resource\Rating\Option\Vote\CollectionFactory
     */
    protected $_votesFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Review\Model\Resource\Rating\CollectionFactory $ratingsFactory
     * @param \Magento\Review\Model\Resource\Rating\Option\Vote\CollectionFactory $votesFactory
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Review\Model\Resource\Rating\CollectionFactory $ratingsFactory,
        \Magento\Review\Model\Resource\Rating\Option\Vote\CollectionFactory $votesFactory,
        \Magento\Framework\Registry $registry,
        array $data = array()
    ) {
        $this->_ratingsFactory = $ratingsFactory;
        $this->_votesFactory = $votesFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize review data
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        if ($this->_coreRegistry->registry('review_data')) {
            $this->setReviewId($this->_coreRegistry->registry('review_data')->getReviewId());
        }
    }

    /**
     * Get collection of ratings
     *
     * @return RatingCollection
     */
    public function getRating()
    {
        if (!$this->getRatingCollection()) {
            if ($this->_coreRegistry->registry('review_data')) {
                $stores = $this->_coreRegistry->registry('review_data')->getStores();

                $stores = array_diff($stores, array(0));

                $ratingCollection = $this->_ratingsFactory->create()->addEntityFilter(
                    'product'
                )->setStoreFilter(
                    $stores
                )->setActiveFilter(
                    true
                )->setPositionOrder()->load()->addOptionToItems();

                $this->_voteCollection = $this->_votesFactory->create()->setReviewFilter(
                    $this->getReviewId()
                )->addOptionInfo()->load()->addRatingOptions();
            } elseif (!$this->getIsIndependentMode()) {
                $ratingCollection = $this->_ratingsFactory->create()->addEntityFilter(
                    'product'
                )->setStoreFilter(
                    null
                )->setPositionOrder()->load()->addOptionToItems();
            } else {
                $stores = $this->getRequest()->getParam('select_stores') ?: $this->getRequest()->getParam('stores');
                $ratingCollection = $this->_ratingsFactory->create()->addEntityFilter(
                    'product'
                )->setStoreFilter(
                    $stores
                )->setPositionOrder()->load()->addOptionToItems();
                if (intval($this->getRequest()->getParam('id'))) {
                    $this->_voteCollection = $this->_votesFactory->create()->setReviewFilter(
                        intval($this->getRequest()->getParam('id'))
                    )->addOptionInfo()->load()->addRatingOptions();
                }
            }
            $this->setRatingCollection($ratingCollection->getSize() ? $ratingCollection : false);
        }
        return $this->getRatingCollection();
    }

    /**
     * Set independent mode
     *
     * @return $this
     */
    public function setIndependentMode()
    {
        $this->setIsIndependentMode(true);
        return $this;
    }

    /**
     * Indicator of whether or not a rating is selected
     *
     * @param Option $option
     * @param \Magento\Review\Model\Rating $rating
     * @return bool
     */
    public function isSelected($option, $rating)
    {
        if ($this->getIsIndependentMode()) {
            $ratings = $this->getRequest()->getParam('ratings');

            if (isset($ratings[$option->getRatingId()])) {
                return $option->getId() == $ratings[$option->getRatingId()];
            } elseif (!$this->_voteCollection) {
                return false;
            }
        }

        if ($this->_voteCollection) {
            foreach ($this->_voteCollection as $vote) {
                if ($option->getId() == $vote->getOptionId()) {
                    return true;
                }
            }
        }
        return false;
    }
}
