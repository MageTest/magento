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
namespace Magento\Framework\File;

class MimeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\File\Mime
     */
    private $object;

    protected function setUp()
    {
        $this->object = new \Magento\Framework\File\Mime;
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage File 'nonexistent.file' doesn't exist
     */
    public function testGetMimeTypeNonexistentFileException()
    {
        $file = 'nonexistent.file';
        $this->object->getMimeType($file);
    }

    /**
     * @param string $file
     * @param string $expectedType
     *
     * @dataProvider getMimeTypeDataProvider
     */
    public function testGetMimeType($file, $expectedType)
    {
        $actualType = $this->object->getMimeType($file);
        $this->assertSame($expectedType, $actualType);
    }

    /**
     * @return array
     */
    public function getMimeTypeDataProvider()
    {
        return array(
            'javascript' => array(__DIR__ . '/_files/javascript.js', 'application/javascript'),
            'weird extension' => array(__DIR__ . '/_files/file.weird', 'application/octet-stream'),
        );
    }
}
