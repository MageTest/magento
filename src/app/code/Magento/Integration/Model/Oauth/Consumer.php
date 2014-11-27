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
namespace Magento\Integration\Model\Oauth;

use Magento\Framework\Oauth\ConsumerInterface;

/**
 * Consumer model
 *
 * @author Magento Core Team <core@magentocommerce.com>
 * @method \Magento\Integration\Model\Resource\Oauth\Consumer _getResource()
 * @method \Magento\Integration\Model\Resource\Oauth\Consumer getResource()
 * @method \Magento\Integration\Model\Resource\Oauth\Consumer\Collection getCollection()
 * @method \Magento\Integration\Model\Resource\Oauth\Consumer\Collection getResourceCollection()
 * @method string getName()
 * @method Consumer setName() setName(string $name)
 * @method Consumer setKey() setKey(string $key)
 * @method Consumer setSecret() setSecret(string $secret)
 * @method Consumer setCallbackUrl() setCallbackUrl(string $url)
 * @method Consumer setCreatedAt() setCreatedAt(string $date)
 * @method string getUpdatedAt()
 * @method Consumer setUpdatedAt() setUpdatedAt(string $date)
 * @method string getRejectedCallbackUrl()
 * @method Consumer setRejectedCallbackUrl() setRejectedCallbackUrl(string $rejectedCallbackUrl)
 */
class Consumer extends \Magento\Framework\Model\AbstractModel implements ConsumerInterface
{
    /**
     * @var \Magento\Framework\Url\Validator
     */
    protected $_urlValidator;

    /**
     * @var \Magento\Integration\Model\Oauth\Consumer\Validator\KeyLengthFactory
     */
    protected $_keyLengthFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Integration\Model\Oauth\Consumer\Validator\KeyLengthFactory $keyLengthFactory
     * @param \Magento\Framework\Url\Validator $urlValidator
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Integration\Model\Oauth\Consumer\Validator\KeyLengthFactory $keyLengthFactory,
        \Magento\Framework\Url\Validator $urlValidator,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_keyLengthFactory = $keyLengthFactory;
        $this->_urlValidator = $urlValidator;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Magento\Integration\Model\Resource\Oauth\Consumer');
    }

    /**
     * BeforeSave actions
     *
     * @return $this
     */
    protected function _beforeSave()
    {
        if (!$this->getId()) {
            $this->setUpdatedAt(time());
        }
        $this->validate();
        parent::_beforeSave();
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function validate()
    {
        if ($this->getCallbackUrl() || $this->getRejectedCallbackUrl()) {
            $this->setCallbackUrl(trim($this->getCallbackUrl()));
            $this->setRejectedCallbackUrl(trim($this->getRejectedCallbackUrl()));

            if ($this->getCallbackUrl() && !$this->_urlValidator->isValid($this->getCallbackUrl())) {
                throw new \Magento\Framework\Model\Exception(__('Invalid Callback URL'));
            }
            if ($this->getRejectedCallbackUrl() && !$this->_urlValidator->isValid($this->getRejectedCallbackUrl())) {
                throw new \Magento\Framework\Model\Exception(__('Invalid Rejected Callback URL'));
            }
        }

        /** @var $validatorLength \Magento\Integration\Model\Oauth\Consumer\Validator\KeyLength */
        $validatorLength = $this->_keyLengthFactory->create(
            array('options' => array('length' => \Magento\Framework\Oauth\Helper\Oauth::LENGTH_CONSUMER_KEY))
        );

        $validatorLength->setName('Consumer Key');
        if (!$validatorLength->isValid($this->getKey())) {
            $messages = $validatorLength->getMessages();
            throw new \Magento\Framework\Model\Exception(array_shift($messages));
        }

        $validatorLength->setLength(\Magento\Framework\Oauth\Helper\Oauth::LENGTH_CONSUMER_SECRET);
        $validatorLength->setName('Consumer Secret');
        if (!$validatorLength->isValid($this->getSecret())) {
            $messages = $validatorLength->getMessages();
            throw new \Magento\Framework\Model\Exception(array_shift($messages));
        }
        return true;
    }

    /**
     * Load consumer data by consumer key.
     *
     * @param string $key
     * @return $this
     */
    public function loadByKey($key)
    {
        return $this->load($key, 'key');
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return $this->getData('key');
    }

    /**
     * {@inheritdoc}
     */
    public function getSecret()
    {
        return $this->getData('secret');
    }

    /**
     * {@inheritdoc}
     */
    public function getCallbackUrl()
    {
        return $this->getData('callback_url');
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->getData('created_at');
    }
}
