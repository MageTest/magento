<?php
/**
 * Attribute mapper
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
namespace Magento\Catalog\Model\Entity\Product\Attribute\Group;

use Magento\Catalog\Model\Attribute;

class AttributeMapper implements AttributeMapperInterface
{
    /**
     * Unassignable attributes
     *
     * @var array
     */
    protected $unassignableAttributes;

    /**
     * @param \Magento\Catalog\Model\Attribute\Config $attributeConfig
     */
    public function __construct(Attribute\Config $attributeConfig)
    {
        $this->unassignableAttributes = $attributeConfig->getAttributeNames('unassignable');
    }

    /**
     * Build attribute representation
     *
     * @param \Magento\Eav\Model\Entity\Attribute $attribute
     * @return array
     */
    public function map(\Magento\Eav\Model\Entity\Attribute $attribute)
    {
        $isUnassignable = !in_array($attribute->getAttributeCode(), $this->unassignableAttributes);

        return array(
            'text' => $attribute->getAttributeCode(),
            'id' => $attribute->getAttributeId(),
            'cls' => $isUnassignable ? 'leaf' : 'system-leaf',
            'allowDrop' => false,
            'allowDrag' => true,
            'leaf' => true,
            'is_user_defined' => $attribute->getIsUserDefined(),
            'is_unassignable' => $isUnassignable,
            'entity_id' => $attribute->getEntityAttributeId()
        );
    }
}
