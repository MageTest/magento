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
 * Config locale allowed currencies backend
 */
namespace Magento\Backend\Model\Config\Backend;

class Locale extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magento\Core\Model\Resource\Config\Data\CollectionFactory
     */
    protected $_configsFactory;

    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $_websiteFactory;

    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $_storeFactory;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $_localeCurrency;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Core\Model\Resource\Config\Data\CollectionFactory $configsFactory
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Core\Model\Resource\Config\Data\CollectionFactory $configsFactory,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_configsFactory = $configsFactory;
        $this->_websiteFactory = $websiteFactory;
        $this->_storeFactory = $storeFactory;
        $this->_localeCurrency = $localeCurrency;
        parent::__construct($context, $registry, $config, $resource, $resourceCollection, $data);
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _afterSave()
    {
        /** @var $collection \Magento\Core\Model\Resource\Config\Data\Collection */
        $collection = $this->_configsFactory->create();
        $collection->addPathFilter('currency/options');

        $values = explode(',', $this->getValue());
        $exceptions = array();

        foreach ($collection as $data) {
            $match = false;
            $scopeName = __('Default scope');

            if (preg_match('/(base|default)$/', $data->getPath(), $match)) {
                if (!in_array($data->getValue(), $values)) {
                    $currencyName = $this->_localeCurrency->getCurrency($data->getValue())->getName();
                    if ($match[1] == 'base') {
                        $fieldName = __('Base currency');
                    } else {
                        $fieldName = __('Display default currency');
                    }

                    switch ($data->getScope()) {
                        case \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT:
                            $scopeName = __('Default scope');
                            break;

                        case \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE:
                            /** @var $website \Magento\Store\Model\Website */
                            $website = $this->_websiteFactory->create();
                            $websiteName = $website->load($data->getScopeId())->getName();
                            $scopeName = __('website(%1) scope', $websiteName);
                            break;

                        case \Magento\Store\Model\ScopeInterface::SCOPE_STORE:
                            /** @var $store \Magento\Store\Model\Store */
                            $store = $this->_storeFactory->create();
                            $storeName = $store->load($data->getScopeId())->getName();
                            $scopeName = __('store(%1) scope', $storeName);
                            break;
                    }

                    $exceptions[] = __('Currency "%1" is used as %2 in %3.', $currencyName, $fieldName, $scopeName);
                }
            }
        }
        if ($exceptions) {
            throw new \Magento\Framework\Model\Exception(join("\n", $exceptions));
        }

        return $this;
    }
}
