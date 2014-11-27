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
namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Tab;

/**
 * Product inventory data
 */
class Inventory extends \Magento\Backend\Block\Widget
{
    /**
     * @var string
     */
    protected $_template = 'catalog/product/tab/inventory.phtml';

    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $catalogData;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\CatalogInventory\Model\Source\Stock
     */
    protected $stock;

    /**
     * @var \Magento\CatalogInventory\Model\Source\Backorders
     */
    protected $backorders;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface
     */
    protected $stockConfiguration;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\CatalogInventory\Model\Source\Backorders $backorders
     * @param \Magento\CatalogInventory\Model\Source\Stock $stock
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Model\Source\Backorders $backorders,
        \Magento\CatalogInventory\Model\Source\Stock $stock,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        array $data = []
    ) {
        $this->stock = $stock;
        $this->backorders = $backorders;
        $this->catalogData = $catalogData;
        $this->coreRegistry = $coreRegistry;
        $this->stockRegistry = $stockRegistry;
        $this->stockConfiguration = $stockConfiguration;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getBackordersOption()
    {
        if ($this->catalogData->isModuleEnabled('Magento_CatalogInventory')) {
            return $this->backorders->toOptionArray();
        }

        return [];
    }

    /**
     * Retrieve stock option array
     *
     * @return array
     */
    public function getStockOption()
    {
        if ($this->catalogData->isModuleEnabled('Magento_CatalogInventory')) {
            return $this->stock->toOptionArray();
        }

        return [];
    }

    /**
     * Return current product instance
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->coreRegistry->registry('product');
    }

    /**
     * Retrieve Catalog Inventory  Stock Item Model
     *
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    public function getStockItem()
    {
        return $this->stockRegistry->getStockItem(
            $this->getProduct()->getId(),
            $this->getProduct()->getStore()->getWebsiteId()
        );
    }

    /**
     * @param string $field
     * @return string|null
     */
    public function getFieldValue($field)
    {
        $stockItem = $this->getStockItem();
        if ($stockItem->getId()) {
            $method = 'get' . \Magento\Framework\Api\SimpleDataObjectConverter::snakeCaseToUpperCamelCase($field);
            if (method_exists($stockItem, $method)) {
                return $stockItem->{$method}();
            }
        }
        return $this->stockConfiguration->getDefaultConfigValue($field);
    }

    /**
     * @param string $field
     * @return string|null
     */
    public function getConfigFieldValue($field)
    {
        $stockItem = $this->getStockItem();
        if ($stockItem->getId()) {
            $method = 'getUseConfig' . \Magento\Framework\Api\SimpleDataObjectConverter::snakeCaseToUpperCamelCase(
                $field
            );
            if (method_exists($stockItem, $method)) {
                return $stockItem->{$method}();
            }
        }
        return $this->stockConfiguration->getDefaultConfigValue($field);
    }

    /**
     * @param string $field
     * @return string|null
     */
    public function getDefaultConfigValue($field)
    {
        return $this->stockConfiguration->getDefaultConfigValue($field);
    }

    /**
     * Is readonly stock
     *
     * @return bool
     */
    public function isReadonly()
    {
        return $this->getProduct()->getInventoryReadonly();
    }

    /**
     * @return bool
     */
    public function isNew()
    {
        if ($this->getProduct()->getId()) {
            return false;
        }
        return true;
    }

    /**
     * @return string
     */
    public function getFieldSuffix()
    {
        return 'product';
    }

    /**
     * Check Whether product type can have fractional quantity or not
     *
     * @return bool
     */
    public function canUseQtyDecimals()
    {
        return $this->getProduct()->getTypeInstance()->canUseQtyDecimals();
    }

    /**
     * Check if product type is virtual
     *
     * @return bool
     */
    public function isVirtual()
    {
        return $this->getProduct()->getIsVirtual();
    }

    /**
     * @return bool
     */
    public function isSingleStoreMode()
    {
        return $this->_storeManager->isSingleStoreMode();
    }
}
