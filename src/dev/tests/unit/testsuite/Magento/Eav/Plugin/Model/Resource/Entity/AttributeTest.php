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

namespace Magento\Eav\Plugin\Model\Resource\Entity;

use Magento\TestFramework\Helper\ObjectManager;

class AttributeTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\App\CacheInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $cache;

    /** @var \Magento\Framework\App\Cache\StateInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $cacheState;

    /** @var \Magento\Eav\Model\Resource\Entity\Attribute|\PHPUnit_Framework_MockObject_MockObject */
    protected $subject;

    protected function setUp()
    {
        $this->cache = $this->getMock('Magento\Framework\App\CacheInterface');
        $this->cacheState = $this->getMock('Magento\Framework\App\Cache\StateInterface');
        $this->subject = $this->getMock('Magento\Eav\Model\Resource\Entity\Attribute', [], [], '', false);
    }

    public function testGetStoreLabelsByAttributeIdOnCacheDisabled()
    {
        $this->cache->expects($this->never())->method('load');

        $this->assertEquals(
            'attributeId',
            $this->getAttribute(false)->aroundGetStoreLabelsByAttributeId(
                $this->subject,
                $this->mockPluginProceed('attributeId'),
               'attributeId'
            )
        );
    }

    public function testGetStoreLabelsByAttributeIdFromCache()
    {
        $attributeId = 1;
        $attributes = ['k' => 'v'];
        $cacheId = \Magento\Eav\Plugin\Model\Resource\Entity\Attribute::STORE_LABEL_ATTRIBUTE . $attributeId;
        $this->cache->expects($this->any())->method('load')->with($cacheId)->willReturn(serialize($attributes));

        $this->assertEquals(
            $attributes,
            $this->getAttribute(true)->aroundGetStoreLabelsByAttributeId(
                $this->subject,
                $this->mockPluginProceed(),
                $attributeId
            )
        );
    }

    public function testGetStoreLabelsByAttributeIdWithCacheSave()
    {
        $attributeId = 1;
        $cacheId = \Magento\Eav\Plugin\Model\Resource\Entity\Attribute::STORE_LABEL_ATTRIBUTE . $attributeId;
        $this->cache->expects($this->any())->method('load')->with($cacheId)->willReturn(false);
        $this->cache->expects($this->any())->method('save')->with(
            serialize([$attributeId]),
            $cacheId,
            [
                \Magento\Eav\Model\Cache\Type::CACHE_TAG,
                \Magento\Eav\Model\Entity\Attribute::CACHE_TAG
            ]
        );

        $this->assertEquals(
            [$attributeId],
            $this->getAttribute(true)->aroundGetStoreLabelsByAttributeId(
                $this->subject,
                $this->mockPluginProceed([$attributeId]),
                $attributeId
            )
        );
    }

    /**
     * @param bool $cacheEnabledFlag
     * @return \Magento\Eav\Plugin\Model\Resource\Entity\Attribute
     */
    protected function getAttribute($cacheEnabledFlag)
    {
        $this->cacheState->expects($this->any())->method('isEnabled')
            ->with(\Magento\Eav\Model\Cache\Type::TYPE_IDENTIFIER)->willReturn($cacheEnabledFlag);
        return (new ObjectManager($this))->getObject(
            'Magento\Eav\Plugin\Model\Resource\Entity\Attribute',
            [
                'cache' => $this->cache,
                'cacheState' => $this->cacheState
            ]
        );
    }

    /**
     * @param mixed $returnValue
     * @return callable
     */
    protected function mockPluginProceed($returnValue = null)
    {
        return function () use ($returnValue) {
            return $returnValue;
        };
    }
}
