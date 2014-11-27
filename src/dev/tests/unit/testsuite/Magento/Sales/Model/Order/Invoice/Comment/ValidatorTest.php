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
namespace Magento\Sales\Model\Order\Invoice\Comment;

/**
 * Class ValidatorTest
 */
class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Invoice\Comment\Validator
     */
    protected $validator;

    /**
     * @var \Magento\Sales\Model\Order\Invoice\Comment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $commentModelMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->commentModelMock = $this->getMock(
            'Magento\Sales\Model\Order\Invoice\Comment',
            ['hasData', 'getData', '__wakeup'],
            [],
            '',
            false
        );
        $this->validator = new \Magento\Sales\Model\Order\Invoice\Comment\Validator();
    }


    /**
     * Run test validate
     *
     * @param $commentDataMap
     * @param $commentData
     * @param $expectedWarnings
     * @dataProvider providerCommentData
     */
    public function testValidate($commentDataMap, $commentData, $expectedWarnings)
    {
        $this->commentModelMock->expects($this->any())
            ->method('hasData')
            ->will($this->returnValueMap($commentDataMap));
        $this->commentModelMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($commentData));
        $actualWarnings = $this->validator->validate($this->commentModelMock);
        $this->assertEquals($expectedWarnings, $actualWarnings);
    }

    /**
     * Provides comment data for tests
     *
     * @return array
     */
    public function providerCommentData()
    {
        return [
            [
                [
                    ['parent_id', true],
                    ['comment', true]
                ],
                [
                    'parent_id' => 25,
                    'comment' => 'Hello world!'
                ],
                []
            ],
            [
                [
                    ['parent_id', true],
                    ['comment', false]
                ],
                [
                    'parent_id' => 0,
                    'comment' => null
                ],
                [
                    'parent_id' => 'Parent Invoice Id can not be empty',
                    'comment' => 'Comment is a required field'
                ]
            ]
        ];
    }
}
 