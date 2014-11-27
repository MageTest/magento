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
namespace Magento\Framework\Data;

use Magento\Framework\Logger;

/**
 * Class AbstractSearchResult
 */
abstract class AbstractSearchResult extends AbstractDataObject implements SearchResultInterface
{
    /**
     * Data Interface name
     *
     * @var string
     */
    protected $dataInterface = 'Magento\Framework\Object';

    /**
     * Name prefix of events that are dispatched by model
     *
     * @var string
     */
    protected $eventPrefix = '';

    /**
     * Name of event parameter
     *
     * @var string
     */
    protected $eventObject = '';

    /**
     * Event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager = null;

    /**
     * Total items number
     *
     * @var int
     */
    protected $totalRecords;

    /**
     * Loading state flag
     *
     * @var bool
     */
    protected $isLoaded;

    /**
     * @var \Magento\Framework\Data\Collection\EntityFactoryInterface
     */
    protected $entityFactory;

    /**
     * @var \Magento\Framework\DB\Select
     */
    protected $select;

    /**
     * @var \Magento\Framework\Data\SearchResultIteratorFactory
     */
    protected $resultIteratorFactory;

    /**
     * @param \Magento\Framework\DB\QueryInterface $query
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Data\SearchResultIteratorFactory $resultIteratorFactory
     */
    public function __construct(
        \Magento\Framework\DB\QueryInterface $query,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Data\SearchResultIteratorFactory $resultIteratorFactory
    ) {
        $this->query = $query;
        $this->eventManager = $eventManager;
        $this->entityFactory = $entityFactory;
        $this->resultIteratorFactory = $resultIteratorFactory;
        $this->init();
    }

    /**
     * Standard query builder initialization
     *
     * @return void
     */
    abstract protected function init();

    /**
     * @return \Magento\Framework\Object[]
     */
    public function getItems()
    {
        $this->load();
        return $this->data['items'];
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        if (!isset($this->data['total_count'])) {
            $this->data['total_count'] = $this->query->getSize();
        }
        return $this->data['total_count'];
    }

    /**
     * @return \Magento\Framework\Api\CriteriaInterface
     */
    public function getSearchCriteria()
    {
        if (!isset($this->data['search_criteria'])) {
            $this->data['search_criteria'] = $this->query->getCriteria();
        }
        return $this->data['search_criteria'];
    }

    /**
     * @return \Magento\Framework\Data\SearchResultIterator
     */
    public function createIterator()
    {
        return $this->resultIteratorFactory->create(
            [
                'searchResult' => $this,
                'query' => $this->query
            ]
        );
    }

    /**
     * @param array $arguments
     * @return \Magento\Framework\Object|mixed
     */
    public function createDataObject(array $arguments = [])
    {
        return $this->entityFactory->create($this->getDataInterfaceName(), $arguments);
    }

    /**
     * @return string
     */
    public function getIdFieldName()
    {
        return $this->query->getIdFieldName();
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->query->getSize();
    }

    /**
     * Get collection item identifier
     *
     * @param \Magento\Framework\Object $item
     * @return mixed
     */
    public function getItemId(\Magento\Framework\Object $item)
    {
        $field = $this->query->getIdFieldName();
        if ($field) {
            return $item->getData($field);
        }
        return $item->getId();
    }

    /**
     * @return bool
     */
    protected function isLoaded()
    {
        return $this->isLoaded;
    }

    /**
     * Load data
     *
     * @return void
     */
    protected function load()
    {
        if (!$this->isLoaded()) {
            $this->beforeLoad();
            $data = $this->query->fetchAll();
            $this->data['items'] = [];
            if (is_array($data)) {
                foreach ($data as $row) {
                    $item = $this->createDataObject(['data' => $row]);
                    $this->addItem($item);
                }
            }
            $this->setIsLoaded(true);
            $this->afterLoad();
        }
    }

    /**
     * Set loading status flag
     *
     * @param bool $flag
     * @return void
     */
    protected function setIsLoaded($flag = true)
    {
        $this->isLoaded = $flag;
    }

    /**
     * Adding item to item array
     *
     * @param \Magento\Framework\Object $item
     * @return void
     * @throws \Exception
     */
    protected function addItem(\Magento\Framework\Object $item)
    {
        $itemId = $this->getItemId($item);
        if (!is_null($itemId)) {
            if (isset($this->data['items'][$itemId])) {
                throw new \Exception(
                    'Item (' . get_class($item) . ') with the same id "' . $item->getId() . '" already exist'
                );
            }
            $this->data['items'][$itemId] = $item;
        } else {
            $this->data['items'][] = $item;
        }
    }

    /**
     * Dispatch "before" load method events
     *
     * @return void
     */
    protected function beforeLoad()
    {
        $this->eventManager->dispatch('abstract_search_result_load_before', ['collection' => $this]);
        if ($this->eventPrefix && $this->eventObject) {
            $this->eventManager->dispatch($this->eventPrefix . '_load_before', [$this->eventObject => $this]);
        }
    }

    /**
     * Dispatch "after" load method events
     *
     * @return void
     */
    protected function afterLoad()
    {
        $this->eventManager->dispatch('abstract_search_result_load_after', ['collection' => $this]);
        if ($this->eventPrefix && $this->eventObject) {
            $this->eventManager->dispatch($this->eventPrefix . '_load_after', [$this->eventObject => $this]);
        }
    }

    /**
     * Set Data Interface name for collection items
     *
     * @param string $dataInterface
     * @return void
     */
    protected function setDataInterfaceName($dataInterface)
    {
        if (is_string($dataInterface)) {
            $this->dataInterface = $dataInterface;
        }
    }

    /**
     * Get Data Interface name for collection items
     *
     * @return string
     */
    protected function getDataInterfaceName()
    {
        return $this->dataInterface;
    }

    /**
     * @return \Magento\Framework\DB\QueryInterface
     */
    protected function getQuery()
    {
        return $this->query;
    }
}
