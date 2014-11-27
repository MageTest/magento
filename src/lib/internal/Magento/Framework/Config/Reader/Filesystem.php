<?php
/**
 * Filesystem configuration loader. Loads configuration from XML files, split by scopes
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
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
namespace Magento\Framework\Config\Reader;

/**
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class Filesystem implements \Magento\Framework\Config\ReaderInterface
{
    /**
     * File locator
     *
     * @var \Magento\Framework\Config\FileResolverInterface
     */
    protected $_fileResolver;

    /**
     * Config converter
     *
     * @var \Magento\Framework\Config\ConverterInterface
     */
    protected $_converter;

    /**
     * The name of file that stores configuration
     *
     * @var string
     */
    protected $_fileName;

    /**
     * Path to corresponding XSD file with validation rules for merged config
     *
     * @var string
     */
    protected $_schema;

    /**
     * Path to corresponding XSD file with validation rules for separate config files
     *
     * @var string
     */
    protected $_perFileSchema;

    /**
     * List of id attributes for merge
     *
     * @var array
     */
    protected $_idAttributes = array();

    /**
     * Class of dom configuration document used for merge
     *
     * @var string
     */
    protected $_domDocumentClass;

    /**
     * Should configuration be validated
     *
     * @var bool
     */
    protected $_isValidated;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Config\FileResolverInterface $fileResolver
     * @param \Magento\Framework\Config\ConverterInterface $converter
     * @param \Magento\Framework\Config\SchemaLocatorInterface $schemaLocator
     * @param \Magento\Framework\Config\ValidationStateInterface $validationState
     * @param string $fileName
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param string $defaultScope
     */
    public function __construct(
        \Magento\Framework\Config\FileResolverInterface $fileResolver,
        \Magento\Framework\Config\ConverterInterface $converter,
        \Magento\Framework\Config\SchemaLocatorInterface $schemaLocator,
        \Magento\Framework\Config\ValidationStateInterface $validationState,
        $fileName,
        $idAttributes = array(),
        $domDocumentClass = 'Magento\Framework\Config\Dom',
        $defaultScope = 'global'
    ) {
        $this->_fileResolver = $fileResolver;
        $this->_converter = $converter;
        $this->_fileName = $fileName;
        $this->_idAttributes = array_replace($this->_idAttributes, $idAttributes);
        $this->_schemaFile = $schemaLocator->getSchema();
        $this->_isValidated = $validationState->isValidated();
        $this->_perFileSchema = $schemaLocator->getPerFileSchema() &&
            $this->_isValidated ? $schemaLocator->getPerFileSchema() : null;
        $this->_domDocumentClass = $domDocumentClass;
        $this->_defaultScope = $defaultScope;
    }

    /**
     * Load configuration scope
     *
     * @param string|null $scope
     * @return array
     */
    public function read($scope = null)
    {
        $scope = $scope ?: $this->_defaultScope;
        $fileList = $this->_fileResolver->get($this->_fileName, $scope);
        if (!count($fileList)) {
            return array();
        }
        $output = $this->_readFiles($fileList);

        return $output;
    }

    /**
     * Read configuration files
     *
     * @param array $fileList
     * @return array
     * @throws \Magento\Framework\Exception
     */
    protected function _readFiles($fileList)
    {
        /** @var \Magento\Framework\Config\Dom $configMerger */
        $configMerger = null;
        foreach ($fileList as $key => $content) {
            try {
                if (!$configMerger) {
                    $configMerger = $this->_createConfigMerger($this->_domDocumentClass, $content);
                } else {
                    $configMerger->merge($content);
                }
            } catch (\Magento\Framework\Config\Dom\ValidationException $e) {
                throw new \Magento\Framework\Exception("Invalid XML in file " . $key . ":\n" . $e->getMessage());
            }
        }
        if ($this->_isValidated) {
            $errors = array();
            if ($configMerger && !$configMerger->validate($this->_schemaFile, $errors)) {
                $message = "Invalid Document \n";
                throw new \Magento\Framework\Exception($message . implode("\n", $errors));
            }
        }

        $output = array();
        if ($configMerger) {
            $output = $this->_converter->convert($configMerger->getDom());
        }
        return $output;
    }

    /**
     * Return newly created instance of a config merger
     *
     * @param string $mergerClass
     * @param string $initialContents
     * @return \Magento\Framework\Config\Dom
     * @throws \UnexpectedValueException
     */
    protected function _createConfigMerger($mergerClass, $initialContents)
    {
        $result = new $mergerClass($initialContents, $this->_idAttributes, null, $this->_perFileSchema);
        if (!$result instanceof \Magento\Framework\Config\Dom) {
            throw new \UnexpectedValueException(
                "Instance of the DOM config merger is expected, got {$mergerClass} instead."
            );
        }
        return $result;
    }
}
