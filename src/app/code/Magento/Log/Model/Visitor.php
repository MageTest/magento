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
namespace Magento\Log\Model;

/**
 * @method Resource\Visitor _getResource()
 * @method Resource\Visitor getResource()
 * @method Visitor setFirstVisitAt(string $value)
 * @method Visitor setLastVisitAt(string $value)
 * @method Visitor setVisitorId(int $value)
 * @method Visitor setIsNewVisitor(bool $value)
 * @method Visitor getIsNewVisitor()
 * @method Visitor getVisitorId()
 * @method int getLastUrlId()
 * @method Visitor setLastUrlId(int $value)
 * @method int getStoreId()
 * @method Visitor setStoreId(int $value)
 */
class Visitor extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $session;

    /**
     * @var \Magento\Framework\HTTP\Header
     */
    protected $httpHeader;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $remoteAddress;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\ServerAddress
     */
    protected $serverAddress;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Framework\HTTP\Header $httpHeader
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     * @param \Magento\Framework\HTTP\PhpEnvironment\ServerAddress $serverAddress
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Framework\HTTP\Header $httpHeader,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Magento\Framework\HTTP\PhpEnvironment\ServerAddress $serverAddress,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->session = $session;
        $this->storeManager = $storeManager;
        $this->httpHeader = $httpHeader;
        $this->remoteAddress = $remoteAddress;
        $this->serverAddress = $serverAddress;
        $this->dateTime = $dateTime;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Log\Model\Resource\Visitor');
    }

    /**
     * Retrieve session object
     *
     * @return \Magento\Framework\Session\SessionManagerInterface
     */
    protected function _getSession()
    {
        return $this->session;
    }

    /**
     * Initialize visitor information from server data
     *
     * @return $this
     */
    public function initServerData()
    {
        $clean = true;
        $this->addData(
            array(
                'server_addr' => $this->serverAddress->getServerAddress(true),
                'remote_addr' => $this->remoteAddress->getRemoteAddress(true),
                'http_secure' => $this->storeManager->getStore()->isCurrentlySecure(),
                'http_host' => $this->httpHeader->getHttpHost($clean),
                'http_user_agent' => $this->httpHeader->getHttpUserAgent($clean),
                'http_accept_language' => $this->httpHeader->getHttpAcceptLanguage($clean),
                'http_accept_charset' => $this->httpHeader->getHttpAcceptCharset($clean),
                'request_uri' => $this->httpHeader->getRequestUri($clean),
                'http_referer' => $this->httpHeader->getHttpReferer($clean)
            )
        );

        return $this;
    }

    /**
     * Retrieve url from model data
     *
     * @return string
     */
    public function getUrl()
    {
        $url = 'http' . ($this->getHttpSecure() ? 's' : '') . '://';
        $url .= $this->getHttpHost() . $this->getRequestUri();
        return $url;
    }

    /**
     * Return First Visit data in internal format.
     *
     * @return string
     */
    public function getFirstVisitAt()
    {
        if (!$this->hasData('first_visit_at')) {
            $this->setData('first_visit_at', $this->dateTime->now());
        }
        return $this->getData('first_visit_at');
    }

    /**
     * Return Last Visit data in internal format.
     *
     * @return string
     */
    public function getLastVisitAt()
    {
        if (!$this->hasData('last_visit_at')) {
            $this->setData('last_visit_at', $this->dateTime->now());
        }
        return $this->getData('last_visit_at');
    }

    /**
     * Initialization visitor information by request
     *
     * Used in event "controller_action_predispatch"
     *
     * @param   \Magento\Framework\Event\Observer $observer
     * @return  \Magento\Log\Model\Visitor
     */
    public function logNewVisitor($observer)
    {
        $visitor = $observer->getEvent()->getVisitor();
        $this->setData($visitor->getData());
        $this->initServerData();
        $this->setFirstVisitAt($this->dateTime->now());
        $this->setIsNewVisitor(true);
        $this->save();
        $visitor->setData($this->getData());
        return $this;
    }

    /**
     * Saving visitor information by request
     *
     * Used in event "controller_action_postdispatch"
     *
     * @param   \Magento\Framework\Event\Observer $observer
     * @return  \Magento\Log\Model\Visitor
     */
    public function logVisitorActivity($observer)
    {
        $visitor = $observer->getEvent()->getVisitor();
        try {
            $this->setData($visitor->getData());
            if ($this->getId() && $this->getVisitorId()) {
                $this->initServerData();
                $this->setLastVisitAt($this->dateTime->now());
                $this->save();
                $visitor->setData($this->getData());
            }
        } catch (\Exception $e) {
            $this->_logger->logException($e);
        }
        return $this;
    }

    /**
     * Save object data
     *
     * @return $this
     * @throws \Exception
     */
    public function save()
    {
        if ($this->isDeleted()) {
            return $this->delete();
        }
        if (!$this->_hasModelChanged()) {
            return $this;
        }
        try {
            $this->_validateBeforeSave();
            $this->_beforeSave();
            if ($this->_dataSaveAllowed) {
                $this->_getResource()->save($this);
                $this->_afterSave();
            }
            $this->_hasDataChanges = false;
        } catch (\Exception $e) {
            $this->_hasDataChanges = true;
            throw $e;
        }
        return $this;
    }
}
