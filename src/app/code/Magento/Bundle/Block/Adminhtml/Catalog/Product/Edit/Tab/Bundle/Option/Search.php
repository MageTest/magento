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
namespace Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option;

/**
 * Bundle selection product block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Search extends \Magento\Backend\Block\Widget
{
    /**
     * @var string
     */
    protected $_template = 'product/edit/bundle/option/search.phtml';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->setId('bundle_option_selection_search');
    }

    /**
     * Create search grid
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'grid',
            $this->getLayout()->createBlock(
                'Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option\Search\Grid',
                'adminhtml.catalog.product.edit.tab.bundle.option.search.grid'
            )
        );
        return parent::_prepareLayout();
    }

    /**
     * Prepare search grid
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->getChildBlock('grid')->setIndex($this->getIndex())->setFirstShow($this->getFirstShow());
        return parent::_beforeToHtml();
    }
}
