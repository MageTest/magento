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
namespace Magento\TaxImportExport\Model\Rate;

/**
 * Tax Rate CSV Import Handler
 */
class CsvImportHandler
{
    /**
     * Collection of publicly available stores
     *
     * @var \Magento\Store\Model\Resource\Store\Collection
     */
    protected $_publicStores;

    /**
     * Region collection prototype
     *
     * The instance is used to retrieve regions based on country code
     *
     * @var \Magento\Directory\Model\Resource\Region\Collection
     */
    protected $_regionCollection;

    /**
     * Country factory
     *
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $_countryFactory;

    /**
     * Tax rate factory
     *
     * @var \Magento\Tax\Model\Calculation\RateFactory
     */
    protected $_taxRateFactory;

    /**
     * @param \Magento\Store\Model\Resource\Store\Collection $storeCollection
     * @param \Magento\Directory\Model\Resource\Region\Collection $regionCollection
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Tax\Model\Calculation\RateFactory $taxRateFactory
     */
    public function __construct(
        \Magento\Store\Model\Resource\Store\Collection $storeCollection,
        \Magento\Directory\Model\Resource\Region\Collection $regionCollection,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Tax\Model\Calculation\RateFactory $taxRateFactory
    ) {
        // prevent admin store from loading
        $this->_publicStores = $storeCollection->setLoadDefault(false);
        $this->_regionCollection = $regionCollection;
        $this->_countryFactory = $countryFactory;
        $this->_taxRateFactory = $taxRateFactory;
    }

    /**
     * Retrieve a list of fields required for CSV file (order is important!)
     *
     * @return array
     */
    public function getRequiredCsvFields()
    {
        // indexes are specified for clarity, they are used during import
        return array(
            0 => __('Code'),
            1 => __('Country'),
            2 => __('State'),
            3 => __('Zip/Post Code'),
            4 => __('Rate'),
            5 => __('Zip/Post is Range'),
            6 => __('Range From'),
            7 => __('Range To')
        );
    }

    /**
     * Import Tax Rates from CSV file
     *
     * @param array $file file info retrieved from $_FILES array
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    public function importFromCsvFile($file)
    {
        if (!isset($file['tmp_name'])) {
            throw new \Magento\Framework\Model\Exception('Invalid file upload attempt.');
        }
        $csvProcessor = new \Magento\Framework\File\Csv();
        $ratesRawData = $csvProcessor->getData($file['tmp_name']);
        // first row of file represents headers
        $fileFields = $ratesRawData[0];
        $validFields = $this->_filterFileFields($fileFields);
        $invalidFields = array_diff_key($fileFields, $validFields);
        $ratesData = $this->_filterRateData($ratesRawData, $invalidFields, $validFields);
        // store cache array is used to quickly retrieve store ID when handling locale-specific tax rate titles
        $storesCache = $this->_composeStoreCache($validFields);
        $regionsCache = array();
        foreach ($ratesData as $rowIndex => $dataRow) {
            // skip headers
            if ($rowIndex == 0) {
                continue;
            }
            $regionsCache = $this->_importRate($dataRow, $regionsCache, $storesCache);
        }
    }

    /**
     * Filter file fields (i.e. unset invalid fields)
     *
     * @param array $fileFields
     * @return string[] filtered fields
     */
    protected function _filterFileFields(array $fileFields)
    {
        $filteredFields = $this->getRequiredCsvFields();
        $requiredFieldsNum = count($this->getRequiredCsvFields());
        $fileFieldsNum = count($fileFields);

        // process title-related fields that are located right after required fields with store code as field name)
        for ($index = $requiredFieldsNum; $index < $fileFieldsNum; $index++) {
            $titleFieldName = $fileFields[$index];
            if ($this->_isStoreCodeValid($titleFieldName)) {
                // if store is still valid, append this field to valid file fields
                $filteredFields[$index] = $titleFieldName;
            }
        }

        return $filteredFields;
    }

