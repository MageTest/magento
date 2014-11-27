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
namespace Magento\Captcha\Model;

class CaptchaFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**@var \PHPUnit_Framework_MockObject_MockObject */
    protected $_objectManagerMock;

    /** @var \Magento\Captcha\Model\CaptchaFactory */
    protected $_model;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->_model = new \Magento\Captcha\Model\CaptchaFactory($this->_objectManagerMock);
    }

    public function testCreatePositive()
    {
        $captchaType = 'default';

        $defaultCaptchaMock = $this->getMock('Magento\Captcha\Model\DefaultModel', array(), array(), '', false);

        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $this->equalTo('Magento\Captcha\Model\\' . ucfirst($captchaType))
        )->will(
            $this->returnValue($defaultCaptchaMock)
        );

        $this->assertEquals($defaultCaptchaMock, $this->_model->create($captchaType, 'form_id'));
    }

    public function testCreateNegative()
    {
        $captchaType = 'wrong_instance';

        $defaultCaptchaMock = $this->getMock('stdClass');

        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $this->equalTo('Magento\Captcha\Model\\' . ucfirst($captchaType))
        )->will(
            $this->returnValue($defaultCaptchaMock)
        );

        $this->setExpectedException(
            'InvalidArgumentException',
            'Magento\Captcha\Model\\' . ucfirst(
                $captchaType
            ) . ' does not implement \Magento\Captcha\Model\ModelInterface'
        );

        $this->assertEquals($defaultCaptchaMock, $this->_model->create($captchaType, 'form_id'));
    }
}
