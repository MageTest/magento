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
 * Category abstract block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Category;

use Magento\Store\Model\Store;
use Magento\Framework\Data\Tree\Node;

class AbstractCategory extends \Magento\Backend\Block\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Catalog\Model\Resource\Category\Tree
     */
    protected $_categoryTree;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var bool
     */
    protected $_withProductCount;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Catalog\Model\Resource\Category\Tree $categoryTree
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\Resource\Category\Tree $categoryTree,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        array $data = array()
    ) {
        $this->_categoryTree = $categoryTree;
        $this->_coreRegistry = $registry;
        $this->_categoryFactory = $categoryFactory;
        $this->_withProductCount = true;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve current category instance
     *
     * @return array|null
     */
    public function getCategory()
    {
        return $this->_coreRegistry->registry('category');
    }

    /**
     * @return int|string|null
     */
    public function getCategoryId()
    {
        if ($this->getCategory()) {
            return $this->getCategory()->getId();
        }
        return \Magento\Catalog\Model\Category::TREE_ROOT_ID;
    }

    /**
     * @return string
     */
    public function getCategoryName()
    {
        return $this->getCategory()->getName();
    }

    /**
     * @return mixed
     */
    public function getCategoryPath()
    {
        if ($this->getCategory()) {
            return $this->getCategory()->getPath();
        }
        return \Magento\Catalog\Model\Category::TREE_ROOT_ID;
    }

    /**
     * @return bool
     */
    public function hasStoreRootCategory()
    {
        $root = $this->getRoot();
        if ($root && $root->getId()) {
            return true;
        }
        return false;
    }

    /**
     * @return Store
     */
    public function getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store');
        return $this->_storeManager->getStore($storeId);
    }

    /**
     * @param mixed|null $parentNodeCategory
     * @param int $recursionLevel
     * @return Node|array|null
     */
    public function getRoot($parentNodeCategory = null, $recursionLevel = 3)
    {
        if (!is_null($parentNodeCategory) && $parentNodeCategory->getId()) {
            return $this->getNode($parentNodeCategory, $recursionLevel);
        }
        $root = $this->_coreRegistry->registry('root');
        if (is_null($root)) {
            $storeId = (int)$this->getRequest()->getParam('store');

            if ($storeId) {
                $store = $this->_storeManager->getStore($storeId);
                $rootId = $store->getRootCategoryId();
            } else {
                $rootId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
            }

            $tree = $this->_categoryTree->load(null, $recursionLevel);

            if ($this->getCategory()) {
                $tree->loadEnsuredNodes($this->getCategory(), $tree->getNodeById($rootId));
            }

            $tree->addCollectionData($this->getCategoryCollection());

            $root = $tree->getNodeById($rootId);

            if ($root && $rootId != \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
                $root->setIsVisible(true);
            } elseif ($root && $root->getId() == \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
                $root->setName(__('Root'));
            }

            $this->_coreRegistry->register('root', $root);
        }

        return $root;
    }

    /**
     * @return int
     */
    protected function _getDefaultStoreId()
    {
        return \Magento\Store\Model\Store::DEFAULT_STORE_ID;
    }

    /**
     * @return \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
     */
    public function getCategoryCollection()
    {
        $storeId = $this->getRequest()->getParam('store', $this->_getDefaultStoreId());
        $collection = $this->getData('category_collection');
        if (is_null($collection)) {
            $collection = $this->_categoryFactory->create()->getCollection();

            $collection->addAttributeToSelect(
                'name'
            )->addAttributeToSelect(
                'is_active'
            )->setProductStoreId(
                $storeId
            )->setLoadProductCount(
                $this->_withProductCount
            )->setStoreId(
                $storeId
            );

            $this->setData('category_collection', $collection);
        }
        return $collection;
    }

    /**
     * Get and register categories root by specified categories IDs
     *
     * IDs can be arbitrary set of any categories ids.
     * Tree with minimal required nodes (all parents and neighbours) will be built.
     * If ids are empty, default tree with depth = 2 will be returned.
     *
     * @param array $ids
     * @return mixed
     */
    public function getRootByIds($ids)
    {
        $root = $this->_coreRegistry->registry('root');
        if (null === $root) {
            $ids = $this->_categoryTree->getExistingCategoryIdsBySpecifiedIds($ids);
            $tree = $this->_categoryTree->loadByIds($ids);
            $rootId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
            $root = $tree->getNodeById($rootId);
            if ($root && $rootId != \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
                $root->setIsVisible(true);
            } elseif ($root && $root->getId() == \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
                $root->setName(__('Root'));
            }

            $tree->addCollectionData($this->getCategoryCollection());
            $this->_coreRegistry->register('root', $root);
        }
        return $root;
    }

    /**
     * @param mixed $parentNodeCategory
     * @param int $recursionLevel
     * @return Node
     */
    public function getNode($parentNodeCategory, $recursionLevel = 2)
    {
        $nodeId = $parentNodeCategory->getId();
        $parentId = $parentNodeCategory->getParentId();

        $node = $this->_categoryTree->loadNode($nodeId);
        $node->loadChildren($recursionLevel);

        if ($node && $nodeId != \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
            $node->setIsVisible(true);
        } elseif ($node && $node->getId() == \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
            $node->setName(__('Root'));
        }

        $this->_categoryTree->addCollectionData($this->getCategoryCollection());

        return $node;
    }

    /**
     * @param array $args
     * @return string
     */
    public function getSaveUrl(array $args = array())
    {
        $params = array('_current' => true);
        $params = array_merge($params, $args);
        return $this->getUrl('catalog/*/save', $params);
    }

    /**
     * @return string
     */
    public function getEditUrl()
    {
        return $this->getUrl(
            'catalog/category/edit',
            array('_current' => true, 'store' => null, '_query' => false, 'id' => null, 'parent' => null)
        );
    }

    /**
     * Return ids of root categories as array
     *
     * @return array
     */
    public function getRootIds()
    {
        $ids = $this->getData('root_ids');
        if (is_null($ids)) {
            $ids = array();
            foreach ($this->_storeManager->getGroups() as $store) {
                $ids[] = $store->getRootCategoryId();
            }
            $this->setData('root_ids', $ids);
        }
        return $ids;
    }
}