    /**
     * Filter rates data (i.e. unset all invalid fields and check consistency)
     *
     * @param array $rateRawData
     * @param array $invalidFields assoc array of invalid file fields
     * @param array $validFields assoc array of valid file fields
     * @return array
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _filterRateData(array $rateRawData, array $invalidFields, array $validFields)
    {
        $validFieldsNum = count($validFields);
        foreach ($rateRawData as $rowIndex => $dataRow) {
            // skip empty rows
            if (count($dataRow) <= 1) {
                unset($rateRawData[$rowIndex]);
                continue;
            }
            // unset invalid fields from data row
            foreach ($dataRow as $fieldIndex => $fieldValue) {
                if (isset($invalidFields[$fieldIndex])) {
                    unset($rateRawData[$rowIndex][$fieldIndex]);
                }
            }
            // check if number of fields in row match with number of valid fields
            if (count($rateRawData[$rowIndex]) != $validFieldsNum) {
                throw new \Magento\Framework\Model\Exception('Invalid file format.');
            }
        }
        return $rateRawData;
    }

    /**
     * Compose stores cache
     *
     * This cache is used to quickly retrieve store ID when handling locale-specific tax rate titles
     *
     * @param string[] $validFields list of valid CSV file fields
     * @return array
     */
    protected function _composeStoreCache($validFields)
    {
        $storesCache = array();
        $requiredFieldsNum = count($this->getRequiredCsvFields());
        $validFieldsNum = count($validFields);
        // title related fields located right after required fields
        for ($index = $requiredFieldsNum; $index < $validFieldsNum; $index++) {
            foreach ($this->_publicStores as $store) {
                $storeCode = $validFields[$index];
                if ($storeCode === $store->getCode()) {
                    $storesCache[$index] = $store->getId();
                }
            }
        }
        return $storesCache;
    }

    /**
     * Check if public store with specified code still exists
     *
     * @param string $storeCode
     * @return boolean
     */
    protected function _isStoreCodeValid($storeCode)
    {
        $isStoreCodeValid = false;
        foreach ($this->_publicStores as $store) {
            if ($storeCode === $store->getCode()) {
                $isStoreCodeValid = true;
                break;
            }
        }
        return $isStoreCodeValid;
    }

    /**
     * Import single rate
     *
     * @param array $rateData
     * @param array $regionsCache cache of regions of already used countries (is used to optimize performance)
     * @param array $storesCache cache of stores related to tax rate titles
     * @return array regions cache populated with regions related to country of imported tax rate
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _importRate(array $rateData, array $regionsCache, array $storesCache)
    {
        // data with index 1 must represent country code
        $countryCode = $rateData[1];
        $country = $this->_countryFactory->create()->loadByCode($countryCode, 'iso2_code');
        if (!$country->getId()) {
            throw new \Magento\Framework\Model\Exception('One of the countries has invalid code.');
        }
        $regionsCache = $this->_addCountryRegionsToCache($countryCode, $regionsCache);

        // data with index 2 must represent region code
        $regionCode = $rateData[2];
        if (!empty($regionsCache[$countryCode][$regionCode])) {
            $regionId = $regionsCache[$countryCode][$regionCode] == '*' ? 0 : $regionsCache[$countryCode][$regionCode];
            // data with index 3 must represent postcode
            $postCode = empty($rateData[3]) ? null : $rateData[3];
            $modelData = array(
                'code' => $rateData[0],
                'tax_country_id' => $rateData[1],
                'tax_region_id' => $regionId,
                'tax_postcode' => $postCode,
                'rate' => $rateData[4],
                'zip_is_range' => $rateData[5],
                'zip_from' => $rateData[6],
                'zip_to' => $rateData[7]
            );

            // try to load existing rate
            /** @var $rateModel \Magento\Tax\Model\Calculation\Rate */
            $rateModel = $this->_taxRateFactory->create()->loadByCode($modelData['code']);
            $rateModel->addData($modelData);

            // compose titles list
            $rateTitles = array();
            foreach ($storesCache as $fileFieldIndex => $storeId) {
                $rateTitles[$storeId] = $rateData[$fileFieldIndex];
            }

            $rateModel->setTitle($rateTitles);
            $rateModel->save();
        }

        return $regionsCache;
    }

    /**
     * Add regions of the given country to regions cache
     *
     * @param string $countryCode
     * @param array $regionsCache
     * @return array
     */
    protected function _addCountryRegionsToCache($countryCode, array $regionsCache)
    {
        if (!isset($regionsCache[$countryCode])) {
            $regionsCache[$countryCode] = array();
            // add 'All Regions' to the list
            $regionsCache[$countryCode]['*'] = '*';
            $regionCollection = clone $this->_regionCollection;
            $regionCollection->addCountryFilter($countryCode);
            if ($regionCollection->getSize()) {
                foreach ($regionCollection as $region) {
                    $regionsCache[$countryCode][$region->getCode()] = $region->getRegionId();
                }
            }
        }
        return $regionsCache;
    }
}
