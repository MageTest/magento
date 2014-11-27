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
 * Directory Region Resource Model
 */
namespace Magento\Directory\Model\Resource;

class Region extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Table with localized region names
     *
     * @var string
     */
    protected $_regionNameTable;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     */
    public function __construct(\Magento\Framework\App\Resource $resource, \Magento\Framework\Locale\ResolverInterface $localeResolver)
    {
        parent::__construct($resource);
        $this->_localeResolver = $localeResolver;
    }

    /**
     * Define main and locale region name tables
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('directory_country_region', 'region_id');
        $this->_regionNameTable = $this->getTable('directory_country_region_name');
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\DB\Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $adapter = $this->_getReadAdapter();

        $locale = $this->_localeResolver->getLocaleCode();
        $systemLocale = \Magento\Framework\AppInterface::DISTRO_LOCALE_CODE;

        $regionField = $adapter->quoteIdentifier($this->getMainTable() . '.' . $this->getIdFieldName());

        $condition = $adapter->quoteInto('lrn.locale = ?', $locale);
        $select->joinLeft(
            array('lrn' => $this->_regionNameTable),
            "{$regionField} = lrn.region_id AND {$condition}",
            array()
        );

        if ($locale != $systemLocale) {
            $nameExpr = $adapter->getCheckSql('lrn.region_id is null', 'srn.name', 'lrn.name');
            $condition = $adapter->quoteInto('srn.locale = ?', $systemLocale);
            $select->joinLeft(
                array('srn' => $this->_regionNameTable),
                "{$regionField} = srn.region_id AND {$condition}",
                array('name' => $nameExpr)
            );
        } else {
            $select->columns(array('name'), 'lrn');
        }

        return $select;
    }

    /**
     * Load object by country id and code or default name
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param int $countryId
     * @param string $value
     * @param string $field
     * @return $this
     */
    protected function _loadByCountry($object, $countryId, $value, $field)
    {
        $adapter = $this->_getReadAdapter();
        $locale = $this->_localeResolver->getLocaleCode();
        $joinCondition = $adapter->quoteInto('rname.region_id = region.region_id AND rname.locale = ?', $locale);
        $select = $adapter->select()->from(
            array('region' => $this->getMainTable())
        )->joinLeft(
            array('rname' => $this->_regionNameTable),
            $joinCondition,
            array('name')
        )->where(
            'region.country_id = ?',
            $countryId
        )->where(
            "region.{$field} = ?",
            $value
        );

        $data = $adapter->fetchRow($select);
        if ($data) {
            $object->setData($data);
        }

        $this->_afterLoad($object);

        return $this;
    }

    /**
     * Loads region by region code and country id
     *
     * @param \Magento\Directory\Model\Region $region
     * @param string $regionCode
     * @param string $countryId
     *
     * @return $this
     */
    public function loadByCode(\Magento\Directory\Model\Region $region, $regionCode, $countryId)
    {
        return $this->_loadByCountry($region, $countryId, (string)$regionCode, 'code');
    }

    /**
     * Load data by country id and default region name
     *
     * @param \Magento\Directory\Model\Region $region
     * @param string $regionName
     * @param string $countryId
     * @return $this
     */
    public function loadByName(\Magento\Directory\Model\Region $region, $regionName, $countryId)
    {
        return $this->_loadByCountry($region, $countryId, (string)$regionName, 'default_name');
    }
}
