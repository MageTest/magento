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

use Magento\CatalogInventory\Api\Data\StockInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Class Stock
 * @package Magento\CatalogInventory\Model
 * @data-api
 */
class Stock extends AbstractExtensibleModel implements StockInterface
{
    /**
     * Stock entity code
     */
    const ENTITY = 'cataloginventory_stock';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $eventPrefix = 'cataloginventory_stock';

    /**
     * Parameter name in event
     * In observe method you can use $observer->getEvent()->getStock() in this case
     *
     * @var string
     */
    protected $eventObject = 'stock';

    const BACKORDERS_NO = 0;

    const BACKORDERS_YES_NONOTIFY = 1;

    const BACKORDERS_YES_NOTIFY = 2;

    const STOCK_OUT_OF_STOCK = 0;

    const STOCK_IN_STOCK = 1;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\CatalogInventory\Model\Resource\Stock');
    }

    /**
     * Retrieve stock identifier
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->_getData(self::ID);
    }

    /**
     * Retrieve website identifier
     *
     * @return int
     */
    public function getWebsiteId()
    {
        return $this->_getData(self::WEBSITE_ID);
    }

    /**
     * Retrieve Stock Name
     *
     * @return string
     */
    public function getStockName()
    {
        return $this->_getData(self::STOCK_NAME);
    }
}
