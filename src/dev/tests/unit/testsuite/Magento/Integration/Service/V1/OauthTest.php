<?php
/**
 * Test for \Magento\Integration\Service\V1\Oauth
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Integration\Service\V1;

use Magento\Integration\Model\Integration;
use Magento\Integration\Model\Oauth\Token;

class OauthTest extends \PHPUnit_Framework_TestCase
{
    const VALUE_CONSUMER_ID = 1;

    const VALUE_CONSUMER_KEY = 'asdfghjklaqwerfdtyuiomnbgfdhbsoi';

    const VALUE_TOKEN_TYPE = 'access';

    /** @var \Magento\Integration\Model\Oauth\Consumer\Factory|\PHPUnit_Framework_MockObject_MockObject */
    protected $_consumerFactory;

    /** @var \Magento\Integration\Model\Oauth\Token\Provider|\PHPUnit_Framework_MockObject_MockObject */
    protected $_tokenProviderMock;

    /** @var \Magento\Integration\Model\Oauth\Consumer|\PHPUnit_Framework_MockObject_MockObject */
    private $_consumerMock;

    /** @var \Magento\Integration\Model\Integration|\PHPUnit_Framework_MockObject_MockObject */
    private $_emptyConsumerMock;

    /**
     * @var \Magento\Integration\Model\Oauth\Token|\PHPUnit_Framework_MockObject_MockObject
     */
    private $_tokenMock;

    /** @var \Magento\Integration\Service\V1\Oauth */
    private $_service;

    /** @var array */
    private $_consumerData;

    /**
     * @var \Magento\Integration\Model\Oauth\Token\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $_tokenFactoryMock;

    protected function setUp()
    {
        $this->_consumerFactory = $this->getMockBuilder(
            'Magento\Integration\Model\Oauth\Consumer\Factory'
        )->disableOriginalConstructor()->getMock();
        $this->_tokenProviderMock = $this->getMockBuilder(
            'Magento\Integration\Model\Oauth\Token\Provider'
        )->disableOriginalConstructor()->getMock();
        $this->_tokenMock = $this->getMockBuilder(
            'Magento\Integration\Model\Oauth\Token'
        )->disableOriginalConstructor()->setMethods(
            array('createVerifierToken', 'getType', '__wakeup', 'delete')
        )->getMock();

        $this->_tokenFactoryMock = $this->getMock(
            'Magento\Integration\Model\Oauth\Token\Factory',
            array(),
            array(),
            '',
            false
        );
        $this->_consumerMock = $this->getMockBuilder(
            'Magento\Integration\Model\Oauth\Consumer'
        )->disableOriginalConstructor()->setMethods(
            array('getData', 'getId', 'load', 'save', 'delete', '__wakeup')
        )->getMock();
        $this->_consumerData = array(
            'entity_id' => self::VALUE_CONSUMER_ID,
            'key' => self::VALUE_CONSUMER_KEY,
            'secret' => 'iuyytrfdsdfbnnhbmkkjlkjl',
            'created_at' => '',
            'updated_at' => '',
            'callback_url' => '',
            'rejected_callback_url' => ''
        );
        $this->_consumerFactory->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_consumerMock)
        );

        $this->_service = new \Magento\Integration\Service\V1\Oauth(
            $this->getMock('Magento\Framework\StoreManagerInterface', array(), array(), '', false),
            $this->_consumerFactory,
            $this->_tokenFactoryMock,
            $this->getMock('Magento\Integration\Helper\Oauth\Data', array(), array(), '', false),
            $this->getMock('Magento\Framework\HTTP\ZendClient', array(), array(), '', false),
            $this->getMock('Magento\Framework\Logger', array(), array(), '', false),
            $this->getMock('Magento\Framework\Oauth\Helper\Oauth', array(), array(), '', false),
            $this->_tokenProviderMock
        );
        $this->_emptyConsumerMock = $this->getMockBuilder(
            'Magento\Integration\Model\Integration'
        )->disableOriginalConstructor()->setMethods(
            array('getData', 'load', 'getId', 'save', 'delete', '__wakeup')
        )->getMock();
        $this->_emptyConsumerMock->expects($this->any())->method('getId')->will($this->returnValue(null));
    }

    public function testDelete()
    {
        $this->_consumerMock->expects(
            $this->once()
        )->method(
            'getId'
        )->will(
            $this->returnValue(self::VALUE_CONSUMER_ID)
        );
        $this->_consumerMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            self::VALUE_CONSUMER_ID
        )->will(
            $this->returnValue($this->_consumerMock)
        );
        $this->_consumerMock->expects($this->once())->method('delete')->will($this->returnValue($this->_consumerMock));
        $this->_consumerMock->expects($this->any())->method('getData')->will($this->returnValue($this->_consumerData));
        $consumerData = $this->_service->deleteConsumer(self::VALUE_CONSUMER_ID);
        $this->assertEquals($this->_consumerData['entity_id'], $consumerData['entity_id']);
    }

    /**
     * @expectedException \Magento\Integration\Exception
     * @expectedExceptionMessage Consumer with ID '1' does not exist.
     */
    public function testDeleteException()
    {
        $this->_consumerMock->expects($this->any())->method('getId')->will($this->returnValue(null));
        $this->_consumerMock->expects($this->once())->method('load')->will($this->returnSelf());
        $this->_consumerMock->expects($this->never())->method('delete');
        $this->_service->deleteConsumer(self::VALUE_CONSUMER_ID);
    }

    public function testCreateAccessTokenAndClearExisting()
    {

        $this->_consumerMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            self::VALUE_CONSUMER_ID
        )->will(
            $this->returnValue($this->_consumerMock)
        );

        $this->_tokenProviderMock->expects(
            $this->any()
        )->method(
            'getIntegrationTokenByConsumerId'
        )->will(
            $this->returnValue($this->_tokenMock)
        );

        $this->_tokenProviderMock->expects($this->any())->method('createRequestToken')->with($this->_consumerMock);

        $this->_tokenProviderMock->expects($this->any())->method('getAccessToken')->with($this->_consumerMock);

        $this->_tokenFactoryMock->expects($this->any())->method('create')->will($this->returnValue($this->_tokenMock));

        $this->_tokenMock->expects($this->once())->method('delete');

        $this->_tokenMock->expects($this->once())->method('createVerifierToken')->with(self::VALUE_CONSUMER_ID);

        $this->_tokenProviderMock->expects($this->once())->method('createRequestToken')->with($this->_consumerMock);

        $this->_tokenProviderMock->expects($this->once())->method('getAccessToken')->with($this->_consumerMock);

        $this->assertTrue($this->_service->createAccessToken(self::VALUE_CONSUMER_ID, true));
    }

    public function testCreateAccessTokenWithoutClearingExisting()
    {
        $this->_consumerMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            self::VALUE_CONSUMER_ID
        )->will(
            $this->returnValue($this->_consumerMock)
        );

        $this->_tokenProviderMock->expects(
            $this->any()
        )->method(
            'getIntegrationTokenByConsumerId'
        )->will(
            $this->returnValue($this->_tokenMock)
        );

        $this->_tokenMock->expects($this->never())->method('delete');

        $this->assertFalse($this->_service->createAccessToken(self::VALUE_CONSUMER_ID, false));
    }

    public function testCreateAccessTokenInvalidConsumerId()
    {
        $this->_consumerMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            0
        )->will(
            $this->returnValue($this->_consumerMock)
        );

        $this->_tokenProviderMock->expects(
            $this->any()
        )->method(
            'getIntegrationTokenByConsumerId'
        )->will(
            $this->throwException(
                new \Magento\Framework\Oauth\Exception(
                    'A token with consumer ID 0 does not exist'
                )
            )
        );

        $this->_tokenMock->expects($this->never())->method('delete');

        $this->_tokenFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_tokenMock)
        );

        $this->_tokenMock->expects($this->once())->method('createVerifierToken');

        $this->_tokenProviderMock->expects($this->once())->method('createRequestToken');

        $this->_tokenProviderMock->expects($this->once())->method('getAccessToken');

        $this->assertTrue($this->_service->createAccessToken(0, false));
    }

    public function testLoadConsumer()
    {
        $this->_consumerMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            self::VALUE_CONSUMER_ID
        )->will(
            $this->returnValue($this->_consumerMock)
        );
        $this->_consumerMock->expects($this->any())->method('getData')->will($this->returnValue($this->_consumerData));
        $consumer = $this->_service->loadConsumer(self::VALUE_CONSUMER_ID);
        $consumerData = $consumer->getData();
        $this->assertEquals($this->_consumerData['entity_id'], $consumerData['entity_id']);
    }

    /**
     * @expectedException \Magento\Framework\Oauth\Exception
     * @expectedExceptionMessage Unexpected error. Unable to load oAuth consumer account.
     */
    public function testLoadConsumerException()
    {
        $this->_consumerMock->expects(
            $this->once()
        )->method(
            'load'
        )->will(
            $this->throwException(
                new \Magento\Framework\Oauth\Exception('Unexpected error. Unable to load oAuth consumer account.')
            )
        );
        $this->_service->loadConsumer(self::VALUE_CONSUMER_ID);
    }

    public function testLoadConsumerByKey()
    {
        $this->_consumerMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            self::VALUE_CONSUMER_KEY,
            'key'
        )->will(
            $this->returnValue($this->_consumerMock)
        );
        $this->_consumerMock->expects($this->any())->method('getData')->will($this->returnValue($this->_consumerData));
        $consumer = $this->_service->loadConsumerByKey(self::VALUE_CONSUMER_KEY);
        $consumerData = $consumer->getData();
        $this->assertEquals($this->_consumerData['key'], $consumerData['key']);
    }

    /**
     * @expectedException \Magento\Framework\Oauth\Exception
     * @expectedExceptionMessage Unexpected error. Unable to load oAuth consumer account.
     */
    public function testLoadConsumerByKeyException()
    {
        $this->_consumerMock->expects(
            $this->once()
        )->method(
            'load'
        )->will(
            $this->throwException(
                new \Magento\Framework\Oauth\Exception('Unexpected error. Unable to load oAuth consumer account.')
            )
        );
        $this->_service->loadConsumerByKey(self::VALUE_CONSUMER_KEY);
    }

    public function testDeleteToken()
    {
        $this->_consumerMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            self::VALUE_CONSUMER_ID
        )->will(
            $this->returnValue($this->_consumerMock)
        );

        $this->_tokenProviderMock->expects(
            $this->any()
        )->method(
            'getIntegrationTokenByConsumerId'
        )->will(
            $this->returnValue($this->_tokenMock)
        );

        $this->_tokenMock->expects($this->once())->method('delete');

        $this->assertTrue($this->_service->deleteIntegrationToken(self::VALUE_CONSUMER_ID));
    }

    public function testDeleteTokenNegative()
    {
        $this->_consumerMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            self::VALUE_CONSUMER_ID
        )->will(
            $this->returnValue($this->_consumerMock)
        );

        $this->_tokenProviderMock->expects(
            $this->any()
        )->method(
            'getIntegrationTokenByConsumerId'
        )->will(
            $this->returnValue($this->_tokenMock)
        );

        $this->_tokenMock->expects($this->never())->method('delete');

        $this->assertFalse($this->_service->deleteIntegrationToken(null));
    }

    public function testGetAccessTokenNoAccess()
    {
        $this->_consumerMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            self::VALUE_CONSUMER_ID
        )->will(
            $this->returnValue($this->_consumerMock)
        );

        $this->_tokenProviderMock->expects(
            $this->any()
        )->method(
            'getIntegrationTokenByConsumerId'
        )->will(
            $this->returnValue($this->_tokenMock)
        );

        $this->assertFalse($this->_service->getAccessToken(self::VALUE_CONSUMER_ID), false);
    }

    public function testGetAccessSuccess()
    {
        $this->_consumerMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            self::VALUE_CONSUMER_ID
        )->will(
            $this->returnValue($this->_consumerMock)
        );

        $this->_tokenMock->expects($this->once())->method('getType')->will($this->returnValue(Token::TYPE_ACCESS));

        $this->_tokenProviderMock->expects(
            $this->any()
        )->method(
            'getIntegrationTokenByConsumerId'
        )->will(
            $this->returnValue($this->_tokenMock)
        );

        $this->assertEquals($this->_service->getAccessToken(self::VALUE_CONSUMER_ID), $this->_tokenMock);
    }
}
