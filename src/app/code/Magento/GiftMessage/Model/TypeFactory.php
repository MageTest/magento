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
namespace Magento\GiftMessage\Model;

/**
 * Factory class for Eav Entity Types
 */
class TypeFactory
{
    /**
     * Allowed types of entities for using of gift messages
     *
     * @var array
     */
    protected $_allowedEntityTypes = array(
        'order' => 'Magento\Sales\Model\Order',
        'order_item' => 'Magento\Sales\Model\Order\Item',
        'order_address' => 'Magento\Sales\Model\Order\Address',
        'quote' => 'Magento\Sales\Model\Quote',
        'quote_item' => 'Magento\Sales\Model\Quote\Item',
        'quote_address' => 'Magento\Sales\Model\Quote\Address',
        'quote_address_item' => 'Magento\Sales\Model\Quote\Address\Item'
    );

    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create type object
     *
     * @param string $eavType
     * @return mixed
     * @throws \Magento\Framework\Model\Exception
     */
    public function createType($eavType)
    {
        $types = $this->_allowedEntityTypes;
        if (!isset($types[$eavType])) {
            throw new \Magento\Framework\Model\Exception(__('Unknown entity type'));
        }
        return $this->_objectManager->create($types[$eavType]);
    }
}
