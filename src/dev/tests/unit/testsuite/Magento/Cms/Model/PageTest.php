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
namespace Magento\Cms\Model;

/**
 * @covers \Magento\Cms\Model\Page
 */
class PageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Cms\Model\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $thisMock;

    /**
     * @var \Magento\Backend\Block\Template\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Cms\Model\Resource\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourcePageMock;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->eventManagerMock = $this->getMockBuilder('Magento\Framework\Event\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->context = $objectManager->getObject(
            'Magento\Framework\Model\Context',
            [
                'eventDispatcher' => $this->eventManagerMock
            ]
        );
        $this->resourcePageMock = $this->getMockBuilder('Magento\Cms\Model\Resource\Page')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getIdFieldName',
                    'checkIdentifier'
                ]
            )
            ->getMock();
        $this->thisMock = $this->getMockBuilder('Magento\Cms\Model\Page')
            ->setConstructorArgs(
                [
                    $this->context,
                    $this->getMockBuilder('Magento\Framework\Registry')
                        ->disableOriginalConstructor()
                        ->getMock(),
                    $this->getMockBuilder('Magento\Framework\Model\Resource\AbstractResource')
                        ->disableOriginalConstructor()
                        ->setMethods(
                            [
                                '_construct',
                                '_getReadAdapter',
                                '_getWriteAdapter'
                            ]
                        )
                        ->getMock(),
                    $this->getMockBuilder('Magento\Framework\Data\Collection\Db')
                        ->disableOriginalConstructor()
                        ->getMock()
                ]
            )
            ->setMethods(
                [
                    '_construct',
                    '_getResource',
                    'load'
                ]
            )
            ->getMock();

        $this->thisMock->expects($this->any())
            ->method('_getResource')
            ->willReturn($this->resourcePageMock);
        $this->thisMock->expects($this->any())
            ->method('load')
            ->willReturnSelf();
    }

    /**
     * @covers \Magento\Cms\Model\Page::noRoutePage
     */
    public function testNoRoutePage()
    {
        $this->assertEquals($this->thisMock, $this->thisMock->noRoutePage());
    }

    /**
     * @covers \Magento\Cms\Model\Page::checkIdentifier
     */
    public function testCheckIdentifier()
    {
        $identifier = 1;
        $storeId = 2;
        $fetchOneResult = 'some result';

        $this->resourcePageMock->expects($this->atLeastOnce())
            ->method('checkIdentifier')
            ->with($identifier, $storeId)
            ->willReturn($fetchOneResult);

        $this->assertInternalType('string', $this->thisMock->checkIdentifier($identifier, $storeId));
    }
}
