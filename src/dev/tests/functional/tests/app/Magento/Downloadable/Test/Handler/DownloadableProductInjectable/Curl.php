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

namespace Magento\Downloadable\Test\Handler\DownloadableProductInjectable;

use Mtf\System\Config;
use Mtf\Fixture\FixtureInterface;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;
use Magento\Catalog\Test\Handler\CatalogProductSimple\Curl as ProductCurl;

/**
 * Class Curl
 * Create new downloadable product via curl
 */
class Curl extends ProductCurl implements DownloadableProductInjectableInterface
{
    /**
     * Constructor
     *
     * @param Config $configuration
     */
    public function __construct(Config $configuration)
    {
        parent::__construct($configuration);

        $this->mappingData += [
            'links_purchased_separately' => [
                'Yes' => 1,
                'No' => 0
            ],
            'is_shareable' => [
                'Yes' => 1,
                'No' => 0,
                'Use config' => 2
            ],
        ];
    }

    /**
     * Prepare POST data for creating product request
     *
     * @param FixtureInterface $fixture
     * @param string|null $prefix [optional]
     * @return array
     */
    protected function prepareData(FixtureInterface $fixture, $prefix = null)
    {
        $data = parent::prepareData($fixture, null);

        $downloadableData = [];
        if (!empty($data['downloadable_links'])) {
            $data['links_title'] = $data['downloadable_links']['title'];
            $data['links_purchased_separately'] = $data['downloadable_links']['links_purchased_separately'];

            foreach ($data['downloadable_links']['downloadable']['link'] as $key => $link) {
                $downloadableData['downloadable']['link'][$key]['title'] = $link['title'];
                // only url type
                $downloadableData['downloadable']['link'][$key]['type'] = 'url';
                $downloadableData['downloadable']['link'][$key]['link_url'] = $link['file_link_url'];
                $downloadableData['downloadable']['link'][$key]['price'] = $link['price'];
                $downloadableData['downloadable']['link'][$key]['number_of_downloads'] = $link['number_of_downloads'];
                $downloadableData['downloadable']['link'][$key]['is_shareable'] = $link['is_shareable'];
                $downloadableData['downloadable']['link'][$key]['sort_order'] = $link['sort_order'];
                $downloadableData['downloadable']['link'][$key]['sample']['type'] = 'url';
                $downloadableData['downloadable']['link'][$key]['sample']['url'] = $link['sample']['sample_url'];
            }
            unset($data['downloadable_links']);
        }

        if (!empty($data['downloadable_sample'])) {
            $data['samples_title'] = $data['downloadable_sample']['title'];
            foreach ($data['downloadable_sample']['downloadable']['sample'] as $key => $sample) {
                $downloadableData['downloadable']['sample'][$key]['title'] = $sample['title'];
                // only url type
                $downloadableData['downloadable']['sample'][$key]['type'] = 'url';
                $downloadableData['downloadable']['sample'][$key]['sample_url'] = $sample['sample_url'];
                $downloadableData['downloadable']['sample'][$key]['sort_order'] = $sample['sort_order'];
            }
            unset($data['downloadable_sample']);
        }

        $data = $prefix ? [$prefix => $data] : $data;
        $data = array_merge($data, $downloadableData);

        return $this->replaceMappingData($data);
    }

    /**
     * Create product via curl
     *
     * @param array $data
     * @param array $config
     * @return array
     * @throws \Exception
     */
    protected function createProduct(array $data, array $config)
    {
        $url = $this->getUrl($config);
        $curl = new BackendDecorator(new CurlTransport(), new Config());
        $curl->addOption(CURLOPT_HEADER, 1);
        $curl->write(CurlInterface::POST, $url, '1.0', [], $data);
        $response = $curl->read();
        $curl->close();

        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            throw new \Exception("Product creation by curl handler was not successful! Response: $response");
        }
        preg_match("~Location: [^\s]*\/id\/(\d+)~", $response, $matches);
        $checkoutData = isset($data['product']['checkout_data']) ? $data['product']['checkout_data'] : null;
        foreach ($data['downloadable']['link'] as $key => $link) {
            preg_match('`"link_id":"(\d*?)","title":"' . $link['title'] . '"`', $response, $linkId);
            if (isset($checkoutData['options']['links'][$key]['label'])) {
                $checkoutData['options']['links'][$key]['id'] = $linkId[1];
            }
        }

        return ['id' => $matches[1], 'checkout_data' => $checkoutData];
    }
}
