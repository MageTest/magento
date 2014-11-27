<?php
/**
 * Google Optimizer Category Tab
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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\GoogleOptimizer\Block\Adminhtml\Catalog\Category\Edit\Tab;

class Googleoptimizer extends \Magento\Catalog\Block\Adminhtml\Form
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Magento\GoogleOptimizer\Helper\Code
     */
    protected $_codeHelper;

    /**
     * @var \Magento\GoogleOptimizer\Helper\Form
     */
    protected $_formHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\GoogleOptimizer\Helper\Code $codeHelper
     * @param \Magento\GoogleOptimizer\Helper\Form $formHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\GoogleOptimizer\Helper\Code $codeHelper,
        \Magento\GoogleOptimizer\Helper\Form $formHelper,
        array $data = array()
    ) {
        parent::__construct($context, $registry, $formFactory, $data);

        $this->_codeHelper = $codeHelper;
        $this->_formHelper = $formHelper;
        $this->setForm($formFactory->create());
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm()
    {
        $this->_formHelper->addGoogleoptimizerFields($this->getForm(), $this->_getGoogleExperiment());
        return parent::_prepareForm();
    }

    /**
     * Get google experiment code model
     *
     * @return \Magento\GoogleOptimizer\Model\Code|null
     * @throws \RuntimeException
     */
    protected function _getGoogleExperiment()
    {
        $category = $this->_getCategory();
        if ($category->getId()) {
            return $this->_codeHelper->getCodeObjectByEntity($category);
        }
        return null;
    }

    /**
     * Get category model from registry
     *
     * @return mixed
     * @throws \RuntimeException
     */
    protected function _getCategory()
    {
        $entity = $this->_coreRegistry->registry('current_category');
        if (!$entity) {
            throw new \RuntimeException('Entity is not found in registry.');
        }
        return $entity;
    }
}
