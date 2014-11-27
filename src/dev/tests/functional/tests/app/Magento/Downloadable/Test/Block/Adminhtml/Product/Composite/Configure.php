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
 * @spi
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Downloadable\Test\Block\Adminhtml\Product\Composite;

use Mtf\Fixture\FixtureInterface;

/**
 * Class Configure
 * Adminhtml downloadable product composite configure block
 */
class Configure extends \Magento\Catalog\Test\Block\Adminhtml\Product\Composite\Configure
{
    /**
     * Fill options for the product
     *
     * @param FixtureInterface $product
     * @return void
     */
    public function fillOptions(FixtureInterface $product)
    {
        $data = $this->prepareData($product->getData());
        $this->_fill($data);
    }

    /**
     * Prepare data
     *
     * @param array $fields
     * @return array
     */
    protected function prepareData(array $fields)
    {
        $productOptions = [];
        $checkoutData = $fields['checkout_data']['options'];
        $productLinks = $fields['downloadable_links']['downloadable']['link'];

        if (!empty($checkoutData['links'])) {
            $linkMapping = $this->dataMapping(['link' => '']);
            $selector = $linkMapping['link']['selector'];
            foreach ($checkoutData['links'] as $key => $link) {
                $link['label'] = $productLinks[str_replace('link_', '', $link['label'])]['title'];
                $linkMapping['link']['selector'] = str_replace('%link_name%', $link['label'], $selector);
                $linkMapping['link']['value'] = $link['value'];
                $productOptions['link_' . $key] = $linkMapping['link'];
            }
        }

        return $productOptions;
    }
}
