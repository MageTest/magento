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
 * Emulation model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Core\Model\App;

use Magento\Framework\Translate\Inline\ConfigInterface;

class Emulation extends \Magento\Framework\Object
{
    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\TranslateInterface
     */
    protected $_translate;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var \Magento\Framework\App\DesignInterface
     */
    protected $_design;

    /**
     * @var ConfigInterface
     */
    protected $inlineConfig;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * Ini
     *
     * @var \Magento\Framework\Object
     */
    private $initialEnvironmentInfo;

    /**
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\DesignInterface $viewDesign
     * @param \Magento\Framework\App\DesignInterface $design
     * @param \Magento\Framework\TranslateInterface $translate
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param ConfigInterface $inlineConfig
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Framework\View\DesignInterface $viewDesign,
        \Magento\Framework\App\DesignInterface $design,
        \Magento\Framework\TranslateInterface $translate,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        ConfigInterface $inlineConfig,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        array $data = array()
    ) {
        $this->_localeResolver = $localeResolver;
        parent::__construct($data);
        $this->_storeManager = $storeManager;
        $this->_viewDesign = $viewDesign;
        $this->_design = $design;
        $this->_translate = $translate;
        $this->_scopeConfig = $scopeConfig;
        $this->inlineConfig = $inlineConfig;
        $this->inlineTranslation = $inlineTranslation;
    }

    /**
     * Start environment emulation of the specified store
     *
     * Function returns information about initial store environment and emulates environment of another store
     *
     * @param integer $storeId
     * @param string $area
     * @return void
     */
    public function startEnvironmentEmulation(
        $storeId,
        $area = \Magento\Framework\App\Area::AREA_FRONTEND
    ) {
        if ($storeId == $this->_storeManager->getStore()->getStoreId()) {
            return;
        }
        $this->storeCurrentEnvironmentInfo();

        // emulate inline translations
        $this->inlineTranslation->suspend($this->inlineConfig->isActive($storeId));

        // emulate design
        $storeTheme = $this->_viewDesign->getConfigurationDesignTheme($area, array('store' => $storeId));
        $this->_viewDesign->setDesignTheme($storeTheme, $area);

        if ($area == \Magento\Framework\App\Area::AREA_FRONTEND) {
            $designChange = $this->_design->loadChange($storeId);
            if ($designChange->getData()) {
                $this->_viewDesign->setDesignTheme($designChange->getDesign(), $area);
            }
        }

        // Current store needs to be changed right before locale change and after design change
        $this->_storeManager->setCurrentStore($storeId);

        // emulate locale
        $newLocaleCode = $this->_scopeConfig->getValue(
            $this->_localeResolver->getDefaultLocalePath(),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $this->_localeResolver->setLocaleCode($newLocaleCode);
        $this->_translate->setLocale($newLocaleCode);
        $this->_translate->loadData($area);

        return;
    }

    /**
     * Stop environment emulation
     *
     * Function restores initial store environment
     *
     * @return \Magento\Core\Model\App\Emulation
     */
    public function stopEnvironmentEmulation()
    {
        if (is_null($this->initialEnvironmentInfo)) {
            return $this;
        }

        $this->_restoreInitialInlineTranslation($this->initialEnvironmentInfo->getInitialTranslateInline());
        $initialDesign = $this->initialEnvironmentInfo->getInitialDesign();
        $this->_restoreInitialDesign($initialDesign);
        // Current store needs to be changed right before locale change and after design change
        $this->_storeManager->setCurrentStore($initialDesign['store']);
        $this->_restoreInitialLocale($this->initialEnvironmentInfo->getInitialLocaleCode(), $initialDesign['area']);
        
        $this->initialEnvironmentInfo = null;
        return $this;
    }

    /**
     * Stores current environment info
     *
     * @return void
     */
    public function storeCurrentEnvironmentInfo()
    {
        $this->initialEnvironmentInfo = new \Magento\Framework\Object();
        $this->initialEnvironmentInfo->setInitialTranslateInline(
            $this->inlineTranslation->isEnabled()
        )->setInitialDesign(
            [
                'area' => $this->_viewDesign->getArea(),
                'theme' => $this->_viewDesign->getDesignTheme(),
                'store' => $this->_storeManager->getStore()->getStoreId()
            ]
        )->setInitialLocaleCode(
            $this->_localeResolver->getLocaleCode()
        );
    }

    /**
     * Restore initial inline translation state
     *
     * @param bool $initialTranslate
     * @return $this
     */
    protected function _restoreInitialInlineTranslation($initialTranslate)
    {
        $this->inlineTranslation->resume($initialTranslate);
        return $this;
    }

    /**
     * Restore design of the initial store
     *
     * @param array $initialDesign
     * @return $this
     */
    protected function _restoreInitialDesign(array $initialDesign)
    {
        $this->_viewDesign->setDesignTheme($initialDesign['theme'], $initialDesign['area']);
        return $this;
    }

    /**
     * Restore locale of the initial store
     *
     * @param string $initialLocaleCode
     * @param string $initialArea
     * @return $this
     */
    protected function _restoreInitialLocale(
        $initialLocaleCode,
        $initialArea = \Magento\Framework\App\Area::AREA_ADMIN
    ) {
        $this->_localeResolver->setLocaleCode($initialLocaleCode);
        $this->_translate->setLocale($initialLocaleCode);
        $this->_translate->loadData($initialArea);

        return $this;
    }
}
