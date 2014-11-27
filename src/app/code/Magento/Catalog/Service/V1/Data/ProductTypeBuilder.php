<?php
/**
 * Builder for product type service data object
 *
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
namespace Magento\Catalog\Service\V1\Data;

use Magento\Framework\Api\ExtensibleObjectBuilder;

/**
 * @codeCoverageIgnore
 */
class ProductTypeBuilder extends ExtensibleObjectBuilder
{
    /**
     * Set product type name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        return $this->_set(ProductType::NAME, $name);
    }

    /**
     * Set product type label
     *
     * @param string $label
     * @return $this
     */
    public function setLabel($label)
    {
        return $this->_set(ProductType::LABEL, $label);
    }
}
