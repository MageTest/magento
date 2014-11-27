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

namespace Magento\Framework\Notification;

/**
 * Default notifiers. Iterates through all registered notifiers to process message
 *
 * Class NotifierPool
 */
class NotifierPool implements NotifierInterface
{
    /**
     * @var NotifierList
     */
    protected $notifierList;

    /**
     * @param NotifierList $notifierList
     */
    public function __construct(NotifierList $notifierList)
    {
        $this->notifierList = $notifierList;
    }

    /**
     * Add new message
     *
     * @param int $severity
     * @param string $title
     * @param string|string[] $description
     * @param string $url
     * @param bool $isInternal
     * @throws \Magento\Framework\Model\Exception
     * @return $this
     */
    public function add($severity, $title, $description, $url = '', $isInternal = true)
    {
        foreach ($this->notifierList->asArray() as $notifier) {
            $notifier->add($severity, $title, $description, $url, $isInternal);
        }
        return $this;
    }

    /**
     * Add critical severity message
     *
     * @param string $title
     * @param string|string[] $description
     * @param string $url
     * @param bool $isInternal
     * @return $this
     */
    public function addCritical($title, $description, $url = '', $isInternal = true)
    {
        foreach ($this->notifierList->asArray() as $notifier) {
            $notifier->addCritical($title, $description, $url, $isInternal);
        }
        return $this;
    }

    /**
     * Add major severity message
     *
     * @param string $title
     * @param string|string[] $description
     * @param string $url
     * @param bool $isInternal
     * @return $this
     */
    public function addMajor($title, $description, $url = '', $isInternal = true)
    {
        foreach ($this->notifierList->asArray() as $notifier) {
            $notifier->addMajor($title, $description, $url, $isInternal);
        }
        return $this;
    }

    /**
     * Add minor severity message
     *
     * @param string $title
     * @param string|string[] $description
     * @param string $url
     * @param bool $isInternal
     * @return $this
     */
    public function addMinor($title, $description, $url = '', $isInternal = true)
    {
        foreach ($this->notifierList->asArray() as $notifier) {
            $notifier->addMinor($title, $description, $url, $isInternal);
        }
        return $this;
    }

    /**
     * Add notice
     *
     * @param string $title
     * @param string|string[] $description
     * @param string $url
     * @param bool $isInternal
     * @return $this
     */
    public function addNotice($title, $description, $url = '', $isInternal = true)
    {
        foreach ($this->notifierList->asArray() as $notifier) {
            $notifier->addNotice($title, $description, $url, $isInternal);
        }
        return $this;
    }
}
