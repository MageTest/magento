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
\Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    'Magento\Framework\Config\ScopeInterface'
)->setCurrentScope(
    \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE
);
$session = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\DesignEditor\Model\Session');
/** @var $auth \Magento\Backend\Model\Auth */
$auth = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Backend\Model\Auth');
$auth->setAuthStorage($session);
$auth->login(\Magento\TestFramework\Bootstrap::ADMIN_NAME, \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD);
$session->activateDesignEditor();

/** @var $theme \Magento\Framework\View\Design\ThemeInterface */
$theme = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Framework\View\Design\ThemeInterface'
);
$theme->setData(
    array(
        'theme_code' => 'blank',
        'area' => 'frontend',
        'parent_id' => null,
        'theme_path' => 'Magento/blank',
        'theme_version' => '0.1.0',
        'theme_title' => 'Default',
        'preview_image' => 'media/preview_image.jpg',
        'is_featured' => '0'
    )
);
$theme->save();
$session->setThemeId($theme->getId());
