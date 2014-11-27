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
namespace Magento\Newsletter\Block\Adminhtml;

use Magento\Newsletter\Model\Resource\Problem\Collection;

/**
 * Newsletter problem block template.
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Problem extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'problem/list.phtml';

    /**
     * @var \Magento\Newsletter\Model\Resource\Problem\Collection
     */
    protected $_problemCollection;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param Collection $problemCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        Collection $problemCollection,
        array $data = array()
    ) {
        $this->_problemCollection = $problemCollection;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $collection = $this->_problemCollection->addSubscriberInfo()->addQueueInfo();
    }

    /**
     * Prepare for the newsletter block layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'deleteButton',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Button',
                'del.button'
            )->setData(
                array('label' => __('Delete Selected Problems'), 'onclick' => 'problemController.deleteSelected();')
            )
        );

        $this->setChild(
            'unsubscribeButton',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Button',
                'unsubscribe.button'
            )->setData(
                array('label' => __('Unsubscribe Selected'), 'onclick' => 'problemController.unsubscribe();')
            )
        );
        return parent::_prepareLayout();
    }

    /**
     * Get the html element for unsubscribe button
     *
     * @return $string
     */
    public function getUnsubscribeButtonHtml()
    {
        return $this->getChildHtml('unsubscribeButton');
    }

    /**
     * Get the html element for delete button
     *
     * @return $string
     */
    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('deleteButton');
    }

    /**
     * Return true if the size is greater than 0
     *
     * @return bool
     */
    public function getShowButtons()
    {
        return $this->_problemCollection->getSize() > 0;
    }
}
