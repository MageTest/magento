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
 * @spi
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Customer\Test\Handler\Ui;

use Mtf\Fixture\FixtureInterface;
use Mtf\Factory\Factory;

/**
 * UI handler for creating customer address.
 */
class CreateAddress extends \Mtf\Handler\Ui
{
    /**
     * Execute handler
     *
     * @param FixtureInterface $fixture [optional]
     * @return mixed
     */
    public function persist(FixtureInterface $fixture = null)
    {
        /** @var \Magento\Customer\Test\Fixture\Address $fixture */
        // Pages
        $loginPage = Factory::getPageFactory()->getCustomerAccountLogin();
        $addressPage = Factory::getPageFactory()->getCustomerAddressEdit();

        $loginPage->open();
        if ($loginPage->getLoginBlock()->isVisible()) {
            $loginPage->getLoginBlock()->login($fixture->getCustomer());
        }

        $addressPage->open();
        $addressPage->getEditForm()->editCustomerAddress($fixture);
    }
}
