<?php
/**
 * Helper for EAV functionality in integration tests.
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
namespace Magento\TestFramework\Helper;

class Eav
{
    /**
     * Set increment id prefix in entity model.
     *
     * @param string $entityType
     * @param string $prefix
     */
    public static function setIncrementIdPrefix($entityType, $prefix)
    {
        $website = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\StoreManagerInterface'
        )->getWebsite();
        $storeId = $website->getDefaultStore()->getId();
        $entityTypeModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Eav\Model\Entity\Type'
        )->loadByCode(
            $entityType
        );
        /** @var \Magento\Eav\Model\Entity\Store $entityStore */
        $entityStore = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Eav\Model\Entity\Store'
        )->loadByEntityStore(
            $entityTypeModel->getId(),
            $storeId
        );
        $entityStore->setEntityTypeId($entityTypeModel->getId());
        $entityStore->setStoreId($storeId);
        $entityStore->setIncrementPrefix($prefix);
        $entityStore->save();
    }
}
