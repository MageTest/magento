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
namespace Magento\Sales\Block\Order;

class CommentsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Block\Order\Comments
     */
    protected $_block;

    protected function setUp()
    {
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Sales\Block\Order\Comments'
        );
    }

    /**
     * @param string $commentedEntity
     * @param string $expectedClass
     * @dataProvider getCommentsDataProvider
     */
    public function testGetComments($commentedEntity, $expectedClass)
    {
        $commentedEntity = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create($commentedEntity);
        $this->_block->setEntity($commentedEntity);
        $comments = $this->_block->getComments();
        $this->assertInstanceOf($expectedClass, $comments);
    }

    /**
     * @return array
     */
    public function getCommentsDataProvider()
    {
        return array(
            array(
                'Magento\Sales\Model\Order\Invoice',
                'Magento\Sales\Model\Resource\Order\Invoice\Comment\Collection'
            ),
            array(
                'Magento\Sales\Model\Order\Creditmemo',
                'Magento\Sales\Model\Resource\Order\Creditmemo\Comment\Collection'
            ),
            array(
                'Magento\Sales\Model\Order\Shipment',
                'Magento\Sales\Model\Resource\Order\Shipment\Comment\Collection'
            )
        );
    }

    /**
     * @expectedException \Magento\Framework\Model\Exception
     */
    public function testGetCommentsWrongEntityException()
    {
        $entity = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
        $this->_block->setEntity($entity);
        $this->_block->getComments();
    }
}
