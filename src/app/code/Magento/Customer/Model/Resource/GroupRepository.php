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

namespace Magento\Customer\Model\Resource;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Model\Resource\Group\Collection;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Tax\Service\V1\Data\TaxClass;
use Magento\Tax\Service\V1\TaxClassServiceInterface;

/**
 * Customer group CRUD class
 */
class GroupRepository implements \Magento\Customer\Api\GroupRepositoryInterface
{

    /**
     * The default tax class id if no tax class id is specified
     */
    const DEFAULT_TAX_CLASS_ID = 3;

    /**
     * @var \Magento\Customer\Model\GroupRegistry
     */
    protected $groupRegistry;

    /**
     * @var \Magento\Customer\Model\GroupFactory
     */
    protected $groupFactory;

    /**
     * @var \Magento\Customer\Api\Data\GroupDataBuilder
     */
    protected $groupBuilder;

    /**
     * @var \Magento\Customer\Model\Resource\Group
     */
    protected $groupResourceModel;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Magento\Customer\Api\Data\GroupSearchResultsDataBuilder
     */
    protected $searchResultsBuilder;

    /**
     * @var TaxClassServiceInterface
     */
    private $taxClassService;

    /**
     * @param \Magento\Customer\Model\GroupRegistry $groupRegistry
     * @param \Magento\Customer\Model\GroupFactory $groupFactory
     * @param \Magento\Customer\Api\Data\GroupDataBuilder $groupBuilder
     * @param \Magento\Customer\Model\Resource\Group $groupResourceModel
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param \Magento\Customer\Api\Data\GroupSearchResultsDataBuilder $searchResultsBuilder
     * @param TaxClassServiceInterface $taxClassServiceInterface
     */
    public function __construct(
        \Magento\Customer\Model\GroupRegistry $groupRegistry,
        \Magento\Customer\Model\GroupFactory $groupFactory,
        \Magento\Customer\Api\Data\GroupDataBuilder $groupBuilder,
        \Magento\Customer\Model\Resource\Group $groupResourceModel,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Magento\Customer\Api\Data\GroupSearchResultsDataBuilder $searchResultsBuilder,
        TaxClassServiceInterface $taxClassServiceInterface
    ) {
        $this->groupRegistry = $groupRegistry;
        $this->groupFactory = $groupFactory;
        $this->groupBuilder = $groupBuilder;
        $this->groupResourceModel = $groupResourceModel;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->searchResultsBuilder = $searchResultsBuilder;
        $this->taxClassService = $taxClassServiceInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Magento\Customer\Api\Data\GroupInterface $group)
    {
        $this->_validate($group);

        /** @var \Magento\Customer\Model\Group $groupModel */
        $groupModel = null;
        if ($group->getId()) {
            $this->_verifyTaxClassModel($group->getTaxClassId(), $group);
            $groupModel = $this->groupRegistry->retrieve($group->getId());
            $groupDataAttributes = $this->dataObjectProcessor->buildOutputDataArray(
                $group,
                '\Magento\Customer\Api\Data\GroupInterface'
            );
            foreach ($groupDataAttributes as $attributeCode => $attributeData) {
                $groupModel->setDataUsingMethod($attributeCode, $attributeData);
            }
        } else {
            $groupModel = $this->groupFactory->create();
            $groupModel->setCode($group->getCode());

            $taxClassId = $group->getTaxClassId() ? : self::DEFAULT_TAX_CLASS_ID;
            $this->_verifyTaxClassModel($taxClassId, $group);
            $groupModel->setTaxClassId($taxClassId);
        }

        try {
            $this->groupResourceModel->save($groupModel);
        } catch (\Magento\Framework\Model\Exception $e) {
            /**
             * Would like a better way to determine this error condition but
             *  difficult to do without imposing more database calls
             */
            if ($e->getMessage() === __('Customer Group already exists.')) {
                throw new InvalidTransitionException('Customer Group already exists.');
            }
            throw $e;
        }

        $this->groupRegistry->remove($groupModel->getId());

        $this->groupBuilder->setId($groupModel->getId());
        $this->groupBuilder->setCode($groupModel->getCode());
        $this->groupBuilder->setTaxClassId($groupModel->getTaxClassId());
        $this->groupBuilder->setTaxClassName($groupModel->getTaxClassName());
        return $this->groupBuilder->create();
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        $groupModel = $this->groupRegistry->retrieve($id);
        return $this->groupBuilder->setId($groupModel->getId())
            ->setCode($groupModel->getCode())
            ->setTaxClassId($groupModel->getTaxClassId())
            ->setTaxClassName($groupModel->getTaxClassName())
            ->create();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $this->searchResultsBuilder->setSearchCriteria($searchCriteria);

        /** @var \Magento\Customer\Model\Resource\Group\Collection $collection */
        $collection = $this->groupFactory->create()->getCollection();

        //Add filters from root filter group to the collection
        /** @var FilterGroup $group */
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }
        $this->searchResultsBuilder->setTotalCount($collection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        /** @var \Magento\Framework\Api\SortOrder $sortOrder */
        if ($sortOrders) {
            foreach ($searchCriteria->getSortOrders() as $sortOrder) {
                $field = $this->translateField($sortOrder->getField());
                $collection->addOrder(
                    $field,
                    ($sortOrder->getDirection() == SearchCriteriaInterface::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        /** @var \Magento\Customer\Api\Data\GroupInterface[] $groups */
        $groups = array();
        /** @var \Magento\Customer\Model\Group $group */
        foreach ($collection as $group) {
            $this->groupBuilder->setId($group->getId());
            $this->groupBuilder->setCode($group->getCode());
            $this->groupBuilder->setTaxClassId($group->getTaxClassId());
            $this->groupBuilder->setTaxClassName($group->getTaxClassName());
            $groups[] = $this->groupBuilder->create();
        }
        return $this->searchResultsBuilder->setItems($groups)->create();
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection $collection
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $collection)
    {
        $fields = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $fields[] = $this->translateField($filter->getField());
            $conditions[] = [$condition => $filter->getValue()];
        }
        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }
    }

    /**
     * Translates a field name to a DB column name for use in collection queries.
     *
     * @param string $field a field name that should be translated to a DB column name.
     * @return string
     */
    protected function translateField($field)
    {
        switch ($field) {
            case GroupInterface::CODE:
                return 'customer_group_code';
            case GroupInterface::ID:
                return 'customer_group_id';
            default:
                return $field;
        }
    }

    /**
     * Delete customer group.
     *
     * @param GroupInterface $group
     * @return bool true on success
     * @throws \Magento\Framework\Exception\StateException If customer group cannot be deleted
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(GroupInterface $group)
    {
        return $this->deleteById($group->getId());
    }

    /**
     * Delete customer group by ID.
     *
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException If customer group cannot be deleted
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id)
    {
        $groupModel = $this->groupRegistry->retrieve($id);

        if ($id <= 0 || $groupModel->usesAsDefault()) {
            throw new \Magento\Framework\Exception\StateException('Cannot delete group.');
        }

        $groupModel->delete();
        $this->groupRegistry->remove($id);
        return true;
    }

    /**
     * Validate group values.
     *
     * @param \Magento\Customer\Api\Data\GroupInterface $group
     * @throws InputException
     * @return void
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function _validate($group)
    {
        $exception = new InputException();
        if (!\Zend_Validate::is($group->getCode(), 'NotEmpty')) {
            $exception->addError(InputException::REQUIRED_FIELD, ['fieldName' => 'code']);
        }

        if ($exception->wasErrorAdded()) {
            throw $exception;
        }
    }

    /**
     * Verifies that the tax class model exists and is a customer tax class type.
     *
     * @param int $taxClassId The id of the tax class model to check
     * @param \Magento\Customer\Api\Data\GroupInterface $group The original group parameters
     * @return void
     * @throws InputException Thrown if the tax class model is invalid
     */
    protected function _verifyTaxClassModel($taxClassId, $group)
    {
        try {
            /* @var TaxClass $taxClassData */
            $taxClassData = $this->taxClassService->getTaxClass($taxClassId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            throw InputException::invalidFieldValue('taxClassId', $group->getTaxClassId());
        }
        if ($taxClassData->getClassType() !== TaxClassServiceInterface::TYPE_CUSTOMER) {
            throw InputException::invalidFieldValue('taxClassId', $group->getTaxClassId());
        }
    }
}
