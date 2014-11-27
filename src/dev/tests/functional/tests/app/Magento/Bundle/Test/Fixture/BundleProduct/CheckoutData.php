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

namespace Magento\Bundle\Test\Fixture\BundleProduct;

/**
 * Class CheckoutData
 * Data for fill product form on frontend
 *
 * Data keys:
 *  - preset (Checkout data verification preset name)
 *
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class CheckoutData extends \Magento\Catalog\Test\Fixture\CatalogProductSimple\CheckoutData
{
    /**
     * Get preset array
     *
     * @param string $name
     * @return array|null
     */
    protected function getPreset($name)
    {
        $presets = [
            'default' => [
                'options' => [
                    'bundle_options' => [
                        [
                            'title' => 'Drop-down Option',
                            'type' => 'Drop-down',
                            'value' => [
                                'name' => '100_dollar_product'
                            ]
                        ]
                    ]
                ]
            ],
            'default_dynamic' => [
                'options' => [
                    'bundle_options' => [
                        [
                            'title' => 'Drop-down Option',
                            'type' => 'Drop-down',
                            'value' => [
                                'name' => '100_dollar_product'
                            ]
                        ]
                    ],
                ],
                'qty' => 2,
                'cartItem' => [
                    'price' => 100,
                    'qty' => 2,
                    'subtotal' => 200
                ]
            ],
            'default_fixed' => [
                'options' => [
                    'bundle_options' => [
                        [
                            'title' => 'Drop-down Option',
                            'type' => 'Drop-down',
                            'value' => [
                                'name' => '100_dollar_product'
                            ]
                        ]
                    ],
                ],
                'qty' => 2,
                'cartItem' => [
                    'price' => 756,
                    'qty' => 2,
                    'subtotal' => 756
                ]
            ],
            'forUpdateMiniShoppingCart' => [
                'options' => [
                    'bundle_options' => [
                        [
                            'title' => 'Drop-down Option',
                            'type' => 'Drop-down',
                            'value' => [
                                'name' => 'Simple Product'
                            ]
                        ]
                    ],
                ],
                'qty' => 1,
                'cartItem' => [
                    'price' => 756,
                    'qty' => 1,
                    'subtotal' => 756
                ]
            ],
            'with_not_required_options' => [
                'options' => [
                    'bundle_options' => [
                        [
                            'title' => 'Drop-down Option',
                            'type' => 'Drop-down',
                            'value' => [
                                'name' => '100_dollar_product'
                            ]
                        ],
                        [
                            'title' => 'Radio Button Option',
                            'type' => 'Radio Buttons',
                            'value' => [
                                'name' => '100_dollar_product'
                            ]
                        ]
                    ]
                ]
            ],
            'with_custom_options_1' => [
                'options' => [
                    'bundle_options' => [
                        [
                            'title' => 'Drop-down Option',
                            'type' => 'Drop-down',
                            'value' => [
                                'name' => '100_dollar_product'
                            ]
                        ],
                    ],
                    'custom_options' => [
                        [
                            'title' => 'attribute_key_0',
                            'value' => 'option_key_0',
                        ],
                        [
                            'title' => 'attribute_key_1',
                            'value' => 'option_key_0',
                        ],
                        [
                            'title' => 'attribute_key_2',
                            'value' => 'Field',
                        ],
                        [
                            'title' => 'attribute_key_3',
                            'value' => 'Field',
                        ],
                        [
                            'title' => 'attribute_key_4',
                            'value' => 'Area',
                        ],
                        [
                            'title' => 'attribute_key_6',
                            'value' => 'option_key_0',
                        ],
                        [
                            'title' => 'attribute_key_7',
                            'value' => 'option_key_0',
                        ],
                        [
                            'title' => 'attribute_key_8',
                            'value' => 'option_key_0',
                        ],
                        [
                            'title' => 'attribute_key_9',
                            'value' => 'option_key_0',
                        ],
                        [
                            'title' => 'attribute_key_10',
                            'value' => '12/12/2014',
                        ],
                        [
                            'title' => 'attribute_key_11',
                            'value' => '12/12/2014/12/30/AM',
                        ],
                        [
                            'title' => 'attribute_key_12',
                            'value' => '12/12/AM',
                        ],
                    ]
                ]
            ],
            'with_custom_options_2' => [
                'options' => [
                    'bundle_options' => [
                        [
                            'title' => 'Drop-down Option',
                            'type' => 'Drop-down',
                            'value' => [
                                'name' => '100_dollar_product'
                            ]
                        ]
                    ],
                    'custom_options' => [
                        [
                            'title' => 'attribute_key_0',
                            'value' => 'option_key_0',
                        ]
                    ]
                ]
            ],
            'all_types_bundle_fixed_and_custom_options' => [
                'options' => [
                    'bundle_options' => [
                        [
                            'title' => 'Drop-down Option',
                            'type' => 'Drop-down',
                            'value' => [
                                'name' => '100_dollar_product'
                            ]
                        ],
                        [
                            'title' => 'Radio Button Option',
                            'type' => 'Radio Buttons',
                            'value' => [
                                'name' => '100_dollar_product'
                            ]
                        ],
                        [
                            'title' => 'Checkbox Option',
                            'type' => 'Checkbox',
                            'value' => [
                                'name' => '100_dollar_product'
                            ]
                        ],
                        [
                            'title' => 'Multiple Select Option',
                            'type' => 'Multiple',
                            'value' => [
                                'name' => '100_dollar_product'
                            ]
                        ],
                    ],
                    'custom_options' => [
                        [
                            'title' => 'attribute_key_0',
                            'value' => 'Field',
                        ],
                        [
                            'title' => 'attribute_key_1',
                            'value' => 'Area',
                        ],
                        [
                            'title' => 'attribute_key_3',
                            'value' => 'option_key_0',
                        ],
                        [
                            'title' => 'attribute_key_4',
                            'value' => 'option_key_0',
                        ],
                        [
                            'title' => 'attribute_key_5',
                            'value' => 'option_key_0',
                        ],
                        [
                            'title' => 'attribute_key_6',
                            'value' => 'option_key_0',
                        ],
                        [
                            'title' => 'attribute_key_7',
                            'value' => '12/12/2014',
                        ],
                        [
                            'title' => 'attribute_key_8',
                            'value' => '12/12/2014/12/30/AM',
                        ],
                        [
                            'title' => 'attribute_key_9',
                            'value' => '12/12/AM',
                        ],
                    ]
                ]
            ],
            'all_types_bundle_options' => [
                'options' => [
                    'bundle_options' => [
                        [
                            'title' => 'Drop-down Option',
                            'type' => 'Drop-down',
                            'value' => [
                                'name' => '100_dollar_product'
                            ]
                        ],
                        [
                            'title' => 'Radio Button Option',
                            'type' => 'Radio Buttons',
                            'value' => [
                                'name' => '100_dollar_product'
                            ]
                        ],
                        [
                            'title' => 'Checkbox Option',
                            'type' => 'Checkbox',
                            'value' => [
                                'name' => '100_dollar_product'
                            ]
                        ],
                        [
                            'title' => 'Multiple Select Option',
                            'type' => 'Multiple',
                            'value' => [
                                'name' => '100_dollar_product'
                            ]
                        ],
                    ],
                ]
            ],
        ];
        return isset($presets[$name]) ? $presets[$name] : null;
    }
}
