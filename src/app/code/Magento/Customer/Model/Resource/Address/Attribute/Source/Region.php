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


/**
 * Customer region attribute source
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Customer\Model\Resource\Address\Attribute\Source;

class Region extends \Magento\Eav\Model\Entity\Attribute\Source\Table
{
    /**
     * @var \Magento\Directory\Model\Resource\Region\CollectionFactory
     */
    protected $_regionsFactory;

    /**
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\OptionFactory $attrOptionFactory
     * @param \Magento\Directory\Model\Resource\Region\CollectionFactory $regionsFactory
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Eav\Model\Resource\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Eav\Model\Resource\Entity\Attribute\OptionFactory $attrOptionFactory,
        \Magento\Directory\Model\Resource\Region\CollectionFactory $regionsFactory
    ) {
        $this->_regionsFactory = $regionsFactory;
        parent::__construct($coreData, $attrOptionCollectionFactory, $attrOptionFactory);
    }

    /**
     * Retrieve all region options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = $this->_createRegionsCollection()->load()->toOptionArray();
        }
        return $this->_options;
    }

    /**
     * @return \Magento\Directory\Model\Resource\Region\Collection
     */
    protected function _createRegionsCollection()
    {
        return $this->_regionsFactory->create();
    }
}
