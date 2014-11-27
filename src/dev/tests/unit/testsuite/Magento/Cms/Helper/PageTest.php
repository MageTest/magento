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
namespace Magento\Cms\Helper;

/**
 * @covers \Magento\Cms\Helper\Page
 */
class PageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Cms\Helper\Page
     */
    protected $this;

    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\App\Action\Action|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $actionMock;

    /**
     * @var \Magento\Cms\Model\PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageFactoryMock;

    /**
     * @var \Magento\Cms\Model\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageMock;

    /**
     * @var \Magento\Framework\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeDateMock;

    /**
     * @var \Magento\Framework\View\DesignInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $designMock;

    /**
     * @var \Magento\Framework\View\Page\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageConfigMock;

    /**
     * @var \Magento\Framework\App\ViewInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewMock;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $escaperMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \Magento\Framework\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageMock;

    /**
     * @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    /**
     * @var \Magento\Framework\View\Layout\ProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutProcessorMock;

    /**
     * @var \Magento\Framework\View\Element\BlockInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $blockMock;

    /**
     * @var \Magento\Framework\View\Element\Messages|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messagesBlockMock;

    /**
     * @var \Magento\Framework\Message\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageCollectionMock;

    protected function setUp()
    {
        $this->actionMock = $this->getMockBuilder('Magento\Framework\App\Action\Action')
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageFactoryMock = $this->getMockBuilder('Magento\Cms\Model\PageFactory')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'create'
                ]
            )
            ->getMock();
        $this->pageMock = $this->getMockBuilder('Magento\Cms\Model\Page')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getId',
                    'setStoreId',
                    'load',
                    'getCustomThemeFrom',
                    'getCustomThemeTo',
                    'getCustomTheme',
                    'getPageLayout',
                    'getIdentifier',
                    'getCustomPageLayout',
                    'getCustomLayoutUpdateXml',
                    'getLayoutUpdateXml',
                    'getContentHeading'
                ]
            )
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder('Magento\Framework\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->localeDateMock = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime\TimezoneInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->designMock = $this->getMockBuilder('Magento\Framework\View\DesignInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfigMock = $this->getMockBuilder('Magento\Framework\View\Page\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->viewMock = $this->getMockBuilder('Magento\Framework\App\ViewInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->escaperMock = $this->getMockBuilder('Magento\Framework\Escaper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventManagerMock = $this->getMockBuilder('Magento\Framework\Event\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlBuilderMock = $this->getMockBuilder('Magento\Framework\UrlInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeMock = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageMock = $this->getMockBuilder('Magento\Framework\View\Result\Page')
            ->disableOriginalConstructor()
            ->getMock();
        $this->layoutMock = $this->getMockBuilder('Magento\Framework\View\LayoutInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->layoutProcessorMock = $this->getMockBuilder('Magento\Framework\View\Layout\ProcessorInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->blockMock = $this->getMockBuilder('Magento\Framework\View\Element\BlockInterface')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setContentHeading',
                    'toHtml'
                ]
            )
            ->getMock();
        $this->messagesBlockMock = $this->getMockBuilder('Magento\Framework\View\Element\Messages')
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder('Magento\Framework\Message\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageCollectionMock = $this->getMockBuilder('Magento\Framework\Message\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->context = $objectManager->getObject(
            'Magento\Framework\App\Helper\Context',
            [
                'eventManager' => $this->eventManagerMock,
                'urlBuilder' => $this->urlBuilderMock
            ]
        );
        $this->this = $objectManager->getObject(
            'Magento\Cms\Helper\Page',
            [
                'context' => $this->context,
                'pageFactory' => $this->pageFactoryMock,
                'page' => $this->pageMock,
                'storeManager' => $this->storeManagerMock,
                'localeDate' => $this->localeDateMock,
                'design' => $this->designMock,
                'pageConfig' => $this->pageConfigMock,
                'view' => $this->viewMock,
                'escaper' => $this->escaperMock,
                'messageManager' => $this->messageManagerMock
            ]
        );
    }

    /**
     * @covers \Magento\Cms\Helper\Page::renderPageExtended
     * @param integer|null $pageId
     * @param integer|null $internalPageId
     * @param integer $pageLoadResultIndex
     * @param string $customPageLayout
     * @param string $handle
     * @param string $customLayoutUpdateXml
     * @param string $layoutUpdate
     * @param boolean $expectedResult
     *
     * @dataProvider renderPageExtendedDataProvider
     */
    public function testRenderPageExtended(
        $pageId,
        $internalPageId,
        $pageLoadResultIndex,
        $customPageLayout,
        $handle,
        $customLayoutUpdateXml,
        $layoutUpdate,
        $expectedResult
    ) {
        $storeId = 321;
        $customThemeFrom = 'customThemeFrom';
        $customThemeTo = 'customThemeTo';
        $isScopeDateInInterval = true;
        $customTheme = 'customTheme';
        $pageLayout = 'pageLayout';
        $pageIdentifier = 111;
        $layoutUpdateXml = 'layoutUpdateXml';
        $contentHeading = 'contentHeading';
        $escapedContentHeading = 'escapedContentHeading';
        $defaultGroup = 'defaultGroup';
        $pageLoadResultCollection = [
            null,
            $this->pageMock
        ];

        $this->pageMock->expects($this->any())
            ->method('getId')
            ->willReturn($internalPageId);
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn($storeId);
        $this->pageMock->expects($this->any())
            ->method('setStoreId')
            ->with($storeId)
            ->willReturnSelf();
        $this->pageMock->expects($this->any())
            ->method('load')
            ->with($pageId)
            ->willReturn($pageLoadResultCollection[$pageLoadResultIndex]);
        $this->pageMock->expects($this->any())
            ->method('getCustomThemeFrom')
            ->willReturn($customThemeFrom);
        $this->pageMock->expects($this->any())
            ->method('getCustomThemeTo')
            ->willReturn($customThemeTo);
        $this->localeDateMock->expects($this->any())
            ->method('isScopeDateInInterval')
            ->with(null, $customThemeFrom, $customThemeTo)
            ->willReturn($isScopeDateInInterval);
        $this->pageMock->expects($this->any())
            ->method('getCustomTheme')
            ->willReturn($customTheme);
        $this->designMock->expects($this->any())
            ->method('setDesignTheme')
            ->with($customTheme)
            ->willReturnSelf();
        $this->pageMock->expects($this->any())
            ->method('getPageLayout')
            ->willReturn($pageLayout);
        $this->pageMock->expects($this->any())
            ->method('getCustomPageLayout')
            ->willReturn($customPageLayout);
        $this->resultPageMock->expects($this->any())
            ->method('getConfig')
            ->willReturn($this->pageConfigMock);
        $this->pageConfigMock->expects($this->any())
            ->method('setPageLayout')
            ->with($handle)
            ->willReturnSelf();
        $this->viewMock->expects($this->any())
            ->method('getPage')
            ->willReturn($this->resultPageMock);
        $this->resultPageMock->expects($this->any())
            ->method('initLayout')
            ->willReturnSelf();
        $this->resultPageMock->expects($this->any())
            ->method('getLayout')
            ->willReturn($this->layoutMock);
        $this->layoutMock->expects($this->any())
            ->method('getUpdate')
            ->willReturn($this->layoutProcessorMock);
        $this->layoutProcessorMock->expects($this->any())
            ->method('addHandle')
            ->with('cms_page_view')
            ->willReturnSelf();
        $this->pageMock->expects($this->any())
            ->method('getIdentifier')
            ->willReturn($pageIdentifier);
        $this->viewMock->expects($this->any())
            ->method('addPageLayoutHandles')
            ->with(['id' => $pageIdentifier])
            ->willReturn(true);
        $this->eventManagerMock->expects($this->any())
            ->method('dispatch')
            ->with(
                'cms_page_render',
                [
                    'page' => $this->pageMock,
                    'controller_action' => $this->actionMock
                ]
            );
        $this->viewMock->expects($this->any())
            ->method('loadLayoutUpdates')
            ->willReturnSelf();
        $this->pageMock->expects($this->any())
            ->method('getCustomLayoutUpdateXml')
            ->willReturn($customLayoutUpdateXml);
        $this->pageMock->expects($this->any())
            ->method('getLayoutUpdateXml')
            ->willReturn($layoutUpdateXml);
        $this->layoutProcessorMock->expects($this->any())
            ->method('addUpdate')
            ->with($layoutUpdate)
            ->willReturnSelf();
        $this->viewMock->expects($this->any())
            ->method('generateLayoutXml')
            ->willReturnSelf();
        $this->viewMock->expects($this->any())
            ->method('generateLayoutBlocks')
            ->willReturnSelf();
        $this->layoutMock->expects($this->any())
            ->method('getBlock')
            ->with('page_content_heading')
            ->willReturn($this->blockMock);
        $this->pageMock->expects($this->any())
            ->method('getContentHeading')
            ->willReturn($contentHeading);
        $this->escaperMock->expects($this->any())
            ->method('escapeHtml')
            ->with($contentHeading)
            ->willReturn($escapedContentHeading);
        $this->blockMock->expects($this->any())
            ->method('setContentHeading')
            ->with($escapedContentHeading)
            ->willReturnSelf();
        $this->layoutMock->expects($this->any())
            ->method('getMessagesBlock')
            ->willReturn($this->messagesBlockMock);
        $this->messageManagerMock->expects($this->any())
            ->method('getDefaultGroup')
            ->willReturn($defaultGroup);
        $this->messagesBlockMock->expects($this->any())
            ->method('addStorageType')
            ->with($defaultGroup);
        $this->messageManagerMock->expects($this->any())
            ->method('getMessages')
            ->with(true)
            ->willReturn($this->messageCollectionMock);
        $this->messagesBlockMock->expects($this->any())
            ->method('addMessages')
            ->with($this->messageCollectionMock)
            ->willReturnSelf();
        $this->viewMock->expects($this->any())
            ->method('renderLayout')
            ->willReturnSelf();

        $this->assertEquals(
            $expectedResult,
            $this->this->renderPageExtended($this->actionMock, $pageId)
        );
    }

    public function renderPageExtendedDataProvider()
    {
        return [
            'ids NOT EQUAL BUT page->load() NOT SUCCESSFUL' => [
                'pageId' => 123,
                'internalPageId' => 234,
                'pageLoadResultIndex' => 0,
                'customPageLayout' => 'DOES NOT MATTER',
                'handle' => 'DOES NOT MATTER',
                'customLayoutUpdateXml' => 'DOES NOT MATTER',
                'layoutUpdate' => 'DOES NOT MATTER',
                'expectedResult' => false
            ],
            'page->load IS SUCCESSFUL BUT internalPageId IS EMPTY' => [
                'pageId' => 123,
                'internalPageId' => null,
                'pageLoadResultIndex' => 1,
                'customPageLayout' => 'DOES NOT MATTER',
                'handle' => 'DOES NOT MATTER',
                'customLayoutUpdateXml' => 'DOES NOT MATTER',
                'layoutUpdate' => 'DOES NOT MATTER',
                'expectedResult' => false
            ],
            'getPageLayout() AND getLayoutUpdateXml() ARE USED' => [
                'pageId' => 123,
                'internalPageId' => 234,
                'pageLoadResultIndex' => 1,
                'customPageLayout' => 'empty',
                'handle' => 'pageLayout',
                'customLayoutUpdateXml' => '',
                'layoutUpdate' => 'layoutUpdateXml',
                'expectedResult' => true
            ],
            'getCustomPageLayout() AND getCustomLayoutUpdateXml() ARE USED' => [
                'pageId' => 123,
                'internalPageId' => 234,
                'pageLoadResultIndex' => 1,
                'customPageLayout' => 'customPageLayout',
                'handle' => 'customPageLayout',
                'customLayoutUpdateXml' => 'customLayoutUpdateXml',
                'layoutUpdate' => 'customLayoutUpdateXml',
                'expectedResult' => true
            ]
        ];
    }

    /**
     * @covers \Magento\Cms\Helper\Page::getPageUrl
     * @param integer|null $pageId
     * @param integer|null $internalPageId
     * @param integer $pageLoadResultIndex
     * @param string|null $expectedResult
     *
     * @dataProvider getPageUrlDataProvider
     */
    public function testGetPageUrl(
        $pageId,
        $internalPageId,
        $pageLoadResultIndex,
        $expectedResult
    ) {
        $storeId = 321;
        $pageIdentifier = 111;
        $url = '/some/url';
        $pageLoadResultCollection = [
            null,
            $this->pageMock
        ];

        $this->pageFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->pageMock);
        $this->pageMock->expects($this->any())
            ->method('getId')
            ->willReturn($internalPageId);
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn($storeId);
        $this->pageMock->expects($this->any())
            ->method('setStoreId')
            ->with($storeId)
            ->willReturnSelf();
        $this->pageMock->expects($this->any())
            ->method('load')
            ->with($pageId)
            ->willReturn($pageLoadResultCollection[$pageLoadResultIndex]);
        $this->pageMock->expects($this->any())
            ->method('getIdentifier')
            ->willReturn($pageIdentifier);
        $this->urlBuilderMock->expects($this->any())
            ->method('getUrl')
            ->with(null, ['_direct' => $pageIdentifier])
            ->willReturn($url);

        $this->assertEquals($expectedResult, $this->this->getPageUrl($pageId));
    }

    public function getPageUrlDataProvider()
    {
        return [
            'ids NOT EQUAL BUT page->load() NOT SUCCESSFUL' => [
                'pageId' => 123,
                'internalPageId' => 234,
                'pageLoadResultIndex' => 0,
                'expectedResult' => null
            ],
            'page->load() IS SUCCESSFUL BUT internalId IS EMPTY' => [
                'pageId' => 123,
                'internalPageId' => null,
                'pageLoadResultIndex' => 1,
                'expectedResult' => null
            ],
            'SUCCESS' => [
                'pageId' => 123,
                'internalPageId' => 234,
                'pageLoadResultIndex' => 1,
                'expectedResult' => '/some/url'
            ]
        ];
    }
}
