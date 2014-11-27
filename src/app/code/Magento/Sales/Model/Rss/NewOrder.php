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
namespace Magento\Sales\Model\Rss;

use \Magento\Framework\App\Rss\DataProviderInterface;

/**
 * Class NewOrder
 * @package Magento\Sales\Model\Rss
 */
class NewOrder implements DataProviderInterface
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * System event manager
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * Parent layout of the block
     *
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \Magento\Framework\App\Rss\UrlBuilderInterface
     */
    protected $rssUrlBuilder;

    /**
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\App\Rss\UrlBuilderInterface $rssUrlBuilder
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\View\LayoutInterface $layout
     */
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\Rss\UrlBuilderInterface $rssUrlBuilder,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\View\LayoutInterface $layout
    ) {
        $this->orderFactory = $orderFactory;
        $this->urlBuilder = $urlBuilder;
        $this->localeDate = $localeDate;
        $this->dateTime = $dateTime;
        $this->eventManager = $eventManager;
        $this->layout = $layout;
        $this->rssUrlBuilder = $rssUrlBuilder;
    }

    /**
     * Check if RSS feed allowed
     *
     * @return mixed
     */
    public function isAllowed()
    {
        return true;
    }

    /**
     * Get RSS feed items
     *
     * @return array
     */
    public function getRssData()
    {
        $passDate = $this->dateTime->formatDate(mktime(0, 0, 0, date('m'), date('d') - 7));
        $newUrl = $this->rssUrlBuilder->getUrl(array('_secure' => true, '_nosecret' => true, 'type' => 'new_order'));
        $title = __('New Orders');
        $data = array('title' => $title, 'description' => $title, 'link' => $newUrl, 'charset' => 'UTF-8');

        /** @var $order \Magento\Sales\Model\Order */
        $order = $this->orderFactory->create();
        /** @var $collection \Magento\Sales\Model\Resource\Order\Collection */
        $collection = $order->getResourceCollection();
        $collection->addAttributeToFilter('created_at', array('date' => true, 'from' => $passDate))
            ->addAttributeToSort('created_at', 'desc');
        $this->eventManager->dispatch('rss_order_new_collection_select', array('collection' => $collection));

        $detailBlock = $this->layout->getBlockSingleton('Magento\Sales\Block\Adminhtml\Order\Details');
        foreach ($collection as $item) {
            $title = __('Order #%1 created at %2', $item->getIncrementId(), $this->localeDate->formatDate(
                $item->getCreatedAt()
            ));
            $url = $this->urlBuilder->getUrl(
                'sales/order/view',
                array('_secure' => true, 'order_id' => $item->getId(), '_nosecret' => true)
            );
            $detailBlock->setOrder($item);

            $data['entries'][] = (array('title' => $title, 'link' => $url, 'description' => $detailBlock->toHtml()));
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getCacheKey()
    {
        return 'rss_new_orders_data';
    }

    /**
     * @return int
     */
    public function getCacheLifetime()
    {
        return 60;
    }

    /**
     * @return array
     */
    public function getFeeds()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthRequired()
    {
        return true;
    }
}
