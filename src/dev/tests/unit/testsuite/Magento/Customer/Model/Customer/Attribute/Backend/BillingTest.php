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
namespace Magento\Customer\Model\Customer\Attribute\Backend;

class BillingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Billing
     */
    protected $testable;

    public function setUp()
    {
        $logger = $this->getMockBuilder('Magento\Framework\Logger')->disableOriginalConstructor()->getMock();
        /** @var \Magento\Framework\Logger $logger */
        $this->testable = new Billing($logger);
    }

    public function testBeforeSave()
    {
        $object = $this->getMockBuilder('Magento\Framework\Object')
            ->disableOriginalConstructor()
            ->setMethods(array('getDefaultBilling', 'unsetDefaultBilling'))
            ->getMock();

        $object->expects($this->once())->method('getDefaultBilling')->will($this->returnValue(null));
        $object->expects($this->once())->method('unsetDefaultBilling')->will($this->returnSelf());
        /** @var \Magento\Framework\Object $object */

        $this->testable->beforeSave($object);
    }

    public function testAfterSave()
    {
        $addressId = 1;
        $attributeCode = 'attribute_code';
        $defaultBilling = 'default billing address';
        $object = $this->getMockBuilder('Magento\Framework\Object')
            ->disableOriginalConstructor()
            ->setMethods(array('getDefaultBilling', 'getAddresses', 'setDefaultBilling'))
            ->getMock();

        $address = $this->getMockBuilder('Magento\Framework\Object')
            ->disableOriginalConstructor()
            ->setMethods(array('getPostIndex', 'getId'))
            ->getMock();

        $attribute = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\AbstractAttribute')
            ->setMethods(array('__wakeup', 'getEntity', 'getAttributeCode'))
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $entity = $this->getMockBuilder('Magento\Eav\Model\Entity\AbstractEntity')
            ->setMethods(array('saveAttribute'))
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $attribute->expects($this->once())->method('getEntity')->will($this->returnValue($entity));
        $attribute->expects($this->once())->method('getAttributeCode')->will($this->returnValue($attributeCode));
        $entity->expects($this->once())->method('saveAttribute')->with($this->logicalOr($object, $attributeCode));
        $address->expects($this->once())->method('getPostIndex')->will($this->returnValue($defaultBilling));
        $address->expects($this->once())->method('getId')->will($this->returnValue($addressId));
        $object->expects($this->once())->method('getDefaultBilling')->will($this->returnValue($defaultBilling));
        $object->expects($this->once())->method('setDefaultBilling')->with($addressId)->will($this->returnSelf());
        $object->expects($this->once())->method('getAddresses')->will($this->returnValue(array($address)));
        /** @var \Magento\Framework\Object $object */
        /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute */

        $this->testable->setAttribute($attribute);
        $this->testable->afterSave($object);
    }
}
