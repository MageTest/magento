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

namespace Magento\Review\Test\Handler\Rating;

use Magento\Backend\Test\Handler\Extractor;
use Mtf\Fixture\FixtureInterface;
use Mtf\Handler\Curl as AbstractCurl;
use Mtf\System\Config;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;
use Mtf\Util\Protocol\CurlTransport;

/**
 * Class Curl
 * Curl handler for creating product Rating through backend.
 */
class Curl extends AbstractCurl implements RatingInterface
{
    /**
     * Mapping values for data.
     *
     * @var array
     */
    protected $mappingData = [
        'is_active' => [
            'Yes' => 1,
            'No' => 0,
        ]
    ];

    /**
     * Mapping stores value
     *
     * @var array
     */
    protected $mappingStores = [
        'Main Website/Main Website Store/Default Store View' => 1
    ];

    /**
     * Post request for creating product Rating in backend
     *
     * @param FixtureInterface|null $rating [optional]
     * @return array
     * @throws \Exception
     */
    public function persist(FixtureInterface $rating = null)
    {
        $url = $_ENV['app_backend_url'] . 'review/rating/save/';
        $curl = new BackendDecorator(new CurlTransport(), new Config());
        $data = $this->replaceMappingData($this->prepareData($rating->getData()));

        $data['stores'] = is_array($data['stores']) ? $data['stores'] : [$data['stores']];
        $data += $this->getAdditionalData();
        $curl->write(CurlInterface::POST, $url, '1.0', [], $data);
        $response = $curl->read();
        $curl->close();
        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            throw new \Exception(
                'Product Rating entity creating by curl handler was not successful! Response:' . $response
            );
        }

        $ratingId = $this->getProductRatingId();
        return [
            'rating_id' => $ratingId,
            'options' => $this->getRatingOptions($ratingId)
        ];
    }

    /**
     * Prepare POST data for creating rating request
     *
     * @param array $data
     * @return array
     */
    protected function prepareData(array $data)
    {
        if (isset($data['stores'])) {
            foreach ($data['stores'] as $key => $store) {
                if (isset($this->mappingStores[$store])) {
                    $data['stores'][$key] = $this->mappingStores[$store];
                }
            }
        }

        return $data;
    }

    /**
     * Get product Rating id
     *
     * @return mixed
     */
    protected function getProductRatingId()
    {
        $url = 'review/rating/index/sort/rating_id/dir/desc/';
        $regex = '/data-column="rating_id"[^>]*>\s*([0-9]+)\s*</';
        $extractor = new Extractor($url, $regex);
        $match = $extractor->getData();

        return empty($match[1]) ? null : $match[1];
    }

    /**
     * Get rating options
     *
     * @param int $ratingId
     * @return array
     */
    protected function getRatingOptions($ratingId)
    {
        $url = 'review/rating/edit/id/' . $ratingId;
        $regex = '/<input[^>]+name="option_title\[(\d+)\]"[^>]+>/';
        $extractor = new Extractor($url, $regex, true);
        $matches = $extractor->getData();

        if (empty($matches[1])) {
            return [];
        }
        array_unshift($matches[1], null);
        return array_filter($matches[1]);
    }

    /**
     * Return additional data for curl request
     *
     * @return array
     */
    protected function getAdditionalData()
    {
        return [
            'rating_codes' => [1 => ''],
            'option_title' => [
                'add_1' => 1,
                'add_2' => 2,
                'add_3' => 3,
                'add_4' => 4,
                'add_5' => 5,
            ],
        ];
    }
}
