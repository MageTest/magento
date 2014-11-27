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
namespace Magento\Sales\Block\Adminhtml\Order\Create;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Adminhtml sales order create totals block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Totals extends \Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate
{
    /**
     * Total renderers
     *
     * @var array
     */
    protected $_totalRenderers;

    /**
     * Default renderer
     *
     * @var string
     */
    protected $_defaultRenderer = 'Magento\Sales\Block\Adminhtml\Order\Create\Totals\DefaultTotals';

    /**
     * Sales data
     *
     * @var \Magento\Sales\Helper\Data
     */
    protected $_salesData = null;

    /**
     * Sales config
     *
     * @var \Magento\Sales\Model\Config
     */
    protected $_salesConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Sales\Helper\Data $salesData
     * @param \Magento\Sales\Model\Config $salesConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Sales\Helper\Data $salesData,
        \Magento\Sales\Model\Config $salesConfig,
        array $data = array()
    ) {
        $this->_salesData = $salesData;
        $this->_salesConfig = $salesConfig;
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $data);
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('sales_order_create_totals');
    }

    /**
     * Get totals
     *
     * @return array
     */
    public function getTotals()
    {
        return $this->getQuote()->getTotals();
    }

    /**
     * Get header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        return __('Order Totals');
    }

    /**
     * Get header css class
     *
     * @return string
     */
    public function getHeaderCssClass()
    {
        return 'head-money';
    }

    /**
     * Get total renderer
     *
     * @param string $code
     * @return bool|\Magento\Framework\View\Element\BlockInterface
     */
    protected function _getTotalRenderer($code)
    {
        $blockName = $code . '_total_renderer';
        $block = $this->getLayout()->getBlock($blockName);
        if (!$block) {
            $configRenderer = $this->_salesConfig->getTotalsRenderer('quote', 'totals', $code);
            if (empty($configRenderer)) {
                $block = $this->_defaultRenderer;
            } else {
                $block = $configRenderer;
            }

            $block = $this->getLayout()->createBlock($block, $blockName);
        }
        /**
         * Transfer totals to renderer
         */
        $block->setTotals($this->getTotals());
        return $block;
    }

    /**
     * Render total
     *
     * @param \Magento\Framework\Object $total
     * @param string|null $area
     * @param int $colspan
     * @return mixed
     */
    public function renderTotal($total, $area = null, $colspan = 1)
    {
        return $this->_getTotalRenderer(
            $total->getCode()
        )->setTotal(
            $total
        )->setColspan(
            $colspan
        )->setRenderingArea(
            is_null($area) ? -1 : $area
        )->toHtml();
    }

    /**
     * Render totals
     *
     * @param null $area
     * @param int $colspan
     * @return string
     */
    public function renderTotals($area = null, $colspan = 1)
    {
        $html = '';
        foreach ($this->getTotals() as $total) {
            if ($total->getArea() != $area && $area != -1) {
                continue;
            }
            $html .= $this->renderTotal($total, $area, $colspan);
        }
        return $html;
    }

    /**
     * Check allow to send new order confirmation email
     *
     * @return bool
     */
    public function canSendNewOrderConfirmationEmail()
    {
        return $this->_salesData->canSendNewOrderConfirmationEmail($this->getQuote()->getStoreId());
    }
}
