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

/**
 * Nonce model
 * @author Magento Core Team <core@magentocommerce.com>
 * @method string getNonce()
 * @method \Magento\Integration\Model\Oauth\Nonce setNonce() setNonce(string $nonce)
 * @method int getConsumerId()
 * @method \Magento\Integration\Model\Oauth\Nonce setConsumerId() setConsumerId(int $consumerId)
 * @method string getTimestamp()
 * @method \Magento\Integration\Model\Oauth\Nonce setTimestamp() setTimestamp(string $timestamp)
 * @method \Magento\Integration\Model\Resource\Oauth\Nonce getResource()
 * @method \Magento\Integration\Model\Resource\Oauth\Nonce _getResource()
 */
class Nonce extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Oauth data
     *
     * @var \Magento\Integration\Helper\Oauth\Data
     */
    protected $_oauthData;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Integration\Helper\Oauth\Data $oauthData
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Integration\Helper\Oauth\Data $oauthData,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_oauthData = $oauthData;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Integration\Model\Resource\Oauth\Nonce');
    }

    /**
     * The "After save" actions
     *
     * @return $this
     */
    protected function _afterSave()
    {
        parent::_afterSave();

        if ($this->_oauthData->isCleanupProbability()) {
            $this->getResource()->deleteOldEntries($this->_oauthData->getCleanupExpirationPeriod());
        }
        return $this;
    }

    /**
     * Load given a composite key consisting of a nonce string and a consumer id
     *
     * @param string $nonce - The nonce string
     * @param int $consumerId - The consumer id
     * @return $this
     */
    public function loadByCompositeKey($nonce, $consumerId)
    {
        $data = $this->getResource()->selectByCompositeKey($nonce, $consumerId);
        $this->setData($data);
        return $this;
    }
}
