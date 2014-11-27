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
namespace Magento\Sales\Service\V1\Action;

/**
 * Class CreditmemoCancelTest
 */
class CreditmemoCancelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Service\V1\Action\CreditmemoCancel
     */
    protected $creditmemoCancel;

    /**
     * @var \Magento\Sales\Model\Order\CreditmemoRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoRepositoryMock;

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoMock;

    /**
     * SetUp
     */
    protected function setUp()
    {
        $this->creditmemoRepositoryMock = $this->getMock(
            'Magento\Sales\Model\Order\CreditmemoRepository',
            ['get'],
            [],
            '',
            false
        );
        $this->creditmemoMock = $this->getMock(
            'Magento\Sales\Model\Order\Creditmemo',
            [],
            [],
            '',
            false
        );
        $this->creditmemoCancel = new CreditmemoCancel(
            $this->creditmemoRepositoryMock
        );
    }

    /**
     * test creditmemo cancel service
     */
    public function testInvoke()
    {
        $this->creditmemoRepositoryMock->expects($this->once())
            ->method('get')
            ->with($this->equalTo(1))
            ->will($this->returnValue($this->creditmemoMock));
        $this->creditmemoMock->expects($this->once())
            ->method('cancel')
            ->will($this->returnSelf());
        $this->assertTrue($this->creditmemoCancel->invoke(1));
    }
}
