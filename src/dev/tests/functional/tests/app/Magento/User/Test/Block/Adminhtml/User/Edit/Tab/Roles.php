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

namespace Magento\User\Test\Block\Adminhtml\User\Edit\Tab;

use Mtf\Client\Element;
use Mtf\Factory\Factory;
use Magento\Backend\Test\Block\Widget\Grid;

/**
 * Class Roles
 * Grid on Roles Tab page for User
 */
class Roles extends Grid
{
    /**
     * Locator value for link in action column
     *
     * @var string
     */
    protected $editLink = '.col-role_name';

    /**
     * An element locator which allows to select entities in grid
     *
     * @var string
     */
    protected $selectItem = 'tbody tr';

    /**
     * Filters Name for Roles Grid
     *
     * @var array
     */
    protected $filters = array(
        'id' => array(
            'selector' => '#permissionsUserRolesGrid_filter_assigned_user_role',
            'input' => 'select'
        ),
        'role_name' => array(
            'selector' => '#permissionsUserRolesGrid_filter_role_name'
        )
    );
}
