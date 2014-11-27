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
namespace Magento\CatalogInventory\Model;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\CatalogInventory\Model\Spi\StockStateProviderInterface;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;

/**
 * Interface StockState
 * @package Magento\CatalogInventory\Model
 * @api
 */
class StockState implements StockStateInterface
{
    /**
     * @var StockStateProviderInterface
     */
    protected $stockStateProvider;

    /**
     * @var StockRegistryProviderInterface
     */
    protected $stockRegistryProvider;

    /**
     * @var StockConfigurationInterface
     */
    protected $stockConfiguration;

    /**
     * @param StockStateProviderInterface $stockStateProvider
     * @param StockRegistryProviderInterface $stockRegistryProvider
     * @param StockConfigurationInterface $stockConfiguration
     */
    public function __construct(
        StockStateProviderInterface $stockStateProvider,
        StockRegistryProviderInterface $stockRegistryProvider,
        StockConfigurationInterface $stockConfiguration
    ) {
        $this->stockStateProvider = $stockStateProvider;
        $this->stockRegistryProvider = $stockRegistryProvider;
        $this->stockConfiguration = $stockConfiguration;
    }

    /**
     * @param int $productId
     * @param int $websiteId
     * @return bool
     */
    public function verifyStock($productId, $websiteId = null)
    {
        if (is_null($websiteId)) {
            $websiteId = $this->stockConfiguration->getDefaultWebsiteId();
        }
        $stockItem = $this->stockRegistryProvider->getStockItem($productId, $websiteId);
        return $this->stockStateProvider->verifyStock($stockItem);
    }

    /**
     * @param int $productId
     * @param int $websiteId
     * @return bool
     */
    public function verifyNotification($productId, $websiteId = null)
    {
        if (is_null($websiteId)) {
            $websiteId = $this->stockConfiguration->getDefaultWebsiteId();
        }
        $stockItem = $this->stockRegistryProvider->getStockItem($productId, $websiteId);
        return $this->stockStateProvider->verifyNotification($stockItem);
    }

    /**
     * Check quantity
     *
     * @param int $productId
     * @param int|float $qty
     * @param int $websiteId
     * @exception \Magento\Framework\Model\Exception
     * @return bool
     */
    public function checkQty($productId, $qty, $websiteId = null)
    {
        if (is_null($websiteId)) {
            $websiteId = $this->stockConfiguration->getDefaultWebsiteId();
        }
        $stockItem = $this->stockRegistryProvider->getStockItem($productId, $websiteId);
        return $this->stockStateProvider->checkQty($stockItem, $qty);
    }

    /**
     * Returns suggested qty that satisfies qty increments and minQty/maxQty/minSaleQty/maxSaleQty conditions
     * or original qty if such value does not exist
     *
     * @param int $productId
     * @param int|float $qty
     * @param int $websiteId
     * @return int|float
     */
    public function suggestQty($productId, $qty, $websiteId = null)
    {
        if (is_null($websiteId)) {
            $websiteId = $this->stockConfiguration->getDefaultWebsiteId();
        }
        $stockItem = $this->stockRegistryProvider->getStockItem($productId, $websiteId);
        return $this->stockStateProvider->suggestQty($stockItem, $qty);
    }

    /**
     * Retrieve stock qty whether product is composite or no
     *
     * @param int $productId
     * @param int $websiteId
     * @return float
     */
    public function getStockQty($productId, $websiteId = null)
    {
        if (is_null($websiteId)) {
            $websiteId = $this->stockConfiguration->getDefaultWebsiteId();
        }
        $stockItem = $this->stockRegistryProvider->getStockItem($productId, $websiteId);
        return $this->stockStateProvider->getStockQty($stockItem);
    }

    /**
     * @param int $productId
     * @param int|float $qty
     * @param int $websiteId
     * @return \Magento\Framework\Object
     */
    public function checkQtyIncrements($productId, $qty, $websiteId = null)
    {
        if (is_null($websiteId)) {
            $websiteId = $this->stockConfiguration->getDefaultWebsiteId();
        }
        $stockItem = $this->stockRegistryProvider->getStockItem($productId, $websiteId);
        return $this->stockStateProvider->checkQtyIncrements($stockItem, $qty);
    }

    /**
     * @param int $productId
     * @param int|float $itemQty
     * @param int|float $qtyToCheck
     * @param int|float $origQty
     * @param int $websiteId
     * @return \Magento\Framework\Object
     */
    public function checkQuoteItemQty($productId, $itemQty, $qtyToCheck, $origQty, $websiteId = null)
    {
        if (is_null($websiteId)) {
            $websiteId = $this->stockConfiguration->getDefaultWebsiteId();
        }
        $stockItem = $this->stockRegistryProvider->getStockItem($productId, $websiteId);
        return $this->stockStateProvider->checkQuoteItemQty($stockItem, $itemQty, $qtyToCheck, $origQty);
    }
}
