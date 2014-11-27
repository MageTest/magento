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
namespace Magento\Catalog\Model;

/**
 * Catalog Custom Category design Model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Design extends \Magento\Framework\Model\AbstractModel
{
    const APPLY_FOR_PRODUCT = 1;

    const APPLY_FOR_CATEGORY = 2;

    /**
     * Design package instance
     *
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $_design = null;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_localeDate = $localeDate;
        $this->_design = $design;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Apply custom design
     *
     * @param string $design
     * @return $this
     */
    public function applyCustomDesign($design)
    {
        $this->_design->setDesignTheme($design);
        return $this;
    }

    /**
     * Get custom layout settings
     *
     * @param \Magento\Catalog\Model\Category|\Magento\Catalog\Model\Product $object
     * @return \Magento\Framework\Object
     */
    public function getDesignSettings($object)
    {
        if ($object instanceof \Magento\Catalog\Model\Product) {
            $currentCategory = $object->getCategory();
        } else {
            $currentCategory = $object;
        }

        $category = null;
        if ($currentCategory) {
            $category = $currentCategory->getParentDesignCategory($currentCategory);
        }

        if ($object instanceof \Magento\Catalog\Model\Product) {
            if ($category && $category->getCustomApplyToProducts()) {
                return $this->_mergeSettings($this->_extractSettings($category), $this->_extractSettings($object));
            } else {
                return $this->_extractSettings($object);
            }
        } else {
            return $this->_extractSettings($category);
        }
    }

    /**
     * Extract custom layout settings from category or product object
     *
     * @param \Magento\Catalog\Model\Category|\Magento\Catalog\Model\Product $object
     * @return \Magento\Framework\Object
     */
    protected function _extractSettings($object)
    {
        $settings = new \Magento\Framework\Object();
        if (!$object) {
            return $settings;
        }
        $date = $object->getCustomDesignDate();
        if (array_key_exists(
            'from',
            $date
        ) && array_key_exists(
            'to',
            $date
        ) && $this->_localeDate->isScopeDateInInterval(
            null,
            $date['from'],
            $date['to']
        )
        ) {
            $settings->setCustomDesign(
                $object->getCustomDesign()
            )->setPageLayout(
                $object->getPageLayout()
            )->setLayoutUpdates(
                (array)$object->getCustomLayoutUpdate()
            );
        }
        return $settings;
    }

    /**
     * Merge custom design settings
     *
     * @param \Magento\Framework\Object $categorySettings
     * @param \Magento\Framework\Object $productSettings
     * @return \Magento\Framework\Object
     */
    protected function _mergeSettings($categorySettings, $productSettings)
    {
        if ($productSettings->getCustomDesign()) {
            $categorySettings->setCustomDesign($productSettings->getCustomDesign());
        }
        if ($productSettings->getPageLayout()) {
            $categorySettings->setPageLayout($productSettings->getPageLayout());
        }
        if ($productSettings->getLayoutUpdates()) {
            $update = array_merge($categorySettings->getLayoutUpdates(), $productSettings->getLayoutUpdates());
            $categorySettings->setLayoutUpdates($update);
        }
        return $categorySettings;
    }
}
