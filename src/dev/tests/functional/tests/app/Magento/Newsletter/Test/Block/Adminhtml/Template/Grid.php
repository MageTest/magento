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

namespace Magento\Newsletter\Test\Block\Adminhtml\Template;

use Mtf\Client\Element\Locator;

/**
 * Class Grid
 * Newsletter templates grid block
 */
class Grid extends \Magento\Backend\Test\Block\Widget\Grid
{
    /**
     * Filters array mapping
     *
     * @var array
     */
    protected $filters = [
        'code' => [
            'selector' => 'input[name="code"]'
        ]
    ];

    /**
     * Locator value for link in action column
     *
     * @var string
     */
    protected $editLink = 'td.col-template';

    /**
     * Locator for "Action"
     *
     * @var string
     */
    protected $action = '.action-select';

    /**
     * Action for newsletter template
     *
     * @param string $action
     * @return void
     */
    public function performAction($action)
    {
        $this->_rootElement->find($this->action, Locator::SELECTOR_CSS, 'select')->setValue($action);
    }
}
