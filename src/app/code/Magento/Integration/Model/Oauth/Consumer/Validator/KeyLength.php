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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Integration\Model\Oauth\Consumer\Validator;

/**
 * Validate OAuth keys
 */
class KeyLength extends \Zend_Validate_StringLength
{
    /**
     * Key name
     *
     * @var string
     */
    protected $_name = 'Key';

    /**
     * Sets validator options
     *
     * @param  integer|array|\Zend_Config $options
     */
    public function __construct($options = array())
    {
        if (!is_array($options)) {
            $options = func_get_args();
            if (!isset($options[1])) {
                $options[1] = 'utf-8';
            }
            parent::__construct($options[0], $options[0], $options[1]);
            return;
        } else {
            if (isset($options['length'])) {
                $options['max'] = $options['min'] = $options['length'];
            }
            if (isset($options['name'])) {
                $this->_name = $options['name'];
            }
        }
        parent::__construct($options);
    }

    /**
     * Init validation failure message template definitions
     *
     * @return $this
     */
    protected function _initMessageTemplates()
    {
        $_messageTemplates[self::TOO_LONG] = __("%name% '%value%' is too long. It must has length %min% symbols.");
        $_messageTemplates[self::TOO_SHORT] = __("%name% '%value%' is too short. It must has length %min% symbols.");

        return $this;
    }

    /**
     * Additional variables available for validation failure messages
     *
     * @var array
     */
    protected $_messageVariables = array('min' => '_min', 'max' => '_max', 'name' => '_name');

    /**
     * Set length
     *
     * @param int|null $length
     * @return $this
     */
    public function setLength($length)
    {
        parent::setMax($length);
        parent::setMin($length);
        return $this;
    }

    /**
     * Set length
     *
     * @return int
     */
    public function getLength()
    {
        return parent::getMin();
    }

    /**
     * Defined by \Zend_Validate_Interface
     *
     * Returns true if and only if the string length of $value is at least the min option and
     * no greater than the max option (when the max option is not null).
     *
     * @param  string $value
     * @return boolean
     * @throws \Exception
     */
    public function isValid($value)
    {
        $result = parent::isValid($value);
        if (!$result && isset($this->_messages[self::INVALID])) {
            throw new \Exception($this->_messages[self::INVALID]);
        }
        return $result;
    }

    /**
     * Set key name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }

    /**
     * Get key name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }
}
