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
namespace Magento\Bundle\Model;

/**
 * Bundle Selection Model
 *
 * @method int getOptionId()
 * @method \Magento\Bundle\Model\Selection setOptionId(int $value)
 * @method int getParentProductId()
 * @method \Magento\Bundle\Model\Selection setParentProductId(int $value)
 * @method int getProductId()
 * @method \Magento\Bundle\Model\Selection setProductId(int $value)
 * @method int getPosition()
 * @method \Magento\Bundle\Model\Selection setPosition(int $value)
 * @method int getIsDefault()
 * @method \Magento\Bundle\Model\Selection setIsDefault(int $value)
 * @method int getSelectionPriceType()
 * @method \Magento\Bundle\Model\Selection setSelectionPriceType(int $value)
 * @method float getSelectionPriceValue()
 * @method \Magento\Bundle\Model\Selection setSelectionPriceValue(float $value)
 * @method float getSelectionQty()
 * @method \Magento\Bundle\Model\Selection setSelectionQty(float $value)
 * @method int getSelectionCanChangeQty()
 * @method \Magento\Bundle\Model\Selection setSelectionCanChangeQty(int $value)
 */
class Selection extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Bundle\Model\Resource\Selection $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Bundle\Model\Resource\Selection $resource,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_catalogData = $catalogData;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Bundle\Model\Resource\Selection');
        parent::_construct();
    }

    /**
     * Processing object before save data
     *
     * @return $this
     */
    protected function _afterSave()
    {
        if (!$this->_catalogData->isPriceGlobal() && $this->getWebsiteId()) {
            $this->getResource()->saveSelectionPrice($this);

            if (!$this->getDefaultPriceScope()) {
                $this->unsSelectionPriceValue();
                $this->unsSelectionPriceType();
            }
        }
        parent::_afterSave();
    }
}
