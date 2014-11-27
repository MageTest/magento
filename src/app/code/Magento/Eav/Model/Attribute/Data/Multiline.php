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
namespace Magento\Eav\Model\Attribute\Data;

use Magento\Framework\App\RequestInterface;

/**
 * EAV Entity Attribute Multiply line Data Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Multiline extends \Magento\Eav\Model\Attribute\Data\Text
{
    /**
     * Extract data from request and return value
     *
     * @param RequestInterface $request
     * @return array|string
     */
    public function extractValue(RequestInterface $request)
    {
        $value = $this->_getRequestValue($request);
        if (!is_array($value)) {
            $value = false;
        } else {
            $value = array_map(array($this, '_applyInputFilter'), $value);
        }
        return $value;
    }

    /**
     * Validate data
     * Return true or array of errors
     *
     * @param array|string $value
     * @return bool|array
     */
    public function validateValue($value)
    {
        $errors = array();
        $lines = $this->processValue($value);
        $attribute = $this->getAttribute();
        $attributeLabel = __($attribute->getStoreLabel());
        if ($attribute->getIsRequired() && empty($lines)) {
            $errors[] = __('"%1" is a required value.', $attributeLabel);
        }

        $maxAllowedLineCount = $attribute->getMultilineCount();
        if (count($lines) > $maxAllowedLineCount) {
            $errors[] = __('"%1" cannot contain more than %2 lines.', $attributeLabel, $maxAllowedLineCount);
        }

        foreach ($lines as $lineIndex => $line) {
            // First line must be always validated
            if ($lineIndex == 0 || !empty($line)) {
                $result = parent::validateValue($line);
                if ($result !== true) {
                    $errors = array_merge($errors, $result);
                }
            }
        }

        return (count($errors) == 0) ? true : $errors;
    }

    /**
     * Process value before validation
     *
     * @param bool|string|array $value
     * @return array list of lines represented by given value
     */
    protected function processValue($value)
    {
        if ($value === false) {
            // try to load original value and validate it
            $attribute = $this->getAttribute();
            $entity = $this->getEntity();
            $value = $entity->getDataUsingMethod($attribute->getAttributeCode());
        }
        if (!is_array($value)) {
            $value = explode("\n", $value);
        }
        return $value;
    }

    /**
     * Export attribute value to entity model
     *
     * @param array|string $value
     * @return $this
     */
    public function compactValue($value)
    {
        if (is_array($value)) {
            $value = trim(implode("\n", $value));
        }
        return parent::compactValue($value);
    }

    /**
     * Restore attribute value from SESSION to entity model
     *
     * @param array|string $value
     * @return $this
     */
    public function restoreValue($value)
    {
        return $this->compactValue($value);
    }

    /**
     * Return formated attribute value from entity model
     *
     * @param string $format
     * @return array|string
     */
    public function outputValue($format = \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_TEXT)
    {
        $values = $this->getEntity()->getData($this->getAttribute()->getAttributeCode());
        if (!is_array($values)) {
            $values = explode("\n", $values);
        }
        $values = array_map(array($this, '_applyOutputFilter'), $values);
        switch ($format) {
            case \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_ARRAY:
                $output = $values;
                break;
            case \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_HTML:
                $output = implode("<br />", $values);
                break;
            case \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_ONELINE:
                $output = implode(" ", $values);
                break;
            default:
                $output = implode("\n", $values);
                break;
        }
        return $output;
    }
}
