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
namespace Magento\Catalog\Service\V1\Category\Tree;

/**
 * Class ReadService
 *
 * @package Magento\Catalog\Service\V1\Category
 */
class ReadService implements ReadServiceInterface
{
    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Service\V1\Data\Category\Tree
     */
    protected $categoryTree;

    /**
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Catalog\Service\V1\Data\Category\Tree $categoryTree
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Service\V1\Data\Category\Tree $categoryTree,
        \Magento\Framework\StoreManagerInterface $storeManager
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->categoryTree = $categoryTree;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function tree($rootCategoryId = null, $depth = null)
    {
        $category = null;
        if (!is_null($rootCategoryId)) {
            /** @var \Magento\Catalog\Model\Category $category */
            $category = $this->categoryFactory->create()->load($rootCategoryId);
            if (!$category->getId()) {
                throw new \Magento\Framework\Exception\NoSuchEntityException('Root Category does not exist');
            }
        }
        $result = $this->categoryTree->getTree($this->categoryTree->getRootNode($category), $depth);
        return $result;
    }
}
