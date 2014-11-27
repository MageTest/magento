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

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\ProductDetails;

use Mtf\Client\Driver\Selenium\Element\MultisuggestElement;
use Mtf\Client\Element\Locator;

/**
 * Class CategoryIds
 * Typified element class for category element
 */
class CategoryIds extends MultisuggestElement
{
    /**
     * Selector suggest input
     *
     * @var string
     */
    protected $suggest = '#category_ids-suggest';

    /**
     * Selector item of search result
     *
     * @var string
     */
    protected $resultItem = './/li/a/span[@class="category-label"][text()="%s"]';

    /**
     * Selector for click on top page.
     *
     * @var string
     */
    protected $top = './ancestor::body//*[@class="page-main-actions"]';

    /**
     * Set value
     *
     * @param array|string $values
     * @return void
     */
    public function setValue($values)
    {
        $this->find($this->top, Locator::SELECTOR_XPATH)->click();
        parent::setValue($values);
    }
}
