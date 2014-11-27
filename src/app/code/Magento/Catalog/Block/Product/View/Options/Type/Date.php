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
namespace Magento\Catalog\Block\Product\View\Options\Type;

use Magento\Framework\View\Element\AbstractBlock;

/**
 * Product options text type block
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Date extends \Magento\Catalog\Block\Product\View\Options\AbstractOptions
{
    /**
     * Fill date and time options with leading zeros or not
     *
     * @var boolean
     */
    protected $_fillLeadingZeros = true;

    /**
     * Catalog product option type date
     *
     * @var \Magento\Catalog\Model\Product\Option\Type\Date
     */
    protected $_catalogProductOptionTypeDate;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Core\Helper\Data $coreHelper
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Catalog\Model\Product\Option\Type\Date $catalogProductOptionTypeDate
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Core\Helper\Data $coreHelper,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Catalog\Model\Product\Option\Type\Date $catalogProductOptionTypeDate,
        array $data = array()
    ) {
        $this->_catalogProductOptionTypeDate = $catalogProductOptionTypeDate;
        parent::__construct($context, $coreHelper, $catalogData, $data);
    }

    /**
     * Use JS calendar settings
     *
     * @return boolean
     */
    public function useCalendar()
    {
        return $this->_catalogProductOptionTypeDate->useCalendar();
    }

    /**
     * Date input
     *
     * @return string Formatted Html
     */
    public function getDateHtml()
    {
        if ($this->useCalendar()) {
            return $this->getCalendarDateHtml();
        } else {
            return $this->getDropDownsDateHtml();
        }
    }

    /**
     * JS Calendar html
     *
     * @return string Formatted Html
     */
    public function getCalendarDateHtml()
    {
        $option = $this->getOption();
        $value = $this->getProduct()->getPreconfiguredValues()->getData('options/' . $option->getId() . '/date');

        $yearStart = $this->_catalogProductOptionTypeDate->getYearStart();
        $yearEnd = $this->_catalogProductOptionTypeDate->getYearEnd();

        $calendar = $this->getLayout()->createBlock(
            'Magento\Framework\View\Element\Html\Date'
        )->setId(
            'options_' . $this->getOption()->getId() . '_date'
        )->setName(
            'options[' . $this->getOption()->getId() . '][date]'
        )->setClass(
            'product-custom-option datetime-picker input-text'
        )->setImage(
            $this->getViewFileUrl('Magento_Core::calendar.gif')
        )->setDateFormat(
            $this->_localeDate->getDateFormat(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_SHORT)
        )->setValue(
            $value
        )->setYearsRange(
            $yearStart . ':' . $yearEnd
        );

        return $calendar->getHtml();
    }

    /**
     * Date (dd/mm/yyyy) html drop-downs
     *
     * @return string Formatted Html
     */
    public function getDropDownsDateHtml()
    {
        $fieldsSeparator = '&nbsp;';
        $fieldsOrder = $this->_catalogProductOptionTypeDate->getConfigData('date_fields_order');
        $fieldsOrder = str_replace(',', $fieldsSeparator, $fieldsOrder);

        $monthsHtml = $this->_getSelectFromToHtml('month', 1, 12);
        $daysHtml = $this->_getSelectFromToHtml('day', 1, 31);

        $yearStart = $this->_catalogProductOptionTypeDate->getYearStart();
        $yearEnd = $this->_catalogProductOptionTypeDate->getYearEnd();
        $yearsHtml = $this->_getSelectFromToHtml('year', $yearStart, $yearEnd);

        $translations = array('d' => $daysHtml, 'm' => $monthsHtml, 'y' => $yearsHtml);
        return strtr($fieldsOrder, $translations);
    }

    /**
     * Time (hh:mm am/pm) html drop-downs
     *
     * @return string Formatted Html
     */
    public function getTimeHtml()
    {
        if ($this->_catalogProductOptionTypeDate->is24hTimeFormat()) {
            $hourStart = 0;
            $hourEnd = 23;
            $dayPartHtml = '';
        } else {
            $hourStart = 1;
            $hourEnd = 12;
            $dayPartHtml = $this->_getHtmlSelect(
                'day_part'
            )->setOptions(
                array('am' => __('AM'), 'pm' => __('PM'))
            )->getHtml();
        }
        $hoursHtml = $this->_getSelectFromToHtml('hour', $hourStart, $hourEnd);
        $minutesHtml = $this->_getSelectFromToHtml('minute', 0, 59);

        return $hoursHtml . '&nbsp;<b>:</b>&nbsp;' . $minutesHtml . '&nbsp;' . $dayPartHtml;
    }

    /**
     * Return drop-down html with range of values
     *
     * @param string $name Id/name of html select element
     * @param int $from  Start position
     * @param int $to    End position
     * @param int|null $value Value selected
     * @return string Formatted Html
     */
    protected function _getSelectFromToHtml($name, $from, $to, $value = null)
    {
        $options = array(array('value' => '', 'label' => '-'));
        for ($i = $from; $i <= $to; $i++) {
            $options[] = array('value' => $i, 'label' => $this->_getValueWithLeadingZeros($i));
        }
        return $this->_getHtmlSelect($name, $value)->setOptions($options)->getHtml();
    }

    /**
     * HTML select element
     *
     * @param string $name Id/name of html select element
     * @param int|null $value
     * @return mixed
     */
    protected function _getHtmlSelect($name, $value = null)
    {
        $option = $this->getOption();

        $this->setSkipJsReloadPrice(1);

        // $require = $this->getOption()->getIsRequire() ? ' required-entry' : '';
        $require = '';
        $select = $this->getLayout()->createBlock(
            'Magento\Framework\View\Element\Html\Select'
        )->setId(
            'options_' . $this->getOption()->getId() . '_' . $name
        )->setClass(
            'product-custom-option datetime-picker' . $require
        )->setExtraParams()->setName(
            'options[' . $option->getId() . '][' . $name . ']'
        );

        $extraParams = 'style="width:auto"';
        if (!$this->getSkipJsReloadPrice()) {
            $extraParams .= ' onchange="opConfig.reloadPrice()"';
        }
        $select->setExtraParams($extraParams);

        if (is_null($value)) {
            $value = $this->getProduct()->getPreconfiguredValues()->getData(
                'options/' . $option->getId() . '/' . $name
            );
        }
        if (!is_null($value)) {
            $select->setValue($value);
        }

        return $select;
    }

    /**
     * Add Leading Zeros to number less than 10
     *
     * @param int $value
     * @return string|int
     */
    protected function _getValueWithLeadingZeros($value)
    {
        if (!$this->_fillLeadingZeros) {
            return $value;
        }
        return $value < 10 ? '0' . $value : $value;
    }
}
