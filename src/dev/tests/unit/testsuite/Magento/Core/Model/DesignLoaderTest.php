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
namespace Magento\Core\Model;

class DesignLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\DesignLoader
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_areaListMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $appState;

    protected function setUp()
    {
        $this->_areaListMock = $this->getMock('\Magento\Framework\App\AreaList', array(), array(), '', false);
        $this->_requestMock = $this->getMock('Magento\Framework\App\Request\Http', array(), array(), '', false);
        $this->appState = $this->getMock('Magento\Framework\App\State', array(), array(), '', false);
        $this->_model = new \Magento\Framework\View\DesignLoader(
            $this->_requestMock,
            $this->_areaListMock,
            $this->appState
        );
    }

    public function testLoad()
    {
        $area = $this->getMock('Magento\Framework\App\Area', array(), array(), '', false);
        $this->appState->expects($this->once())->method('getAreaCode')->will($this->returnValue('area'));
        $this->_areaListMock->expects($this->once())->method('getArea')->with('area')->will($this->returnValue($area));
        $area->expects($this->at(0))->method('load')
            ->with(\Magento\Framework\App\Area::PART_DESIGN)->will($this->returnValue($area));
        $area->expects($this->at(1))->method('load')
            ->with(\Magento\Framework\App\Area::PART_TRANSLATE)->will($this->returnValue($area));
        $this->_model->load($this->_requestMock);
    }
}
