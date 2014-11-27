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

return array(
    'connection' => array(
        'connection_one' => array('name' => 'connection_one', 'dbName' => 'db_one'),
        'connection_two' => array('name' => 'connection_two', 'dbName' => 'db_two')
    ),
    'resource' => array(
        'resource_one' => array('name' => 'resource_one', 'connection' => 'connection_one'),
        'resource_two' => array('name' => 'resource_two', 'connection' => 'connection_two')
    ),
    'cache' => array(
        'frontend' => array(
            'cache_frontend_one' => array('name' => 'cache_frontend_one', 'backend' => 'blackHole'),
            'cache_frontend_two' => array('name' => 'cache_frontend_two', 'backend' => 'file')
        ),
        'type' => array(
            'cache_type_one' => array('name' => 'cache_type_one', 'frontend' => 'cache_frontend_one'),
            'cache_type_two' => array('name' => 'cache_type_two', 'frontend' => 'cache_frontend_two')
        )
    ),
    'arbitrary_one' => 'Value One',
    'arbitrary_two' => 'Value Two',
    'huge_nested_level' => array(
        'level_one' => array(
            'level_two' => array('level_three' => array('level_four' => array('level_five' => 'Level Five Data')))
        )
    )
);
