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

namespace Magento\Downloadable\Test\Fixture\DownloadableProductInjectable;

/**
 * Class CheckoutData
 * Data for fill product form on frontend
 *
 * Data keys:
 *  - preset (Checkout data verification preset name)
 */
class CheckoutData extends \Magento\Catalog\Test\Fixture\CatalogProductSimple\CheckoutData
{
    /**
     * Get preset array
     *
     * @param $name
     * @return array|null
     */
    protected function getPreset($name)
    {
        $presets = [
            'with_two_separately_links' => [
                'options' => [
                    'links' => [
                        [
                            'label' => 'link_1',
                            'value' => 'Yes'
                        ]
                    ],
                ],
                'cartItem' => [
                    'price' => 23,
                    'subtotal' => 23
                ]
            ],
            'with_two_bought_links' => [
                'options' => [
                    'links' => [
                        [
                            'label' => 'link_1',
                            'value' => 'Yes'
                        ],
                        [
                            'label' => 'link_2',
                            'value' => 'Yes'
                        ]
                    ],
                    'cartItem' => [
                        'price' => 23,
                        'subtotal' => 23
                    ]
                ],
            ],
            'forUpdateMiniShoppingCart' => [
                'options' => [
                    'links' => [
                        [
                            'label' => 'link_0',
                            'value' => 'Yes'
                        ]
                    ],
                ],
                'cartItem' => [
                    'price' => 23,
                    'subtotal' => 22.43
                ]
            ],
            'default' => [
                'options' => [
                    'links' => [
                        [
                            'label' => 'link_1',
                            'value' => 'Yes'
                        ]
                    ],
                ]
            ],
        ];
        return isset($presets[$name]) ? $presets[$name] : [];
    }
}
