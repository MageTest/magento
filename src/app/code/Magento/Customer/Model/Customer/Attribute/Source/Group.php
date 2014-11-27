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
namespace Magento\Customer\Model\Customer\Attribute\Source;

/**
 * Customer group attribute source
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Group extends \Magento\Eav\Model\Entity\Attribute\Source\Table
{
    /**
     * @var \Magento\Customer\Service\V1\CustomerGroupServiceInterface
     */
    protected $_groupService;

    /**
     * @var \Magento\Framework\Convert\Object
     */
    protected $_converter;

    /**
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\OptionFactory $attrOptionFactory
     * @param \Magento\Customer\Service\V1\CustomerGroupServiceInterface $groupService
     * @param \Magento\Framework\Convert\Object $converter
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Eav\Model\Resource\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Eav\Model\Resource\Entity\Attribute\OptionFactory $attrOptionFactory,
        \Magento\Customer\Service\V1\CustomerGroupServiceInterface $groupService,
        \Magento\Framework\Convert\Object $converter
    ) {
        $this->_groupService = $groupService;
        $this->_converter = $converter;
        parent::__construct($coreData, $attrOptionCollectionFactory, $attrOptionFactory);
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $groups = $this->_groupService->getGroups(false);
            $this->_options = $this->_converter->toOptionArray($groups, 'id', 'code');
        }
        return $this->_options;
    }
}
