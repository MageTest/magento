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
 * customers defined options
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Type;

class Select extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Type\AbstractType
{
    /**
     * @var string
     */
    protected $_template = 'catalog/product/edit/options/type/select.phtml';

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setCanEditPrice(true);
        $this->setCanReadPrice(true);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->addChild(
            'add_select_row_button',
            'Magento\Backend\Block\Widget\Button',
            array(
                'label' => __('Add New Row'),
                'class' => 'add add-select-row',
                'id' => 'product_option_${option_id}_add_select_row'
            )
        );

        $this->addChild(
            'delete_select_row_button',
            'Magento\Backend\Block\Widget\Button',
            array(
                'label' => __('Delete Row'),
                'class' => 'delete delete-select-row icon-btn',
                'id' => 'product_option_${id}_select_${select_id}_delete'
            )
        );

        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_select_row_button');
    }

    /**
     * @return string
     */
    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_select_row_button');
    }

    /**
     * Return select input for price type
     *
     * @param string $extraParams
     * @return string
     */
    public function getPriceTypeSelectHtml($extraParams = '')
    {
        $this->getChildBlock(
            'option_price_type'
        )->setData(
            'id',
            'product_option_${id}_select_${select_id}_price_type'
        )->setName(
            'product[options][${id}][values][${select_id}][price_type]'
        )->setExtraParams($extraParams);

        return parent::getPriceTypeSelectHtml();
    }
}
