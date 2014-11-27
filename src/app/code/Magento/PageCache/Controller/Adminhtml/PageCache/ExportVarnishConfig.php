<?php
/**
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
namespace Magento\PageCache\Controller\Adminhtml\PageCache;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportVarnishConfig extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Backend\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var \Magento\PageCache\Model\Config
     */
    protected $config;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\PageCache\Model\Config $config
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\PageCache\Model\Config $config
    ) {
        parent::__construct($context);
        $this->config = $config;
        $this->fileFactory = $fileFactory;
    }

    /**
     * Export Varnish Configuration as .vcl
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $fileName = 'varnish.vcl';
        $content = $this->config->getVclFile();
        return $this->fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}
