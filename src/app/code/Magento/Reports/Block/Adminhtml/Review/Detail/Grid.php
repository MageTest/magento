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
namespace Magento\Reports\Block\Adminhtml\Review\Detail;

/**
 * Adminhtml report reviews product grid block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Reports\Model\Resource\Review\CollectionFactory
     */
    protected $_reviewsFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Reports\Model\Resource\Review\CollectionFactory $reviewsFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Reports\Model\Resource\Review\CollectionFactory $reviewsFactory,
        array $data = array()
    ) {
        $this->_reviewsFactory = $reviewsFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('reviews_grid');
    }

    /**
     * Apply sorting and filtering to reports review collection
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_reviewsFactory->create()->addProductFilter((int)$this->getRequest()->getParam('id'));

        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    /**
     * Initialize grid report review columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('nickname', array('header' => __('Customer'), 'width' => '100px', 'index' => 'nickname'));

        $this->addColumn('title', array('header' => __('Title'), 'width' => '150px', 'index' => 'title'));

        $this->addColumn('detail', array('header' => __('Detail'), 'index' => 'detail'));

        $this->addColumn(
            'created_at',
            array('header' => __('Created'), 'index' => 'created_at', 'width' => '200px', 'type' => 'datetime')
        );

        $this->setFilterVisibility(false);

        $this->addExportType('*/*/exportProductDetailCsv', __('CSV'));
        $this->addExportType('*/*/exportProductDetailExcel', __('Excel XML'));

        return parent::_prepareColumns();
    }
}
