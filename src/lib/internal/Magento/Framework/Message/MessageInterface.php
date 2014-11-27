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
namespace Magento\Framework\Message;

/**
 * Interface for message
 */
interface MessageInterface
{
    /**
     * Error type
     */
    const TYPE_ERROR = 'error';

    /**
     * Warning type
     */
    const TYPE_WARNING = 'warning';

    /**
     * Notice type
     */
    const TYPE_NOTICE = 'notice';

    /**
     * Success type
     */
    const TYPE_SUCCESS = 'success';

    /**
     * Getter message type
     *
     * @return string
     */
    public function getType();

    /**
     * Getter for text of message
     *
     * @return string
     */
    public function getText();

    /**
     * Setter message text
     *
     * @param string $text
     * @return $this
     */
    public function setText($text);

    /**
     * Setter message identifier
     *
     * @param string $identifier
     * @return $this
     */
    public function setIdentifier($identifier);

    /**
     * Getter message identifier
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * Setter for flag. Whether message is sticky
     *
     * @param bool $isSticky
     * @return $this
     */
    public function setIsSticky($isSticky);

    /**
     * Getter for flag. Whether message is sticky
     *
     * @return bool
     */
    public function getIsSticky();

    /**
     * Retrieve message as a string
     *
     * @return string
     */
    public function toString();
}
