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
namespace Magento\CustomerImportExport\Model\Export;

/**
 * Export entity customer model
 *
 * @method \Magento\Customer\Model\Resource\Attribute\Collection getAttributeCollection() getAttributeCollection()
 */
class Customer extends \Magento\ImportExport\Model\Export\Entity\AbstractEav
{
    /**#@+
     * Permanent column names.
     *
     * Names that begins with underscore is not an attribute. This name convention is for
     * to avoid interference with same attribute name.
     */
    const COLUMN_EMAIL = 'email';

    const COLUMN_WEBSITE = '_website';

    const COLUMN_STORE = '_store';

    /**#@-*/

    /**#@+
     * Attribute collection name
     */
    const ATTRIBUTE_COLLECTION_NAME = 'Magento\Customer\Model\Resource\Attribute\Collection';

    /**#@-*/

    /**#@+
     * XML path to page size parameter
     */
    const XML_PATH_PAGE_SIZE = 'export/customer_page_size/customer';

    /**#@-*/

    /**
     * Overridden attributes parameters.
     *
     * @var array
     */
    protected $_attributeOverrides = array(
        'created_at' => array('backend_type' => 'datetime'),
        'reward_update_notification' => array('source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean'),
        'reward_warning_notification' => array('source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean')
    );

    /**
     * Array of attributes codes which are disabled for export
     *
     * @var string[]
     */
    protected $_disabledAttributes = array('default_billing', 'default_shipping');

    /**
     * Attributes with index (not label) value.
     *
     * @var string[]
     */
    protected $_indexValueAttributes = array('group_id', 'website_id', 'store_id');

    /**
     * Permanent entity columns.
     *
     * @var string[]
     */
    protected $_permanentAttributes = array(self::COLUMN_EMAIL, self::COLUMN_WEBSITE, self::COLUMN_STORE);

    /**
     * Customers whose data is exported
     *
     * @var \Magento\Customer\Model\Resource\Customer\Collection
     */
    protected $_customerCollection;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\ImportExport\Model\Export\Factory $collectionFactory
     * @param \Magento\ImportExport\Model\Resource\CollectionByPagesIteratorFactory $resourceColFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Customer\Model\Resource\Customer\CollectionFactory $customerColFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\ImportExport\Model\Export\Factory $collectionFactory,
        \Magento\ImportExport\Model\Resource\CollectionByPagesIteratorFactory $resourceColFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Customer\Model\Resource\Customer\CollectionFactory $customerColFactory,
        array $data = array()
    ) {
        parent::__construct(
            $scopeConfig,
            $storeManager,
            $collectionFactory,
            $resourceColFactory,
            $localeDate,
            $eavConfig,
            $data
        );

        $this->_customerCollection = isset(
            $data['customer_collection']
        ) ? $data['customer_collection'] : $customerColFactory->create();

        $this->_initAttributeValues()->_initStores()->_initWebsites(true);
    }

    /**
     * Export process.
     *
     * @return string
     */
    public function export()
    {
        $this->_prepareEntityCollection($this->_getEntityCollection());
        $writer = $this->getWriter();

        // create export file
        $writer->setHeaderCols($this->_getHeaderColumns());
        $this->_exportCollectionByPages($this->_getEntityCollection());

        return $writer->getContents();
    }

    /**
     * Get customers collection
     *
     * @return \Magento\Customer\Model\Resource\Customer\Collection
     */
    protected function _getEntityCollection()
    {
        return $this->_customerCollection;
    }

    /**
     * {@inheritdoc}
     */
    protected function _getHeaderColumns()
    {
        $validAttributeCodes = $this->_getExportAttributeCodes();
        return array_merge($this->_permanentAttributes, $validAttributeCodes, array('password'));
    }

    /**
     * Export given customer data
     *
     * @param \Magento\Customer\Model\Customer $item
     * @return void
     */
    public function exportItem($item)
    {
        $row = $this->_addAttributeValuesToRow($item);
        $row[self::COLUMN_WEBSITE] = $this->_websiteIdToCode[$item->getWebsiteId()];
        $row[self::COLUMN_STORE] = $this->_storeIdToCode[$item->getStoreId()];

        $this->getWriter()->writeRow($row);
    }

    /**
     * Clean up already loaded attribute collection.
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @return \Magento\Framework\Data\Collection
     */
    public function filterAttributeCollection(\Magento\Framework\Data\Collection $collection)
    {
        /** @var $attribute \Magento\Customer\Model\Attribute */
        foreach (parent::filterAttributeCollection($collection) as $attribute) {
            if (!empty($this->_attributeOverrides[$attribute->getAttributeCode()])) {
                $data = $this->_attributeOverrides[$attribute->getAttributeCode()];

                if (isset($data['options_method']) && method_exists($this, $data['options_method'])) {
                    $data['filter_options'] = $this->{$data['options_method']}();
                }
                $attribute->addData($data);
            }
        }
        return $collection;
    }

    /**
     * EAV entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return $this->getAttributeCollection()->getEntityTypeCode();
    }

    /**
     * Retrieve list of overridden attributes
     *
     * @return array
     */
    public function getOverriddenAttributes()
    {
        return $this->_attributeOverrides;
    }
}
