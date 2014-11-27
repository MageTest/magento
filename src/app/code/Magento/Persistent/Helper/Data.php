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

/**
 * Persistent Shopping Cart Data Helper
 */
namespace Magento\Persistent\Helper;

use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Core\Helper\Data
{
    const XML_PATH_ENABLED = 'persistent/options/enabled';

    const XML_PATH_LIFE_TIME = 'persistent/options/lifetime';

    const XML_PATH_LOGOUT_CLEAR = 'persistent/options/logout_clear';

    const XML_PATH_REMEMBER_ME_ENABLED = 'persistent/options/remember_enabled';

    const XML_PATH_REMEMBER_ME_DEFAULT = 'persistent/options/remember_default';

    const XML_PATH_PERSIST_SHOPPING_CART = 'persistent/options/shopping_cart';

    /**
     * Name of config file
     *
     * @var string
     */
    protected $_configFileName = 'persistent.xml';

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $_modulesReader;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Module\Dir\Reader $modulesReader
     * @param \Magento\Framework\Escaper $escaper
     * @param bool $dbCompatibleMode
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Module\Dir\Reader $modulesReader,
        \Magento\Framework\Escaper $escaper,
        $dbCompatibleMode = true
    ) {
        $this->_modulesReader = $modulesReader;
        $this->_escaper = $escaper;

        parent::__construct(
            $context,
            $scopeConfig,
            $storeManager,
            $appState,
            $priceCurrency,
            $dbCompatibleMode
        );
    }

    /**
     * Checks whether Persistence Functionality is enabled
     *
     * @param int|string|\Magento\Store\Model\Store $store
     * @return bool
     */
    public function isEnabled($store = null)
    {
        return $this->_scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Checks whether "Remember Me" enabled
     *
     * @param int|string|\Magento\Store\Model\Store $store
     * @return bool
     */
    public function isRememberMeEnabled($store = null)
    {
        return $this->_scopeConfig->isSetFlag(
            self::XML_PATH_REMEMBER_ME_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Is "Remember Me" checked by default
     *
     * @param int|string|\Magento\Store\Model\Store $store
     * @return bool
     */
    public function isRememberMeCheckedDefault($store = null)
    {
        return $this->_scopeConfig->isSetFlag(
            self::XML_PATH_REMEMBER_ME_DEFAULT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Is shopping cart persist
     *
     * @param int|string|\Magento\Store\Model\Store $store
     * @return bool
     */
    public function isShoppingCartPersist($store = null)
    {
        return $this->_scopeConfig->isSetFlag(
            self::XML_PATH_PERSIST_SHOPPING_CART,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get Persistence Lifetime
     *
     * @param int|string|\Magento\Store\Model\Store $store
     * @return int
     */
    public function getLifeTime($store = null)
    {
        $lifeTime = intval(
            $this->_scopeConfig->getValue(
                self::XML_PATH_LIFE_TIME,
                ScopeInterface::SCOPE_STORE,
                $store
            )
        );
        return $lifeTime < 0 ? 0 : $lifeTime;
    }

    /**
     * Check if set `Clear on Logout` in config settings
     *
     * @return bool
     */
    public function getClearOnLogout()
    {
        return $this->_scopeConfig->isSetFlag(
            self::XML_PATH_LOGOUT_CLEAR,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve url for unset long-term cookie
     *
     * @return string
     */
    public function getUnsetCookieUrl()
    {
        return $this->_getUrl('persistent/index/unsetCookie');
    }

    /**
     * Retrieve path for config file
     *
     * @return string
     */
    public function getPersistentConfigFilePath()
    {
        return $this->_modulesReader->getModuleDir('etc', $this->_getModuleName()) . '/' . $this->_configFileName;
    }

    /**
     * Check whether specified action should be processed
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return bool
     */
    public function canProcess($observer)
    {
        return true;
    }
}
