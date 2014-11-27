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

namespace Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option\Metadata\Reader;

use \Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option\MetadataBuilder;
use \Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option\Metadata;
use \Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option\Metadata\ReaderInterface;

class DefaultReader implements ReaderInterface
{
    /**
     * @var MetadataBuilder
     */
    protected $valueBuilder;

    /**
     * @param MetadataBuilder $valueBuilder
     */
    public function __construct(MetadataBuilder $valueBuilder)
    {
        $this->valueBuilder = $valueBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function read(\Magento\Catalog\Model\Product\Option $option)
    {
        $fields = [
            Metadata::PRICE => $option->getPrice(),
            Metadata::PRICE_TYPE => $option->getPriceType(),
            Metadata::SKU => $option->getSku()
        ];
        $fields = array_merge($fields, $this->getCustomAttributes($option));
        $value = $this->valueBuilder->populateWithArray($fields)->create();
        return [$value];
    }

    /**
     * Get custom attributes
     *
     * @param \Magento\Catalog\Model\Product\Option $option
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getCustomAttributes(\Magento\Catalog\Model\Product\Option $option)
    {
        return [];
    }
}
