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

namespace Magento\Customer\Test\Block\Account\Dashboard;

use Mtf\Block\Block;

/**
 * Class Info
 * Main block on customer account page
 */
class Info extends Block
{
    /**
     * Css selector for Contact Information Edit Link
     *
     * @var string
     */
    protected $contactInfoEditLink = '.block-dashboard-info .box-information .action.edit';

    /**
     * Css selector for Contact Information Change Password Link
     *
     * @var string
     */
    protected $contactInfoChangePasswordLink = '.block-dashboard-info .box-information .action.change-password';

    /**
     * Dashboard Welcome block locator
     *
     * @var string
     */
    protected $dashboardWelcome = '.block-dashboard-welcome .block-title';

    /**
     * Click on Contact Information Edit Link
     *
     * @return void
     */
    public function openEditContactInfo()
    {
        $this->_rootElement->find($this->contactInfoEditLink)->click();
    }

    /**
     * Click on Contact Information Edit Link
     *
     * @return void
     */
    public function openChangePassword()
    {
        $this->_rootElement->find($this->contactInfoChangePasswordLink)->click();
    }

    /**
     * Get welcome text
     *
     * @return string
     */
    public function getWelcomeText()
    {
        return $this->_rootElement->find($this->dashboardWelcome)->getText();
    }
}
