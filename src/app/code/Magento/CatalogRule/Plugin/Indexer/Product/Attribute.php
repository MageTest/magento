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
namespace Magento\CatalogRule\Plugin\Indexer\Product;

use Magento\CatalogRule\Model\Rule;
use Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor;
use Magento\CatalogRule\Model\Rule\Condition\Combine;
use Magento\CatalogRule\Model\Resource\Rule\CollectionFactory as RuleCollectionFactory;
use Magento\Rule\Model\Condition\Product\AbstractProduct;
use Magento\Framework\Message\ManagerInterface;

class Attribute
{
    /**
     * @var RuleCollectionFactory
     */
    protected $ruleCollectionFactory;

    /**
     * @var RuleProductProcessor
     */
    protected $ruleProductProcessor;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @param RuleCollectionFactory $ruleCollectionFactory
     * @param RuleProductProcessor $ruleProductProcessor
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        RuleCollectionFactory $ruleCollectionFactory,
        RuleProductProcessor $ruleProductProcessor,
        ManagerInterface $messageManager
    ) {
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->ruleProductProcessor = $ruleProductProcessor;
        $this->messageManager = $messageManager;
    }

    /**
     * @param \Magento\Catalog\Model\Resource\Eav\Attribute $subject
     * @param \Magento\Catalog\Model\Resource\Eav\Attribute $attribute
     * @return \Magento\Catalog\Model\Resource\Eav\Attribute
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        \Magento\Catalog\Model\Resource\Eav\Attribute $subject,
        \Magento\Catalog\Model\Resource\Eav\Attribute $attribute
    ) {
        if ($attribute->dataHasChangedFor('is_used_for_promo_rules') && !$attribute->getIsUsedForPromoRules()) {
            $this->checkCatalogRulesAvailability($attribute->getAttributeCode());
        }
        return $attribute;
    }

    /**
     * @param \Magento\Catalog\Model\Resource\Eav\Attribute $subject
     * @param \Magento\Catalog\Model\Resource\Eav\Attribute $attribute
     * @return \Magento\Catalog\Model\Resource\Eav\Attribute
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(
        \Magento\Catalog\Model\Resource\Eav\Attribute $subject,
        \Magento\Catalog\Model\Resource\Eav\Attribute $attribute
    ) {
        if ($attribute->getIsUsedForPromoRules()) {
            $this->checkCatalogRulesAvailability($attribute->getAttributeCode());
        }
        return $attribute;
    }

    /**
     * Check rules that contains affected attribute
     * If rules were found they will be set to inactive and notice will be add to admin session
     *
     * @param string $attributeCode
     * @return $this
     */
    protected function checkCatalogRulesAvailability($attributeCode)
    {
        /* @var $collection RuleCollectionFactory */
        $collection = $this->ruleCollectionFactory->create()->addAttributeInConditionFilter($attributeCode);

        $disabledRulesCount = 0;
        foreach ($collection as $rule) {
            /* @var $rule Rule */
            $rule->setIsActive(0);
            /* @var $rule->getConditions() Combine */
            $this->removeAttributeFromConditions($rule->getConditions(), $attributeCode);
            $rule->save();

            $disabledRulesCount++;
        }

        if ($disabledRulesCount) {
            $this->ruleProductProcessor->markIndexerAsInvalid();
            $this->messageManager->addWarning(
                __(
                    '%1 Catalog Price Rules based on "%2" attribute have been disabled.',
                    $disabledRulesCount,
                    $attributeCode
                )
            );
        }

        return $this;
    }

    /**
     * Remove catalog attribute condition by attribute code from rule conditions
     *
     * @param Combine $combine
     * @param string $attributeCode
     * @return void
     */
    protected function removeAttributeFromConditions(Combine $combine, $attributeCode)
    {
        $conditions = $combine->getConditions();
        foreach ($conditions as $conditionId => $condition) {
            if ($condition instanceof Combine) {
                $this->removeAttributeFromConditions($condition, $attributeCode);
            }
            if ($condition instanceof AbstractProduct) {
                if ($condition->getAttribute() == $attributeCode) {
                    unset($conditions[$conditionId]);
                }
            }
        }
        $combine->setConditions($conditions);
    }
}
