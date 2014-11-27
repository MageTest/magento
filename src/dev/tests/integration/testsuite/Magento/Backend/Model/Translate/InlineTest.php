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
namespace Magento\Backend\Model\Translate;

/**
 * @magentoAppArea adminhtml
 */
class InlineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Translate\InlineInterface
     */
    protected $_translateInline;

    protected function setUp()
    {
        $this->_translateInline = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\Translate\InlineInterface'
        );
    }

    /**
     * @magentoAdminConfigFixture dev/translate_inline/active_admin 1
     * @covers \Magento\Framework\Translate\Inline::getAjaxUrl
     */
    public function testAjaxUrl()
    {
        $body = '<html><body>some body</body></html>';
        /** @var \Magento\Backend\Model\UrlInterface $url */
        $url = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\UrlInterface');
        $url->getUrl(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE . '/ajax/translate');
        $this->_translateInline->processResponseBody($body, true);
        $this->assertContains(
            $url->getUrl(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE . '/ajax/translate'),
            $body
        );
    }
}
