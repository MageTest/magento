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
namespace Magento\Sales\Block\Adminhtml\Order\Status\Assign;

/**
 * Class FormTest
 * @package Magento\Sales\Block\Adminhtml\Order\Status\Assign
 */
class FormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Form
     */
    protected $block;

    /**
     * @var \Magento\Framework\Data\FormFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $formFactory;

    /**
     * @var \Magento\Sales\Model\Resource\Order\Status\CollectionFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Sales\Model\Order\Config | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderConfig;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->formFactory = $this->getMock('Magento\Framework\Data\FormFactory', ['create'], [], '', false);
        $this->collectionFactory = $this->getMock(
            'Magento\Sales\Model\Resource\Order\Status\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->orderConfig = $this->getMock('Magento\Sales\Model\Order\Config', [], [], '', false);

        $this->block = $objectManager->getObject(
            'Magento\Sales\Block\Adminhtml\Order\Status\Assign\Form',
            [
                'formFactory' => $this->formFactory,
                'collectionFactory' => $this->collectionFactory,
                'orderConfig' => $this->orderConfig,
                'data' => ['template' => null]
            ]
        );
    }

    public function testToHtml()
    {
        $statuses = ['status1', 'status2'];
        $states = ['state1', 'state2'];

        $statusesForField = $statuses;
        array_unshift($statusesForField, ['value' => '', 'label' => '']);
        $statesForField = array_merge(['' => ''], $states);

        $form = $this->getMock('Magento\Framework\Data\Form', [], [], '', false);
        $fieldset = $this->getMock('Magento\Framework\Data\Form\Element\Fieldset', [], [], '', false);
        $collection = $this->getMock('Magento\Sales\Model\Resource\Order\Status\Collection', [], [], '', false);

        $form->expects($this->once())
            ->method('addFieldset')
            ->will($this->returnValue($fieldset));
        $this->formFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($form));

        $collection->expects($this->once())
            ->method('toOptionArray')
            ->will($this->returnValue($statuses));
        $this->collectionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($collection));

        $this->orderConfig->expects($this->once())
            ->method('getStates')
            ->will($this->returnValue($states));

        $fieldset->expects($this->at(0))
            ->method('addField')
            ->with(
                'status',
                'select',
                [
                    'name' => 'status',
                    'label' => __('Order Status'),
                    'class' => 'required-entry',
                    'values' => $statusesForField,
                    'required' => true
                ]
            );
        $fieldset->expects($this->at(1))
            ->method('addField')
            ->with(
                'state',
                'select',
                [
                    'name' => 'state',
                    'label' => __('Order State'),
                    'class' => 'required-entry',
                    'values' => $statesForField,
                    'required' => true
                ]
            );
        $fieldset->expects($this->at(2))
            ->method('addField')
            ->with(
                'is_default',
                'checkbox',
                ['name' => 'is_default', 'label' => __('Use Order Status As Default'), 'value' => 1]
            );
        $fieldset->expects($this->at(3))
            ->method('addField')
            ->with(
                'visible_on_front',
                'checkbox',
                ['name' => 'visible_on_front', 'label' => __('Visible On Frontend'), 'value' => 1]
            );

        $this->block->toHtml();
    }
}
