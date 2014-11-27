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

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\AdvancedPricingTab;

use Mtf\Client\Element;
use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Options\AbstractOptions;

/**
 * Class OptionTier
 * Form 'Tier prices' on the 'Advanced Pricing' tab
 */
class OptionTier extends AbstractOptions
{
    /**
     * 'Add Tier' button selector
     *
     * @var string
     */
    protected $buttonFormLocator = "#tiers_table tfoot button";

    /**
     * Fill product form 'Tier price'
     *
     * @param array $fields
     * @param Element $element
     * @return $this
     */
    public function fillOptions(array $fields, Element $element = null)
    {
        $this->_rootElement->find($this->buttonFormLocator)->click();
        return parent::fillOptions($fields, $element);
    }
}
