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

namespace Magento\Customer\Api\Data;

/**
 * Customer attribute metadata interface.
 */
interface AttributeMetadataInterface
{
    /**#@+
     * Constants used as keys of data array
     */
    const ATTRIBUTE_CODE = 'attribute_code';
    const FRONTEND_INPUT = 'frontend_input';
    const INPUT_FILTER = 'input_filter';
    const STORE_LABEL = 'store_label';
    const VALIDATION_RULES = 'validation_rules';
    const OPTIONS = 'options';
    const VISIBLE = 'visible';
    const REQUIRED = 'required';
    const MULTILINE_COUNT = 'multiline_count';
    const DATA_MODEL = 'data_model';
    const USER_DEFINED = 'user_defined';
    const FRONTEND_CLASS = 'frontend_class';
    const SORT_ORDER = 'sort_order';
    const FRONTEND_LABEL = 'frontend_label';
    const SYSTEM = 'system';
    const NOTE = 'note';
    const BACKEND_TYPE = 'backend_type';
    /**#@-*/

    /**
     * Retrieve code of the attribute.
     *
     * @return string
     */
    public function getAttributeCode();

    /**
     * Frontend HTML for input element.
     *
     * @return string
     */
    public function getFrontendInput();

    /**
     * Get template used for input (e.g. "date")
     *
     * @return string
     */
    public function getInputFilter();

    /**
     * Get label of the store.
     *
     * @return string
     */
    public function getStoreLabel();

    /**
     * Retrieve validation rules.
     *
     * @return \Magento\Customer\Api\Data\ValidationRuleInterface[]
     */
    public function getValidationRules();

    /**
     * Number of lines of the attribute value.
     *
     * @return int
     */
    public function getMultilineCount();

    /**
     * Whether attribute is visible on frontend.
     *
     * @return bool
     */
    public function isVisible();

    /**
     * Whether attribute is required.
     *
     * @return bool
     */
    public function isRequired();

    /**
     * Get data model for attribute.
     *
     * @return string
     */
    public function getDataModel();

    /**
     * Return options of the attribute (key => value pairs for select)
     *
     * @return \Magento\Customer\Api\Data\OptionInterface[]
     */
    public function getOptions();

    /**
     * Get class which is used to display the attribute on frontend.
     *
     * @return string
     */
    public function getFrontendClass();

    /**
     * Whether current attribute has been defined by a user.
     *
     * @return bool
     */
    public function isUserDefined();

    /**
     * Get attributes sort order.
     *
     * @return int
     */
    public function getSortOrder();

    /**
     * Get label which supposed to be displayed on frontend.
     *
     * @return string
     */
    public function getFrontendLabel();

    /**
     * Get the note attribute for the element.
     *
     * @return string
     */
    public function getNote();

    /**
     * Whether this is a system attribute.
     *
     * @return bool
     */
    public function isSystem();

    /**
     * Get backend type.
     *
     * @return string
     */
    public function getBackendType();
}
