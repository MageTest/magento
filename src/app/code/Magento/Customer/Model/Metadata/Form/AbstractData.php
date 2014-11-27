<?php
/**
 * Form Element Abstract Data Model
 *
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
namespace Magento\Customer\Model\Metadata\Form;

use Magento\Framework\Api\ArrayObjectSearch;

abstract class AbstractData
{
    /**
     * Request Scope name
     *
     * @var string
     */
    protected $_requestScope;

    /**
     * Scope visibility flag
     *
     * @var boolean
     */
    protected $_requestScopeOnly = true;

    /**
     * Is AJAX request flag
     *
     * @var boolean
     */
    protected $_isAjax = false;

    /**
     * Array of full extracted data
     * Needed for depends attributes
     *
     * @var array
     */
    protected $_extractedData = array();

    /**
     * Date filter format
     *
     * @var string
     */
    protected $_dateFilterFormat;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var \Magento\Framework\Logger
     */
    protected $_logger;

    /**
     * @var \Magento\Customer\Service\V1\Data\Eav\AttributeMetadata
     */
    protected $_attribute;

    /**
     * @var string|int|bool
     */
    protected $_value;

    /**
     * @var  string
     */
    protected $_entityTypeCode;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Customer\Service\V1\Data\Eav\AttributeMetadata $attribute
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param string|int|bool $value
     * @param string $entityTypeCode
     * @param bool $isAjax
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Logger $logger,
        \Magento\Customer\Service\V1\Data\Eav\AttributeMetadata $attribute,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        $value,
        $entityTypeCode,
        $isAjax = false
    ) {
        $this->_localeDate = $localeDate;
        $this->_logger = $logger;
        $this->_attribute = $attribute;
        $this->_localeResolver = $localeResolver;
        $this->_value = $value;
        $this->_entityTypeCode = $entityTypeCode;
        $this->_isAjax = $isAjax;
    }

    /**
     * Return Attribute instance
     *
     * @return \Magento\Customer\Service\V1\Data\Eav\AttributeMetadata
     * @throws \Magento\Framework\Model\Exception
     */
    public function getAttribute()
    {
        if (!$this->_attribute) {
            throw new \Magento\Framework\Model\Exception(__('Attribute object is undefined'));
        }
        return $this->_attribute;
    }

    /**
     * Set Request scope
     *
     * @param string $scope
     * @return $this
     */
    public function setRequestScope($scope)
    {
        $this->_requestScope = $scope;
        return $this;
    }

    /**
     * Set scope visibility
     * Search value only in scope or search value in scope and global
     *
     * @param boolean $flag
     * @return $this
     */
    public function setRequestScopeOnly($flag)
    {
        $this->_requestScopeOnly = (bool)$flag;
        return $this;
    }

    /**
     * Set array of full extracted data
     *
     * @param array $data
     * @return $this
     */
    public function setExtractedData(array $data)
    {
        $this->_extractedData = $data;
        return $this;
    }

    /**
     * Return extracted data
     *
     * @param string $index
     * @return array|null
     */
    public function getExtractedData($index = null)
    {
        if (!is_null($index)) {
            if (isset($this->_extractedData[$index])) {
                return $this->_extractedData[$index];
            }
            return null;
        }
        return $this->_extractedData;
    }

    /**
     * Apply attribute input filter to value
     *
     * @param string $value
     * @return string|bool
     */
    protected function _applyInputFilter($value)
    {
        if ($value === false) {
            return false;
        }

        $filter = $this->_getFormFilter();
        if ($filter) {
            $value = $filter->inputFilter($value);
        }

        return $value;
    }

    /**
     * Return Data Form Input/Output Filter
     *
     * @return \Magento\Framework\Data\Form\Filter\FilterInterface|false
     */
    protected function _getFormFilter()
    {
        $filterCode = $this->getAttribute()->getInputFilter();
        if ($filterCode) {
            $filterClass = 'Magento\Framework\Data\Form\Filter\\' . ucfirst($filterCode);
            if ($filterCode == 'date') {
                $filter = new $filterClass($this->_dateFilterFormat(), $this->_localeResolver->getLocale());
            } else {
                $filter = new $filterClass();
            }
            return $filter;
        }
        return false;
    }

    /**
     * Get/Set/Reset date filter format
     *
     * @param string|null|false $format
     * @return $this|string
     */
    protected function _dateFilterFormat($format = null)
    {
        if (is_null($format)) {
            // get format
            if (is_null($this->_dateFilterFormat)) {
                $this->_dateFilterFormat = \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_SHORT;
            }
            return $this->_localeDate->getDateFormat($this->_dateFilterFormat);
        } else if ($format === false) {
            // reset value
            $this->_dateFilterFormat = null;
            return $this;
        }

        $this->_dateFilterFormat = $format;
        return $this;
    }

    /**
     * Apply attribute output filter to value
     *
     * @param string $value
     * @return string
     */
    protected function _applyOutputFilter($value)
    {
        $filter = $this->_getFormFilter();
        if ($filter) {
            $value = $filter->outputFilter($value);
        }

        return $value;
    }

    /**
     * Validate value by attribute input validation rule
     *
     * @param string $value
     * @return array|true
     */
    protected function _validateInputRule($value)
    {
        // skip validate empty value
        if (empty($value)) {
            return true;
        }

        $label = $this->getAttribute()->getStoreLabel();
        $validateRules = $this->getAttribute()->getValidationRules();

        $inputValidation = ArrayObjectSearch::getArrayElementByName(
            $validateRules,
            'input_validation'
        );

        if (!is_null($inputValidation)) {
            switch ($inputValidation) {
                case 'alphanumeric':
                    $validator = new \Zend_Validate_Alnum(true);
                    $validator->setMessage(__('"%1" invalid type entered.', $label), \Zend_Validate_Alnum::INVALID);
                    $validator->setMessage(
                        __('"%1" contains non-alphabetic or non-numeric characters.', $label),
                        \Zend_Validate_Alnum::NOT_ALNUM
                    );
                    $validator->setMessage(__('"%1" is an empty string.', $label), \Zend_Validate_Alnum::STRING_EMPTY);
                    if (!$validator->isValid($value)) {
                        return $validator->getMessages();
                    }
                    break;
                case 'numeric':
                    $validator = new \Zend_Validate_Digits();
                    $validator->setMessage(__('"%1" invalid type entered.', $label), \Zend_Validate_Digits::INVALID);
                    $validator->setMessage(
                        __('"%1" contains non-numeric characters.', $label),
                        \Zend_Validate_Digits::NOT_DIGITS
                    );
                    $validator->setMessage(
                        __('"%1" is an empty string.', $label),
                        \Zend_Validate_Digits::STRING_EMPTY
                    );
                    if (!$validator->isValid($value)) {
                        return $validator->getMessages();
                    }
                    break;
                case 'alpha':
                    $validator = new \Zend_Validate_Alpha(true);
                    $validator->setMessage(__('"%1" invalid type entered.', $label), \Zend_Validate_Alpha::INVALID);
                    $validator->setMessage(
                        __('"%1" contains non-alphabetic characters.', $label),
                        \Zend_Validate_Alpha::NOT_ALPHA
                    );
                    $validator->setMessage(__('"%1" is an empty string.', $label), \Zend_Validate_Alpha::STRING_EMPTY);
                    if (!$validator->isValid($value)) {
                        return $validator->getMessages();
                    }
                    break;
                case 'email':
                    /**
                    __("'%value%' appears to be a DNS hostname but the given punycode notation cannot be decoded")
                    __("Invalid type given. String expected")
                    __("'%value%' appears to be a DNS hostname but contains a dash in an invalid position")
                    __("'%value%' does not match the expected structure for a DNS hostname")
                    __("'%value%' appears to be a DNS hostname but cannot match against hostname schema for TLD '%tld%'")
                    __("'%value%' does not appear to be a valid local network name")
                    __("'%value%' does not appear to be a valid URI hostname")
                    __("'%value%' appears to be an IP address, but IP addresses are not allowed")
                    __("'%value%' appears to be a local network name but local network names are not allowed")
                    __("'%value%' appears to be a DNS hostname but cannot extract TLD part")
                    __("'%value%' appears to be a DNS hostname but cannot match TLD against known list")
                    */
                    $validator = new \Zend_Validate_EmailAddress();
                    $validator->setMessage(
                        __('"%1" invalid type entered.', $label),
                        \Zend_Validate_EmailAddress::INVALID
                    );
                    $validator->setMessage(
                        __('"%1" is not a valid email address.', $label),
                        \Zend_Validate_EmailAddress::INVALID_FORMAT
                    );
                    $validator->setMessage(
                        __('"%1" is not a valid hostname.', $label),
                        \Zend_Validate_EmailAddress::INVALID_HOSTNAME
                    );
                    $validator->setMessage(
                        __('"%1" is not a valid hostname.', $label),
                        \Zend_Validate_EmailAddress::INVALID_MX_RECORD
                    );
                    $validator->setMessage(
                        __('"%1" is not a valid hostname.', $label),
                        \Zend_Validate_EmailAddress::INVALID_MX_RECORD
                    );
                    $validator->setMessage(
                        __('"%1" is not a valid email address.', $label),
                        \Zend_Validate_EmailAddress::DOT_ATOM
                    );
                    $validator->setMessage(
                        __('"%1" is not a valid email address.', $label),
                        \Zend_Validate_EmailAddress::QUOTED_STRING
                    );
                    $validator->setMessage(
                        __('"%1" is not a valid email address.', $label),
                        \Zend_Validate_EmailAddress::INVALID_LOCAL_PART
                    );
                    $validator->setMessage(
                        __('"%1" exceeds the allowed length.', $label),
                        \Zend_Validate_EmailAddress::LENGTH_EXCEEDED
                    );
                    $validator->setMessage(
                        __("'%value%' appears to be an IP address, but IP addresses are not allowed."),
                        \Zend_Validate_Hostname::IP_ADDRESS_NOT_ALLOWED
                    );
                    $validator->setMessage(
                        __("'%value%' appears to be a DNS hostname but cannot match TLD against known list."),
                        \Zend_Validate_Hostname::UNKNOWN_TLD
                    );
                    $validator->setMessage(
                        __("'%value%' appears to be a DNS hostname but contains a dash in an invalid position."),
                        \Zend_Validate_Hostname::INVALID_DASH
                    );
                    $validator->setMessage(
                        __(
                            "'%value%' appears to be a DNS hostname but cannot match against hostname schema for TLD '%tld%'."
                        ),
                        \Zend_Validate_Hostname::INVALID_HOSTNAME_SCHEMA
                    );
                    $validator->setMessage(
                        __("'%value%' appears to be a DNS hostname but cannot extract TLD part."),
                        \Zend_Validate_Hostname::UNDECIPHERABLE_TLD
                    );
                    $validator->setMessage(
                        __("'%value%' does not appear to be a valid local network name."),
                        \Zend_Validate_Hostname::INVALID_LOCAL_NAME
                    );
                    $validator->setMessage(
                        __("'%value%' appears to be a local network name but local network names are not allowed."),
                        \Zend_Validate_Hostname::LOCAL_NAME_NOT_ALLOWED
                    );
                    $validator->setMessage(
                        __(
                            "'%value%' appears to be a DNS hostname but the given punycode notation cannot be decoded."
                        ),
                        \Zend_Validate_Hostname::CANNOT_DECODE_PUNYCODE
                    );
                    if (!$validator->isValid($value)) {
                        return array_unique($validator->getMessages());
                    }
                    break;
                case 'url':
                    $parsedUrl = parse_url($value);
                    if ($parsedUrl === false || empty($parsedUrl['scheme']) || empty($parsedUrl['host'])) {
                        return array(__('"%1" is not a valid URL.', $label));
                    }
                    $validator = new \Zend_Validate_Hostname();
                    if (!$validator->isValid($parsedUrl['host'])) {
                        return array(__('"%1" is not a valid URL.', $label));
                    }
                    break;
                case 'date':
                    $validator = new \Zend_Validate_Date(\Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT);
                    $validator->setMessage(__('"%1" invalid type entered.', $label), \Zend_Validate_Date::INVALID);
                    $validator->setMessage(__('"%1" is not a valid date.', $label), \Zend_Validate_Date::INVALID_DATE);
                    $validator->setMessage(
                        __('"%1" does not fit the entered date format.', $label),
                        \Zend_Validate_Date::FALSEFORMAT
                    );
                    if (!$validator->isValid($value)) {
                        return array_unique($validator->getMessages());
                    }

                    break;
            }
        }
        return true;
    }

    /**
     * Return is AJAX Request
     *
     * @return boolean
     */
    public function getIsAjaxRequest()
    {
        return $this->_isAjax;
    }

    /**
     * Return Original Attribute value from Request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return mixed
     */
    protected function _getRequestValue(\Magento\Framework\App\RequestInterface $request)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        if ($this->_requestScope) {
            if (strpos($this->_requestScope, '/') !== false) {
                $params = $request->getParams();
                $parts = explode('/', $this->_requestScope);
                foreach ($parts as $part) {
                    if (isset($params[$part])) {
                        $params = $params[$part];
                    } else {
                        $params = array();
                    }
                }
            } else {
                $params = $request->getParam($this->_requestScope);
            }

            if (isset($params[$attrCode])) {
                $value = $params[$attrCode];
            } else {
                $value = false;
            }

            if (!$this->_requestScopeOnly && $value === false) {
                $value = $request->getParam($attrCode, false);
            }
        } else {
            $value = $request->getParam($attrCode, false);
        }
        return $value;
    }

    /**
     * Extract data from request and return value
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return array|string
     */
    abstract public function extractValue(\Magento\Framework\App\RequestInterface $request);

    /**
     * Validate data
     *
     * @param array|string|null $value
     * @return array|bool
     * @throws \Magento\Framework\Model\Exception
     */
    abstract public function validateValue($value);

    /**
     * Export attribute value
     *
     * @param array|string $value
     * @return array|string|bool
     */
    abstract public function compactValue($value);

    /**
     * Restore attribute value from SESSION
     *
     * @param array|string $value
     * @return array|string|bool
     */
    abstract public function restoreValue($value);

    /**
     * Return formatted attribute value from entity model
     *
     * @param string $format
     * @return string|array
     */
    abstract public function outputValue(
        $format = \Magento\Customer\Model\Metadata\ElementFactory::OUTPUT_FORMAT_TEXT
    );
}
