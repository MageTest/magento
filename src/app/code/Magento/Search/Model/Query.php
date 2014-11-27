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
namespace Magento\Search\Model;

use Magento\Search\Model\Resource\Query\Collection as QueryCollection;
use Magento\Search\Model\Resource\Query\CollectionFactory as QueryCollectionFactory;
use Magento\Search\Model\SearchCollectionInterface as Collection;
use Magento\Search\Model\SearchCollectionFactory as CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\Db;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Resource\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\StoreManagerInterface;

/**
 * Search query model
 *
 * @method Resource\Query _getResource()
 * @method Resource\Query getResource()
 * @method \Magento\Search\Model\Query setQueryText(string $value)
 * @method int getNumResults()
 * @method \Magento\Search\Model\Query setNumResults(int $value)
 * @method int getPopularity()
 * @method \Magento\Search\Model\Query setPopularity(int $value)
 * @method string getRedirect()
 * @method \Magento\Search\Model\Query setRedirect(string $value)
 * @method string getSynonymFor()
 * @method \Magento\Search\Model\Query setSynonymFor(string $value)
 * @method int getDisplayInTerms()
 * @method \Magento\Search\Model\Query setDisplayInTerms(int $value)
 * @method \Magento\Search\Model\Query setQueryNameExceeded(bool $value)
 * @method int getIsActive()
 * @method \Magento\Search\Model\Query setIsActive(int $value)
 * @method int getIsProcessed()
 * @method \Magento\Search\Model\Query setIsProcessed(int $value)
 * @method string getUpdatedAt()
 * @method \Magento\Search\Model\Query setUpdatedAt(string $value)
 */
class Query extends AbstractModel implements QueryInterface
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'search_query';

    /**
     * Event object key name
     *
     * @var string
     */
    protected $_eventObject = 'search_query';

    const CACHE_TAG = 'SEARCH_QUERY';

    const XML_PATH_MIN_QUERY_LENGTH = 'catalog/search/min_query_length';

    const XML_PATH_MAX_QUERY_LENGTH = 'catalog/search/max_query_length';

    const XML_PATH_MAX_QUERY_WORDS = 'catalog/search/max_query_words';

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Search collection factory
     *
     * @var CollectionFactory
     */
    protected $_searchCollectionFactory;

    /**
     * Query collection factory
     *
     * @var QueryCollectionFactory
     */
    protected $_queryCollectionFactory;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\Context $context
     * @param Registry $registry
     * @param QueryCollectionFactory $queryCollectionFactory
     * @param CollectionFactory $searchCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param Config $scopeConfig
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        Registry $registry,
        QueryCollectionFactory $queryCollectionFactory,
        CollectionFactory $searchCollectionFactory,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        AbstractResource $resource = null,
        Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_queryCollectionFactory = $queryCollectionFactory;
        $this->_searchCollectionFactory = $searchCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Search\Model\Resource\Query');
    }

    /**
     * Retrieve search collection
     *
     * @return Collection
     */
    public function getSearchCollection()
    {
        return $this->_searchCollectionFactory->create();
    }

    /**
     * Retrieve collection of suggest queries
     *
     * @return QueryCollection
     */
    public function getSuggestCollection()
    {
        $collection = $this->getData('suggest_collection');
        if (is_null($collection)) {
            $collection = $this->_queryCollectionFactory->create()->setStoreId(
                $this->getStoreId()
            )->setQueryFilter(
                $this->getQueryText()
            );
            $this->setData('suggest_collection', $collection);
        }
        return $collection;
    }

    /**
     * Load Query object by query string
     *
     * @param string $text
     * @return $this
     */
    public function loadByQuery($text)
    {
        $this->_getResource()->loadByQuery($this, $text);
        $this->_afterLoad();
        $this->setOrigData();
        return $this;
    }

    /**
     * Load Query object only by query text (skip 'synonym For')
     *
     * @param string $text
     * @return $this
     */
    public function loadByQueryText($text)
    {
        $this->_getResource()->loadByQueryText($this, $text);
        $this->_afterLoad();
        $this->setOrigData();
        return $this;
    }

    /**
     * Set Store Id
     *
     * @param int $storeId
     * @return void
     */
    public function setStoreId($storeId)
    {
        $this->setData('store_id', $storeId);
    }

    /**
     * Retrieve store Id
     *
     * @return int
     */
    public function getStoreId()
    {
        if (!($storeId = $this->getData('store_id'))) {
            $storeId = $this->_storeManager->getStore()->getId();
        }
        return $storeId;
    }

    /**
     * Prepare save query for result
     *
     * @return $this
     */
    public function prepare()
    {
        if (!$this->getId()) {
            $this->setIsActive(0);
            $this->setIsProcessed(0);
            $this->save();
            $this->setIsActive(1);
        }

        return $this;
    }

    /**
     * Retrieve minimum query length
     *
     * @return int
     */
    public function getMinQueryLength()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_MIN_QUERY_LENGTH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * Retrieve maximum query length
     *
     * @return int
     */
    public function getMaxQueryLength()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_MAX_QUERY_LENGTH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * Retrieve maximum query words for like search
     *
     * @return int
     */
    public function getMaxQueryWords()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_MAX_QUERY_WORDS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     */
    public function getQueryText()
    {
        return $this->getDataByKey('query_text');
    }

    /**
     * @return bool
     */
    public function isQueryTextExceeded()
    {
        return $this->getData('is_query_text_exceeded');
    }
}
