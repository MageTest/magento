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
namespace Magento\Eav\Model\Entity\Increment;

class AlphanumTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Alphanum
     */
    private $model;

    protected function setUp()
    {
        $this->model = new Alphanum();
    }

    public function testGetAllowedChars()
    {
        $this->assertEquals('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', $this->model->getAllowedChars());
    }

    /**
     * @param int $lastId
     * @param string $prefix
     * @param int|string $expectedResult
     * @dataProvider getLastIdDataProvider
     */
    public function testGetNextId($lastId, $prefix, $expectedResult)
    {
        $this->model->setPrefix($prefix);
        $this->model->setLastId($lastId);
        $this->assertEquals($expectedResult, $this->model->getNextId());
    }

    public function getLastIdDataProvider()
    {
        return [
            [
                'lastId' => 'prefix00000001CZ',
                'prefix' => 'prefix',
                'expectedResult' => 'prefix00000001D0'
            ],
            [
                'lastId' => 1,
                'prefix' => 'prefix',
                'expectedResult' => 'prefix00000002'
            ],
        ];
    }

    /**
     * @expectedException \Magento\Eav\Exception
     * @expectedExceptionMessage Invalid character encountered in increment ID: ---wrong-id---
     */
    public function testGetNextIdThrowsExceptionIfIdContainsNotAllowedCharacters()
    {
        $this->model->setLastId('---wrong-id---');
        $this->model->setPrefix('prefix');
        $this->model->getNextId();
    }
}
