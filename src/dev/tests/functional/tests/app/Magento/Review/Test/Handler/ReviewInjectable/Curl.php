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

namespace Magento\Review\Test\Handler\ReviewInjectable;

use Mtf\System\Config;
use Mtf\Fixture\FixtureInterface;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;
use Magento\Review\Test\Fixture\Rating;
use Magento\Backend\Test\Handler\Extractor;
use Magento\Review\Test\Fixture\ReviewInjectable;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;
use Mtf\Handler\Curl as AbstractCurl;

/**
 * Class Curl
 * Curl handler for creating product Review through backend.
 */
class Curl extends AbstractCurl implements ReviewInjectableInterface
{
    /**
     * Mapping values for data.
     *
     * @var array
     */
    protected $mappingData = [
        'status_id' => [
            'Approved' => 1,
            'Pending' => 2,
            'Not Approved' => 3
        ],
        'select_stores' => [
            'Main Website/Main Website Store/Default Store View' => 1
        ]
    ];

    /**
     * Post request for creating product Review in backend
     *
     * @param FixtureInterface|null $review [optional]
     * @return array
     * @throws \Exception
     */
    public function persist(FixtureInterface $review = null)
    {
        /** @var ReviewInjectable $review */
        $url = $_ENV['app_backend_url'] . 'review/product/post/';
        $curl = new BackendDecorator(new CurlTransport(), new Config());
        $data = $this->replaceMappingData($this->getPreparedData($review));

        $curl->write(CurlInterface::POST, $url, '1.0', [], $data);
        $response = $curl->read();
        $curl->close();
        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            throw new \Exception(
                'Product Review entity creating by curl handler was not successful! Response:' . $response
            );
        }

        return ['review_id' => $this->getReviewId()];
    }

    /**
     * Prepare and return data of review
     *
     * @param FixtureInterface $review
     * @return array
     */
    protected function getPreparedData(FixtureInterface $review)
    {
        $data = $review->getData();

        /* Prepare ratings */
        if ($review->hasData('ratings')) {
            $sourceRatings = $review->getDataFieldConfig('ratings')['source'];
            $ratings = [];
            foreach ($data['ratings'] as $rating) {
                $ratings[$rating['title']] = $rating['rating'];
            }
            $data['ratings'] = [];
            foreach ($sourceRatings->getRatings() as $ratingFixture) {
                /** @var Rating $ratingFixture */
                $ratingCode = $ratingFixture->getRatingCode();
                if (isset($ratings[$ratingCode])) {
                    $ratingOptions = $ratingFixture->getOptions();
                    $vote = $ratings[$ratingCode];
                    $data['ratings'][$ratingFixture->getRatingId()] = $ratingOptions[$vote];
                }
            }
        }

        if ($review->hasData('select_stores')) {
            foreach (array_keys($data['select_stores']) as $key) {
                if (isset($this->mappingData['select_stores'][$data['select_stores'][$key]])) {
                    $data['select_stores'][$key] = $this->mappingData['select_stores'][$data['select_stores'][$key]];
                }
            }
        }

        /* Prepare product id */
        $data['product_id'] = $data['entity_id'];
        unset($data['entity_id']);

        return $data;
    }

    /**
     * Get product Rating id
     *
     * @return int|null
     */
    protected function getReviewId()
    {
        $url = 'review/product/index/sort/review_id/dir/desc/';
        $regex = '/class="[^"]+col-id[^"]+"[^>]*>\s*([0-9]+)\s*</';
        $extractor = new Extractor($url, $regex);
        $match = $extractor->getData();

        return empty($match[1]) ? null : (int)$match[1];
    }
}
