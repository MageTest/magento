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

namespace Magento\Framework\Api;

/**
 * Builder for the SearchResults Service Data Object
 *
 * @method SearchResults create()
 */
abstract class AbstractSearchResultsBuilder extends ExtensibleObjectBuilder
{
    /**
     * Search criteria builder
     *
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * Item data object builder
     *
     * @var BuilderInterface $itemObjectBuilder
     */
    protected $itemObjectBuilder;

    /**
     * Constructor
     *
     * @param ObjectFactory $objectFactory
     * @param AttributeDataBuilder $valueBuilder
     * @param MetadataServiceInterface $metadataService
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param BuilderInterface $itemObjectBuilder
     */
    public function __construct(
        ObjectFactory $objectFactory,
        AttributeDataBuilder $valueBuilder,
        MetadataServiceInterface $metadataService,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        BuilderInterface $itemObjectBuilder
    ) {
        parent::__construct($objectFactory, $valueBuilder, $metadataService);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->itemObjectBuilder = $itemObjectBuilder;
    }

    /**
     * Set search criteria
     *
     * @param SearchCriteria $searchCriteria
     * @return $this
     */
    public function setSearchCriteria(SearchCriteria $searchCriteria)
    {
        return $this->_set(SearchResults::KEY_SEARCH_CRITERIA, $searchCriteria);
    }

    /**
     * Set total count
     *
     * @param int $totalCount
     * @return $this
     */
    public function setTotalCount($totalCount)
    {
        return $this->_set(SearchResults::KEY_TOTAL_COUNT, $totalCount);
    }

    /**
     * Set items
     *
     * @param \Magento\Framework\Api\AbstractExtensibleObject[] $items
     * @return $this
     */
    public function setItems($items)
    {
        return $this->_set(SearchResults::KEY_ITEMS, $items);
    }

    /**
     * {@inheritdoc}
     */
    protected function _setDataValues(array $data)
    {
        if (array_key_exists(SearchResults::KEY_SEARCH_CRITERIA, $data)) {
            $data[SearchResults::KEY_SEARCH_CRITERIA] =
                $this->searchCriteriaBuilder->populateWithArray($data[SearchResults::KEY_SEARCH_CRITERIA])->create();
        }
        if (array_key_exists(SearchResults::KEY_ITEMS, $data)) {
            $items = [];
            foreach ($data[SearchResults::KEY_ITEMS] as $itemArray) {
                $items[] = $this->itemObjectBuilder->populateWithArray($itemArray)->create();
            }
            $data[SearchResults::KEY_ITEMS] = $items;
        }
        return parent::_setDataValues($data);
    }
}
