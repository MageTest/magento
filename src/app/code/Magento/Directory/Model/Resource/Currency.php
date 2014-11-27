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
 * Directory Currency Resource Model
 */
namespace Magento\Directory\Model\Resource;

class Currency extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Currency rate table
     *
     * @var string
     */
    protected $_currencyRateTable;

    /**
     * Currency rate cache array
     *
     * @var array
     */
    protected static $_rateCache;

    /**
     * Define main and currency rate tables
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('directory_currency', 'currency_code');
        $this->_currencyRateTable = $this->getTable('directory_currency_rate');
    }

    /**
     * Retrieve currency rate (only base=>allowed)
     *
     * @param \Magento\Directory\Model\Currency|string $currencyFrom
     * @param \Magento\Directory\Model\Currency|string $currencyTo
     * @return float
     */
    public function getRate($currencyFrom, $currencyTo)
    {
        if ($currencyFrom instanceof \Magento\Directory\Model\Currency) {
            $currencyFrom = $currencyFrom->getCode();
        }

        if ($currencyTo instanceof \Magento\Directory\Model\Currency) {
            $currencyTo = $currencyTo->getCode();
        }

        if ($currencyFrom == $currencyTo) {
            return 1;
        }

        if (!isset(self::$_rateCache[$currencyFrom][$currencyTo])) {
            $read = $this->_getReadAdapter();
            $bind = array(':currency_from' => strtoupper($currencyFrom), ':currency_to' => strtoupper($currencyTo));
            $select = $read->select()->from(
                $this->_currencyRateTable,
                'rate'
            )->where(
                'currency_from = :currency_from'
            )->where(
                'currency_to = :currency_to'
            );

            self::$_rateCache[$currencyFrom][$currencyTo] = $read->fetchOne($select, $bind);
        }

        return self::$_rateCache[$currencyFrom][$currencyTo];
    }

    /**
     * Retrieve currency rate (base=>allowed or allowed=>base)
     *
     * @param \Magento\Directory\Model\Currency|string $currencyFrom
     * @param \Magento\Directory\Model\Currency|string $currencyTo
     * @return float
     */
    public function getAnyRate($currencyFrom, $currencyTo)
    {
        if ($currencyFrom instanceof \Magento\Directory\Model\Currency) {
            $currencyFrom = $currencyFrom->getCode();
        }

        if ($currencyTo instanceof \Magento\Directory\Model\Currency) {
            $currencyTo = $currencyTo->getCode();
        }

        if ($currencyFrom == $currencyTo) {
            return 1;
        }

        if (!isset(self::$_rateCache[$currencyFrom][$currencyTo])) {
            $adapter = $this->_getReadAdapter();
            $bind = array(':currency_from' => strtoupper($currencyFrom), ':currency_to' => strtoupper($currencyTo));
            $select = $adapter->select()->from(
                $this->_currencyRateTable,
                'rate'
            )->where(
                'currency_from = :currency_from'
            )->where(
                'currency_to = :currency_to'
            );

            $rate = $adapter->fetchOne($select, $bind);
            if ($rate === false) {
                $select = $adapter->select()->from(
                    $this->_currencyRateTable,
                    new \Zend_Db_Expr('1/rate')
                )->where(
                    'currency_to = :currency_from'
                )->where(
                    'currency_from = :currency_to'
                );
                $rate = $adapter->fetchOne($select, $bind);
            }
            self::$_rateCache[$currencyFrom][$currencyTo] = $rate;
        }

        return self::$_rateCache[$currencyFrom][$currencyTo];
    }

    /**
     * Saving currency rates
     *
     * @param array $rates
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    public function saveRates($rates)
    {
        if (is_array($rates) && sizeof($rates) > 0) {
            $adapter = $this->_getWriteAdapter();
            $data = array();
            foreach ($rates as $currencyCode => $rate) {
                foreach ($rate as $currencyTo => $value) {
                    $value = abs($value);
                    if ($value == 0) {
                        continue;
                    }
                    $data[] = array('currency_from' => $currencyCode, 'currency_to' => $currencyTo, 'rate' => $value);
                }
            }
            if ($data) {
                $adapter->insertOnDuplicate($this->_currencyRateTable, $data, array('rate'));
            }
        } else {
            throw new \Magento\Framework\Model\Exception(__('Please correct the rates received'));
        }
    }

    /**
     * Retrieve config currency data by config path
     *
     * @param \Magento\Directory\Model\Currency $model
     * @param string $path
     * @return array
     */
    public function getConfigCurrencies($model, $path)
    {
        $adapter = $this->_getReadAdapter();
        $bind = array(':config_path' => $path);
        $select = $adapter->select()->from($this->getTable('core_config_data'))->where('path = :config_path');
        $result = array();
        $rowSet = $adapter->fetchAll($select, $bind);
        foreach ($rowSet as $row) {
            $result = array_merge($result, explode(',', $row['value']));
        }
        sort($result);

        return array_unique($result);
    }

    /**
     * Return currency rates
     *
     * @param string|array $currency
     * @param array $toCurrencies
     * @return array
     */
    public function getCurrencyRates($currency, $toCurrencies = null)
    {
        $rates = array();
        if (is_array($currency)) {
            foreach ($currency as $code) {
                $rates[$code] = $this->_getRatesByCode($code, $toCurrencies);
            }
        } else {
            $rates = $this->_getRatesByCode($currency, $toCurrencies);
        }

        return $rates;
    }

    /**
     * Protected method used by getCurrencyRates() method
     *
     * @param string $code
     * @param array $toCurrencies
     * @return array
     */
    protected function _getRatesByCode($code, $toCurrencies = null)
    {
        $adapter = $this->_getReadAdapter();
        $bind = array(':currency_from' => $code);
        $select = $adapter->select()->from(
            $this->getTable('directory_currency_rate'),
            array('currency_to', 'rate')
        )->where(
            'currency_from = :currency_from'
        )->where(
            'currency_to IN(?)',
            $toCurrencies
        );
        $rowSet = $adapter->fetchAll($select, $bind);
        $result = array();

        foreach ($rowSet as $row) {
            $result[$row['currency_to']] = $row['rate'];
        }

        return $result;
    }
}
