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
namespace Magento\Downloadable\Test\Fixture\DownloadableProduct;

use Mtf\Factory\Factory;
use Magento\Downloadable\Test\Fixture\DownloadableProduct;

/**
 * Class LinksPurchasedSeparately
 *
 * Init downloadable data purchased separately
 */
class LinksPurchasedSeparately extends DownloadableProduct
{
    /**
     * Init downloadable data
     */
    protected function _initData()
    {
        parent::_initData();
        $this->_data = array_replace_recursive(
            $this->_data,
            [
                'fields' => [
                    'downloadable_links' => [
                        'value' => [
                            'title' => 'Links%isolation%',
                            'links_purchased_separately' => 'Yes',
                            'downloadable' => [
                                'link' => [
                                    [
                                        'title' => 'row1%isolation%',
                                        'price' => 2.43,
                                        'number_of_downloads' => 2,
                                        'sample' => [
                                            'sample_type_url' => 'Yes',
                                            'sample_url' => 'http://example.com'
                                        ],
                                        'file_type_url' => 'Yes',
                                        'file_link_url' => 'http://example.com',
                                        'is_shareable' => 'No',
                                        'sort_order' => 0
                                    ]
                                ],
                            ]
                        ],
                        'group' => static::GROUP
                    ],
                ]
            ]
        );

        $this->_repository = Factory::getRepositoryFactory()
            ->getMagentoDownloadableDownloadableProduct($this->_dataConfig, $this->_data);
    }
}
