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
namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Model\Resource\Product\Link\Collection;
use Magento\Catalog\Model\Resource\Product\Link\Product\Collection as ProductCollection;

/**
 * Catalog product link model
 *
 * @method \Magento\Catalog\Model\Resource\Product\Link _getResource()
 * @method \Magento\Catalog\Model\Resource\Product\Link getResource()
 * @method int getProductId()
 * @method \Magento\Catalog\Model\Product\Link setProductId(int $value)
 * @method int getLinkedProductId()
 * @method \Magento\Catalog\Model\Product\Link setLinkedProductId(int $value)
 * @method int getLinkTypeId()
 * @method \Magento\Catalog\Model\Product\Link setLinkTypeId(int $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Link extends \Magento\Framework\Model\AbstractModel
{
    const LINK_TYPE_RELATED = 1;

    const LINK_TYPE_UPSELL = 4;

    const LINK_TYPE_CROSSSELL = 5;

    /**
     * @var mixed
     */
    protected $_attributes = null;

    /**
     * Product collection factory
     *
     * @var \Magento\Catalog\Model\Resource\Product\Link\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * Link collection factory
     *
     * @var \Magento\Catalog\Model\Resource\Product\Link\CollectionFactory
     */
    protected $_linkCollectionFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\Resource\Product\Link\CollectionFactory $linkCollectionFactory
     * @param \Magento\Catalog\Model\Resource\Product\Link\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Resource\Product\Link\CollectionFactory $linkCollectionFactory,
        \Magento\Catalog\Model\Resource\Product\Link\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_linkCollectionFactory = $linkCollectionFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Catalog\Model\Resource\Product\Link');
    }

    /**
     * @return $this
     */
    public function useRelatedLinks()
    {
        $this->setLinkTypeId(self::LINK_TYPE_RELATED);
        return $this;
    }

    /**
     * @return $this
     */
    public function useUpSellLinks()
    {
        $this->setLinkTypeId(self::LINK_TYPE_UPSELL);
        return $this;
    }

    /**
     * @return $this
     */
    public function useCrossSellLinks()
    {
        $this->setLinkTypeId(self::LINK_TYPE_CROSSSELL);
        return $this;
    }

    /**
     * Retrieve table name for attribute type
     *
     * @param   string $type
     * @return  string
     */
    public function getAttributeTypeTable($type)
    {
        return $this->_getResource()->getAttributeTypeTable($type);
    }

    /**
     * Retrieve linked product collection
     *
     * @return ProductCollection
     */
    public function getProductCollection()
    {
        $collection = $this->_productCollectionFactory->create()->setLinkModel($this);
        return $collection;
    }

    /**
     * Retrieve link collection
     *
     * @return Collection
     */
    public function getLinkCollection()
    {
        $collection = $this->_linkCollectionFactory->create()->setLinkModel($this);
        return $collection;
    }

    /**
     * @param int $type
     * @return array
     */
    public function getAttributes($type = null)
    {
        if (is_null($type)) {
            $type = $this->getLinkTypeId();
        }
        if (!isset($this->_attributes[$type])) {
            $this->_attributes[$type] = $this->_getResource()->getAttributesByType($type);
        }

        return $this->_attributes[$type];
    }

    /**
     * Save data for product relations
     *
     * @param   \Magento\Catalog\Model\Product $product
     * @return  $this
     */
    public function saveProductRelations($product)
    {
        $data = $product->getRelatedLinkData();
        if (!is_null($data)) {
            $this->_getResource()->saveProductLinks($product, $data, self::LINK_TYPE_RELATED);
        }
        $data = $product->getUpSellLinkData();
        if (!is_null($data)) {
            $this->_getResource()->saveProductLinks($product, $data, self::LINK_TYPE_UPSELL);
        }
        $data = $product->getCrossSellLinkData();
        if (!is_null($data)) {
            $this->_getResource()->saveProductLinks($product, $data, self::LINK_TYPE_CROSSSELL);
        }
        return $this;
    }
}
