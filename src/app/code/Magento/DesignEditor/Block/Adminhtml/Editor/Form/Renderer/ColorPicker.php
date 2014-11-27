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
namespace Magento\DesignEditor\Block\Adminhtml\Editor\Form\Renderer;

/**
 * Color-picker form element renderer
 */
class ColorPicker extends \Magento\DesignEditor\Block\Adminhtml\Editor\Form\Renderer\Recursive
{
    /**
     * Set of templates to render
     *
     * Upper is rendered first and is inserted into next using <?php echo $this->getHtml() ?>
     * Templates used are based fieldset/element.phtml but split into several templates
     *
     * @var string[]
     */
    protected $_templates = array(
        'Magento_DesignEditor::editor/form/renderer/element/input.phtml',
        'Magento_DesignEditor::editor/form/renderer/color-picker.phtml',
        'Magento_DesignEditor::editor/form/renderer/element/wrapper.phtml',
        'Magento_DesignEditor::editor/form/renderer/simple.phtml'
    );

    /**
     * Get HTMl class of a field
     *
     * Actually it will be added to a field wrapper
     *
     * @return string[]
     */
    public function getFieldClass()
    {
        /** @var $element \Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\ColorPicker */
        $element = $this->getElement();

        $elementBeforeLabel = $element->getExtType() == 'checkbox' || $element->getExtType() == 'radio';
        $addOn = $element->getBeforeElementHtml() || $element->getAfterElementHtml();

        //@TODO add class that show the control type 'color-picker' for this one
        $classes = array();
        $classes[] = 'field';
        $classes[] = 'field-' . $element->getId();
        $classes[] = $element->getCssClass();
        if ($elementBeforeLabel) {
            $classes[] = 'choice';
        }
        if ($addOn) {
            $classes[] = 'with-addon';
        }
        if ($element->getRequired()) {
            $classes[] = 'required';
        }
        if ($element->getNote()) {
            $classes[] = 'with-note';
        }

        return $classes;
    }

    /**
     * Get field attributes string
     *
     * Actually it will be added to a field wrapper
     *
     * @see Magento_DesignEditor::editor/form/renderer/simple.phtml
     * @return string
     */
    public function getFieldAttributes()
    {
        $element = $this->getElement();

        $fieldAttributes = array();
        if ($element->getHtmlContainerId()) {
            $fieldAttributes[] = sprintf('id="%s"', $element->getHtmlContainerId());
        }
        $fieldAttributes[] = sprintf('class="%s"', join(' ', $this->getFieldClass()));
        $fieldAttributes[] = $this->getUiId('form-field', $element->getId());

        return join(' ', $fieldAttributes);
    }
}
