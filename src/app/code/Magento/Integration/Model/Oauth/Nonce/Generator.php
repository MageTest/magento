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
namespace Magento\Integration\Model\Oauth\Nonce;

use Magento\Framework\Oauth\ConsumerInterface;
use Magento\Framework\Oauth\NonceGeneratorInterface;

class Generator implements NonceGeneratorInterface
{
    /**
     * @var \Magento\Framework\Oauth\Helper\Oauth
     */
    protected $_oauthHelper;

    /**
     * @var  \Magento\Integration\Model\Oauth\Nonce\Factory
     */
    protected $_nonceFactory;

    /**
     * @var  int
     */
    protected $_nonceLength;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * Possible time deviation for timestamp validation in seconds.
     */
    const TIME_DEVIATION = 600;

    /**
     * @param \Magento\Framework\Oauth\Helper\Oauth $oauthHelper
     * @param \Magento\Integration\Model\Oauth\Nonce\Factory $nonceFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param int $nonceLength - Length of the generated nonce
     */
    public function __construct(
        \Magento\Framework\Oauth\Helper\Oauth $oauthHelper,
        \Magento\Integration\Model\Oauth\Nonce\Factory $nonceFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        $nonceLength = \Magento\Framework\Oauth\Helper\Oauth::LENGTH_NONCE
    ) {
        $this->_oauthHelper = $oauthHelper;
        $this->_nonceFactory = $nonceFactory;
        $this->_date = $date;
        $this->_nonceLength = $nonceLength;
    }

    /**
     * {@inheritdoc}
     */
    public function generateNonce(ConsumerInterface $consumer = null)
    {
        return $this->_oauthHelper->generateRandomString($this->_nonceLength);
    }

    /**
     * {@inheritdoc}
     */
    public function generateTimestamp()
    {
        return $this->_date->timestamp();
    }

    /**
     * {@inheritdoc}
     */
    public function validateNonce(ConsumerInterface $consumer, $nonce, $timestamp)
    {
        try {
            $timestamp = (int)$timestamp;
            if ($timestamp <= 0 || $timestamp > time() + self::TIME_DEVIATION) {
                throw new \Magento\Framework\Oauth\OauthInputException(
                    'Incorrect timestamp value in the oauth_timestamp parameter'
                );
            }

            /** @var \Magento\Integration\Model\Oauth\Nonce $nonceObj */
            $nonceObj = $this->_nonceFactory->create()->loadByCompositeKey($nonce, $consumer->getId());

            if ($nonceObj->getNonce()) {
                throw new \Magento\Framework\Oauth\Exception(
                    'The nonce is already being used by the consumer with ID %1',
                    [$consumer->getId()]
                );
            }

            $nonceObj->setNonce($nonce)->setConsumerId($consumer->getId())->setTimestamp($timestamp)->save();
        } catch (\Magento\Framework\Oauth\Exception $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Oauth\Exception('An error occurred validating the nonce');
        }
    }
}
