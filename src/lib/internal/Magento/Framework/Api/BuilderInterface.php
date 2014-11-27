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

interface BuilderInterface extends SimpleBuilderInterface
{
    /**
     * Set custom attribute value.
     *
     * @param string $attributeCode
     * @param mixed $attributeValue
     * @return $this
     */
    public function setCustomAttribute($attributeCode, $attributeValue);

    /**
     * Set array of custom attributes
     *
     * @param \Magento\Framework\Api\AttributeInterface[] $attributes
     * @return $this
     * @throws \LogicException If array elements are not of AttributeValue type
     */
    public function setCustomAttributes(array $attributes);

    /**
     * Return created ExtensibleDataInterface object
     *
     * @return \Magento\Framework\Api\ExtensibleDataInterface
     */
    public function create();

    /**
     * Populates the fields with data from the array.
     *
     * Keys for the map are snake_case attribute/field names.
     *
     * @param array $data
     * @return $this
     */
    public function populateWithArray(array $data);

    /**
     * Populates the fields with an existing entity.
     *
     * @param ExtensibleDataInterface $prototype the prototype to base on
     * @return $this
     * @throws \LogicException If $prototype object class is not the same type as object that is constructed
     */
    public function populate(ExtensibleDataInterface $prototype);

    /**
     * Populate builder with the two data interfaces, merging them
     *
     * @param ExtensibleDataInterface $firstDataObject
     * @param ExtensibleDataInterface $secondDataObject
     * @return $this
     * @throws \LogicException
     */
    public function mergeDataObjects(
        ExtensibleDataInterface $firstDataObject,
        ExtensibleDataInterface $secondDataObject
    );

    /**
     * Populate builder with the data interface and array, merging them
     *
     * @param ExtensibleDataInterface $dataObject
     * @param array $data
     * @return $this
     * @throws \LogicException
     */
    public function mergeDataObjectWithArray(ExtensibleDataInterface $dataObject, array $data);
}
