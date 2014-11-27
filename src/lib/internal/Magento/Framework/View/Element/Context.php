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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\View\Element;

/**
 * Abstract block context object
 *
 * Will be used as block constructor modification point after release.
 * Important: Should not be modified by extension developers.
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Context implements \Magento\Framework\ObjectManager\ContextInterface
{
    /**
     * Request
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * Layout
     *
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * Event manager
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * URL builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * Translator
     *
     * @var \Magento\Framework\TranslateInterface
     */
    protected $_translator;

    /**
     * Cache
     *
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $_cache;

    /**
     * Design
     *
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $_design;

    /**
     * Session
     *
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_session;

    /**
     * SID Resolver
     *
     * @var \Magento\Framework\Session\SidResolverInterface
     */
    protected $_sidResolver;

    /**
     * Scope config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * View config model
     *
     * @var \Magento\Framework\View\ConfigInterface
     */
    protected $_viewConfig;

    /**
     * Cache state
     *
     * @var \Magento\Framework\App\Cache\StateInterface
     */
    protected $_cacheState;

    /**
     * Logger
     *
     * @var \Magento\Framework\Logger
     */
    protected $_logger;

    /**
     * Escaper
     *
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * Filter manager
     *
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $_filterManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\TranslateInterface $translator
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\View\ConfigInterface $viewConfig
     * @param \Magento\Framework\App\Cache\StateInterface $cacheState
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\TranslateInterface $translator,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\View\ConfigInterface $viewConfig,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        \Magento\Framework\Logger $logger,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
    ) {
        $this->_request = $request;
        $this->_layout = $layout;
        $this->_eventManager = $eventManager;
        $this->_urlBuilder = $urlBuilder;
        $this->_translator = $translator;
        $this->_cache = $cache;
        $this->_design = $design;
        $this->_session = $session;
        $this->_sidResolver = $sidResolver;
        $this->_scopeConfig = $scopeConfig;
        $this->_assetRepo = $assetRepo;
        $this->_viewConfig = $viewConfig;
        $this->_cacheState = $cacheState;
        $this->_logger = $logger;
        $this->_escaper = $escaper;
        $this->_filterManager = $filterManager;
        $this->_localeDate = $localeDate;
        $this->inlineTranslation = $inlineTranslation;
    }

    /**
     * Get cache
     *
     * @return \Magento\Framework\App\CacheInterface
     */
    public function getCache()
    {
        return $this->_cache;
    }

    /**
     * Get design package
     *
     * @return \Magento\Framework\View\DesignInterface
     */
    public function getDesignPackage()
    {
        return $this->_design;
    }

    /**
     * Get event manager
     *
     * @return \Magento\Framework\Event\ManagerInterface
     */
    public function getEventManager()
    {
        return $this->_eventManager;
    }

    /**
     * Get layout
     *
     * @return \Magento\Framework\View\LayoutInterface
     */
    public function getLayout()
    {
        return $this->_layout;
    }

    /**
     * Get request
     *
     * @return \Magento\Framework\App\RequestInterface
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Get session
     *
     * @return \Magento\Framework\Session\SessionManagerInterface
     */
    public function getSession()
    {
        return $this->_session;
    }

    /**
     * Get SID resolver
     *
     * @return \Magento\Framework\Session\SidResolverInterface
     */
    public function getSidResolver()
    {
        return $this->_sidResolver;
    }

    /**
     * Get scope config
     *
     * @return \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public function getScopeConfig()
    {
        return $this->_scopeConfig;
    }

    /**
     * Get translator
     *
     * @return \Magento\Framework\TranslateInterface
     */
    public function getTranslator()
    {
        return $this->_translator;
    }

    /**
     * Get inline translation status object
     *
     * @return \Magento\Framework\Translate\Inline\StateInterface
     */
    public function getInlineTranslation()
    {
        return $this->inlineTranslation;
    }

    /**
     * Get URL builder
     *
     * @return \Magento\Framework\UrlInterface
     */
    public function getUrlBuilder()
    {
        return $this->_urlBuilder;
    }

    /**
     * Get asset service
     *
     * @return \Magento\Framework\View\Asset\Repository
     */
    public function getAssetRepository()
    {
        return $this->_assetRepo;
    }

    /**
     * Get view config
     *
     * @return \Magento\Framework\View\ConfigInterface
     */
    public function getViewConfig()
    {
        return $this->_viewConfig;
    }

    /**
     * Get cache state
     *
     * @return \Magento\Framework\App\Cache\StateInterface
     */
    public function getCacheState()
    {
        return $this->_cacheState;
    }

    /**
     * Get logger
     *
     * @return \Magento\Framework\Logger
     */
    public function getLogger()
    {
        return $this->_logger;
    }

    /**
     * Get escaper
     *
     * @return \Magento\Framework\Escaper
     */
    public function getEscaper()
    {
        return $this->_escaper;
    }

    /**
     * Get filter manager
     *
     * @return \Magento\Framework\Filter\FilterManager
     */
    public function getFilterManager()
    {
        return $this->_filterManager;
    }

    /**
     * @return \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    public function getLocaleDate()
    {
        return $this->_localeDate;
    }
}
