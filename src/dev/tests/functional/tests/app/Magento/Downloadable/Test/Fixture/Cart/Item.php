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

namespace Magento\Downloadable\Test\Fixture\Cart;

use Mtf\Fixture\FixtureInterface;
use Magento\Downloadable\Test\Fixture\DownloadableProductInjectable;

/**
 * Class Item
 * Data for verify cart item block on checkout page
 *
 * Data keys:
 *  - product (fixture data for verify)
 */
class Item extends \Magento\Catalog\Test\Fixture\Cart\Item
{
    /**
     * @constructor
     * @param FixtureInterface $product
     */
    public function __construct(FixtureInterface $product)
    {
        parent::__construct($product);

        /** @var DownloadableProductInjectable $product */
        $checkoutDownloadableOptions = [];
        $checkoutData = $product->getCheckoutData();
        $downloadableOptions = $product->getDownloadableLinks();
        foreach ($checkoutData['options']['links'] as $link) {
            $keyLink = str_replace('link_', '', $link['label']);
            $checkoutDownloadableOptions[] = [
                'title' => 'Links',
                'value' => $downloadableOptions['downloadable']['link'][$keyLink]['title']
            ];
        }

        $this->data['options'] += $checkoutDownloadableOptions;
    }
}
