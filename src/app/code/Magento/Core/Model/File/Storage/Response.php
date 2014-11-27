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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\File\Storage;

use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\App\Response\Http;

class Response extends Http implements \Magento\Framework\App\Response\FileInterface
{
    /**
     * @var \Magento\Framework\File\Transfer\Adapter\Http
     */
    protected $_transferAdapter;

    /**
     * Full path to file
     *
     * @var string
     */
    protected $_filePath;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\App\Http\Context $context
     * @param \Magento\Framework\File\Transfer\Adapter\Http $transferAdapter
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\App\Http\Context $context,
        \Magento\Framework\File\Transfer\Adapter\Http $transferAdapter
    ) {
        parent::__construct($cookieManager, $cookieMetadataFactory, $context);
        $this->_transferAdapter = $transferAdapter;
    }

    /**
     * Send response
     *
     * @return void
     */
    public function sendResponse()
    {
        if ($this->_filePath && $this->getHttpResponseCode() == 200) {
            $this->_transferAdapter->send($this->_filePath);
        } else {
            parent::sendResponse();
        }
    }

    /**
     * @param string $path
     * @return void
     */
    public function setFilePath($path)
    {
        $this->_filePath = $path;
    }
}
