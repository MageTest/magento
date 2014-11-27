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

namespace Magento\Framework\Stdlib\DateTime;

/**
 * Date conversion model
 *
 * @author Magento Core Team <core@magentocommerce.com>
 */
class DateTime
{
    /**
     * Current config offset in seconds
     *
     * @var int
     */
    private $_offset = 0;

    /**
     * @var TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @param TimezoneInterface $localeDate
     */
    public function __construct(TimezoneInterface $localeDate)
    {
        $this->_localeDate = $localeDate;
        $this->_offset = $this->calculateOffset($this->_localeDate->getConfigTimezone());
    }

    /**
     * Calculates timezone offset
     *
     * @param  string|null $timezone
     * @return int offset between timezone and gmt
     */
    public function calculateOffset($timezone = null)
    {
        $result = true;
        $offset = 0;
        if ($timezone !== null) {
            $oldZone = @date_default_timezone_get();
            $result = date_default_timezone_set($timezone);
        }
        if ($result === true) {
            $offset = (int)date('Z');
        }
        if ($timezone !== null) {
            date_default_timezone_set($oldZone);
        }
        return $offset;
    }

    /**
     * Forms GMT date
     *
     * @param  string $format
     * @param  int|string $input date in current timezone
     * @return string
     */
    public function gmtDate($format = null, $input = null)
    {
        if ($format === null) {
            $format = 'Y-m-d H:i:s';
        }
        $date = $this->gmtTimestamp($input);
        if ($date === false) {
            return false;
        }
        $result = date($format, $date);
        return $result;
    }

    /**
     * Converts input date into date with timezone offset
     * Input date must be in GMT timezone
     *
     * @param  string $format
     * @param  int|string $input date in GMT timezone
     * @return string
     */
    public function date($format = null, $input = null)
    {
        if ($format === null) {
            $format = 'Y-m-d H:i:s';
        }
        $result = date($format, $this->timestamp($input));
        return $result;
    }

    /**
     * Forms GMT timestamp
     *
     * @param  int|string $input date in current timezone
     * @return int
     */
    public function gmtTimestamp($input = null)
    {
        if ($input === null) {
            return gmdate('U');
        } elseif (is_numeric($input)) {
            $result = $input;
        } else {
            $result = strtotime($input);
        }
        if ($result === false) {
            // strtotime() unable to parse string (it's not a date or has incorrect format)
            return false;
        }
        $date = $this->_localeDate->date($result);
        $timestamp = $date->get(\Zend_Date::TIMESTAMP) - $date->get(\Zend_Date::TIMEZONE_SECS);
        unset($date);
        return $timestamp;
    }

    /**
     * Converts input date into timestamp with timezone offset
     * Input date must be in GMT timezone
     *
     * @param  int|string $input date in GMT timezone
     * @return int
     */
    public function timestamp($input = null)
    {
        if ($input === null) {
            $result = $this->gmtTimestamp();
        } elseif (is_numeric($input)) {
            $result = $input;
        } else {
            $result = strtotime($input);
        }
        $date = $this->_localeDate->date($result);
        $timestamp = $date->get(\Zend_Date::TIMESTAMP) + $date->get(\Zend_Date::TIMEZONE_SECS);
        unset($date);
        return $timestamp;
    }

    /**
     * Get current timezone offset in seconds/minutes/hours
     *
     * @param  string $type
     * @return int
     */
    public function getGmtOffset($type = 'seconds')
    {
        $result = $this->_offset;
        switch ($type) {
            case 'seconds':
            default:
                break;
            case 'minutes':
                $result = $result / 60;
                break;
            case 'hours':
                $result = $result / 60 / 60;
                break;
        }
        return $result;
    }
}
