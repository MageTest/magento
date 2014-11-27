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
namespace Magento\Backend\Model\Translate\Inline;

/**
 * Backend Inline Translation config
 */
class Config implements \Magento\Framework\Translate\Inline\ConfigInterface
{
    /**
     * @var \Magento\Backend\App\ConfigInterface
     */
    protected $_config;

    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $_helper;

    /**
     * @param \Magento\Backend\App\ConfigInterface $config
     * @param \Magento\Core\Helper\Data $helper
     */
    public function __construct(\Magento\Backend\App\ConfigInterface $config, \Magento\Core\Helper\Data $helper)
    {
        $this->_config = $config;
        $this->_helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function isActive($scope = null)
    {
        return $this->_config->isSetFlag('dev/translate_inline/active_admin');
    }

    /**
     * {@inheritdoc}
     */
    public function isDevAllowed($scope = null)
    {
        return $this->_helper->isDevAllowed($scope);
    }
}
