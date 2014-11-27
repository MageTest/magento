<?php
/**
 * Webapi module exception. Should be used in web API services implementation.
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
namespace Magento\Webapi;

use Magento\Framework\Exception\ErrorMessage;
use Magento\Webapi\Model\Soap\Fault;

class Exception extends \RuntimeException
{
    /**#@+
     * Error HTTP response codes.
     */
    const HTTP_BAD_REQUEST = 400;

    const HTTP_UNAUTHORIZED = 401;

    const HTTP_FORBIDDEN = 403;

    const HTTP_NOT_FOUND = 404;

    const HTTP_METHOD_NOT_ALLOWED = 405;

    const HTTP_NOT_ACCEPTABLE = 406;

    const HTTP_INTERNAL_ERROR = 500;

    /**#@-*/

    /**
     * Optional exception details.
     *
     * @var array
     */
    protected $_details;

    /**
     * HTTP status code associated with current exception.
     *
     * @var int
     */
    protected $_httpCode;

    /**
     * Exception name is used for SOAP faults generation.
     *
     * @var string
     */
    protected $_name;

    /**
     * Stacktrace
     *
     * @var string
     */
    protected $_stackTrace;

    /**
     * List of errors
     *
     * @var null|ErrorMessage[]
     */
    protected $_errors;

    /**
     * Initialize exception with HTTP code.
     *
     * @param string $message
     * @param int $code Error code
     * @param int $httpCode
     * @param array $details Additional exception details
     * @param string $name Exception name
     * @param ErrorMessage[]|null $errors Array of errors messages
     * @param string $stackTrace
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        $message,
        $code = 0,
        $httpCode = self::HTTP_BAD_REQUEST,
        array $details = array(),
        $name = '',
        $errors = null,
        $stackTrace = null
    ) {
        /** Only HTTP error codes are allowed. No success or redirect codes must be used. */
        if ($httpCode < 400 || $httpCode > 599) {
            throw new \InvalidArgumentException(sprintf('The specified HTTP code "%d" is invalid.', $httpCode));
        }
        parent::__construct($message, $code);
        $this->_httpCode = $httpCode;
        $this->_details = $details;
        $this->_name = $name;
        $this->_errors = $errors;
        $this->_stackTrace = $stackTrace;
    }

    /**
     * Retrieve current HTTP code.
     *
     * @return int
     */
    public function getHttpCode()
    {
        return $this->_httpCode;
    }

    /**
     * Identify exception originator: sender or receiver.
     *
     * @return string
     */
    public function getOriginator()
    {
        return $this->getHttpCode() < 500 ? Fault::FAULT_CODE_SENDER : Fault::FAULT_CODE_RECEIVER;
    }

    /**
     * Retrieve exception details.
     *
     * @return array
     */
    public function getDetails()
    {
        return $this->_details;
    }

    /**
     * Retrieve exception name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Retrieve list of errors.
     *
     * @return null|ErrorMessage[]
     */
    public function getErrors()
    {
        return $this->_errors;
    }
}
