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
namespace Magento\Reports\Block\Adminhtml\Sales\Tax;

/**
 * Adminhtml tax report grid block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Grid extends \Magento\Reports\Block\Adminhtml\Grid\AbstractGrid
{
    /**
     * @var string
     */
    protected $_columnGroupBy = 'period';

    /**
     * @var \Magento\Sales\Model\Order\ConfigFactory
     */
    protected $_configFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Reports\Model\Resource\Report\Collection\Factory $resourceFactory
     * @param \Magento\Reports\Model\Grouped\CollectionFactory $collectionFactory
     * @param \Magento\Reports\Helper\Data $reportsData
     * @param \Magento\Sales\Model\Order\ConfigFactory $configFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Reports\Model\Resource\Report\Collection\Factory $resourceFactory,
        \Magento\Reports\Model\Grouped\CollectionFactory $collectionFactory,
        \Magento\Reports\Helper\Data $reportsData,
        \Magento\Sales\Model\Order\ConfigFactory $configFactory,
        array $data = array()
    ) {
        $this->_configFactory = $configFactory;
        parent::__construct($context, $backendHelper, $resourceFactory, $collectionFactory, $reportsData, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setCountTotals(true);
        $this->setCountSubTotals(true);
    }

    /**
     * @return string
     */
    public function getResourceCollectionName()
    {
        return $this->getFilterData()->getData(
            'report_type'
        ) ==
            'updated_at_order' ? 'Magento\Tax\Model\Resource\Report\Updatedat\Collection' : 'Magento\Tax\Model\Resource\Report\Collection';
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'period',
            array(
                'header' => __('Interval'),
                'index' => 'period',
                'sortable' => false,
                'period_type' => $this->getPeriodType(),
                'renderer' => 'Magento\Reports\Block\Adminhtml\Sales\Grid\Column\Renderer\Date',
                'totals_label' => __('Total'),
                'subtotals_label' => __('Subtotal'),
                'html_decorators' => array('nobr'),
                'header_css_class' => 'col-period',
                'column_css_class' => 'col-period'
            )
        );

        $this->addColumn(
            'code',
            array(
                'header' => __('Tax'),
                'index' => 'code',
                'type' => 'string',
                'sortable' => false,
                'header_css_class' => 'col-tax-name',
                'column_css_class' => 'col-tax-name'
            )
        );

        $this->addColumn(
            'percent',
            array(
                'header' => __('Rate'),
                'index' => 'percent',
                'type' => 'number',
                'sortable' => false,
                'header_css_class' => 'col-rate',
                'column_css_class' => 'col-rate'
            )
        );

        $this->addColumn(
            'orders_count',
            array(
                'header' => __('Orders'),
                'index' => 'orders_count',
                'total' => 'sum',
                'type' => 'number',
                'sortable' => false,
                'header_css_class' => 'col-qty',
                'column_css_class' => 'col-qty'
            )
        );

        if ($this->getFilterData()->getStoreIds()) {
            $this->setStoreIds(explode(',', $this->getFilterData()->getStoreIds()));
        }
        $currencyCode = $this->getCurrentCurrencyCode();

        $this->addColumn(
            'tax_base_amount_sum',
            array(
                'header' => __('Tax Amount'),
                'type' => 'currency',
                'currency_code' => $currencyCode,
                'index' => 'tax_base_amount_sum',
                'total' => 'sum',
                'sortable' => false,
                'rate' => $this->getRate($currencyCode),
                'header_css_class' => 'col-tax-amount',
                'column_css_class' => 'col-tax-amount'
            )
        );

        $this->addExportType('*/*/exportTaxCsv', __('CSV'));
        $this->addExportType('*/*/exportTaxExcel', __('Excel XML'));

        return parent::_prepareColumns();
    }

    /**
     * Preparing collection.  Filter canceled statuses for orders in taxes
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $filterData = $this->getFilterData();
        if (!$filterData->hasData('order_statuses')) {
            $orderConfig = $this->_configFactory->create();
            $statusValues = array();
            $canceledStatuses = $orderConfig->getStateStatuses(\Magento\Sales\Model\Order::STATE_CANCELED);
            foreach ($orderConfig->getStatuses() as $code => $label) {
                if (!isset($canceledStatuses[$code])) {
                    $statusValues[] = $code;
                }
            }
            $filterData->setOrderStatuses($statusValues);
        }
        return parent::_prepareCollection();
    }
}
