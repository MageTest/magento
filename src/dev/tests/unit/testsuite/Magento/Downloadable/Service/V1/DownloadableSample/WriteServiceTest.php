<?php
/**
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
 * @license     {license_sample}
 */
namespace Magento\Downloadable\Service\V1\DownloadableSample;

class WriteServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $repositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contentValidatorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contentUploaderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $jsonEncoderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sampleFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var WriteService
     */
    protected $service;

    protected function setUp()
    {
        $this->productMock = $this->getMock(
            '\Magento\Catalog\Model\Product',
            array('__wakeup', 'getTypeId', 'setDownloadableData', 'save', 'getId', 'getStoreId'),
            array(),
            '',
            false
        );
        $this->repositoryMock = $this->getMock('\Magento\Catalog\Model\ProductRepository', array(), array(), '', false);
        $this->contentValidatorMock = $this->getMock(
            '\Magento\Downloadable\Service\V1\DownloadableSample\Data\DownloadableSampleContentValidator',
            array(),
            array(),
            '',
            false
        );
        $this->contentUploaderMock = $this->getMock(
            '\Magento\Downloadable\Service\V1\Data\FileContentUploaderInterface'
        );
        $this->jsonEncoderMock = $this->getMock(
            '\Magento\Framework\Json\EncoderInterface'
        );
        $this->sampleFactoryMock = $this->getMock(
            '\Magento\Downloadable\Model\SampleFactory',
            array('create'),
            array(),
            '',
            false
        );

        $this->service = new WriteService(
            $this->repositoryMock,
            $this->contentValidatorMock,
            $this->contentUploaderMock,
            $this->jsonEncoderMock,
            $this->sampleFactoryMock
        );
    }

    /**
     * @param array $sampleContentData
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getSampleContentMock(array $sampleContentData)
    {
        $contentMock = $this->getMock(
            '\Magento\Downloadable\Service\V1\DownloadableSample\Data\DownloadableSampleContent',
            array(),
            array(),
            '',
            false
        );


        $contentMock->expects($this->any())->method('getTitle')->will($this->returnValue(
            $sampleContentData['title']
        ));
        $contentMock->expects($this->any())->method('getSortOrder')->will($this->returnValue(
            $sampleContentData['sort_order']
        ));

        if (isset($sampleContentData['sample_type'])) {
            $contentMock->expects($this->any())->method('getSampleType')->will($this->returnValue(
                $sampleContentData['sample_type']
            ));
        }
        if (isset($sampleContentData['sample_url'])) {
            $contentMock->expects($this->any())->method('getSampleUrl')->will($this->returnValue(
                $sampleContentData['sample_url']
            ));
        }
        return $contentMock;
    }

    public function testCreate()
    {
        $productSku = 'simple';
        $sampleContentData = array(
            'title' => 'Title',
            'sort_order' => 1,
            'sample_type' => 'url',
            'sample_url' => 'http://example.com/'
        );
        $this->repositoryMock->expects($this->any())->method('get')->with($productSku, true)
            ->will($this->returnValue($this->productMock));
        $this->productMock->expects($this->any())->method('getTypeId')->will($this->returnValue('downloadable'));
        $sampleContentMock = $this->getSampleContentMock($sampleContentData);
        $this->contentValidatorMock->expects($this->any())->method('isValid')->with($sampleContentMock)
            ->will($this->returnValue(true));

        $this->productMock->expects($this->once())->method('setDownloadableData')->with(array(
            'sample' => array(
                array(
                    'sample_id' => 0,
                    'is_delete' => 0,
                    'type' => $sampleContentData['sample_type'],
                    'sort_order' => $sampleContentData['sort_order'],
                    'title' => $sampleContentData['title'],
                    'sample_url' => $sampleContentData['sample_url'],
                ),
            ),
        ));
        $this->productMock->expects($this->once())->method('save');
        $this->service->create($productSku, $sampleContentMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Sample title cannot be empty.
     */
    public function testCreateThrowsExceptionIfTitleIsEmpty()
    {
        $productSku = 'simple';
        $sampleContentData = array(
            'title' => '',
            'sort_order' => 1,
            'sample_type' => 'url',
            'sample_url' => 'http://example.com/'
        );

        $this->repositoryMock->expects($this->any())->method('get')->with($productSku, true)
            ->will($this->returnValue($this->productMock));
        $this->productMock->expects($this->any())->method('getTypeId')->will($this->returnValue('downloadable'));
        $sampleContentMock = $this->getSampleContentMock($sampleContentData);
        $this->contentValidatorMock->expects($this->any())->method('isValid')->with($sampleContentMock)
            ->will($this->returnValue(true));

        $this->productMock->expects($this->never())->method('save');

        $this->service->create($productSku, $sampleContentMock);

    }

    public function testUpdate()
    {
        $sampleId = 1;
        $productId = 1;
        $productSku = 'simple';
        $sampleContentData = array(
            'title' => 'Updated Title',
            'sort_order' => 1,
        );
        $this->repositoryMock->expects($this->any())->method('get')->with($productSku, true)
            ->will($this->returnValue($this->productMock));
        $this->productMock->expects($this->any())->method('getId')->will($this->returnValue($productId));
        $sampleMock = $this->getMock(
            '\Magento\Downloadable\Model\Sample',
            array('__wakeup', 'setTitle', 'setSortOrder', 'getId', 'setProductId', 'setStoreId',
                'load', 'save', 'getProductId'),
            array(),
            '',
            false
        );
        $this->sampleFactoryMock->expects($this->once())->method('create')->will($this->returnValue($sampleMock));
        $sampleContentMock = $this->getSampleContentMock($sampleContentData);
        $this->contentValidatorMock->expects($this->any())->method('isValid')->with($sampleContentMock)
            ->will($this->returnValue(true));

        $sampleMock->expects($this->any())->method('getId')->will($this->returnValue($sampleId));
        $sampleMock->expects($this->any())->method('getProductId')->will($this->returnValue($productId));
        $sampleMock->expects($this->once())->method('load')->with($sampleId)->will($this->returnSelf());
        $sampleMock->expects($this->once())->method('setTitle')->with($sampleContentData['title'])
            ->will($this->returnSelf());
        $sampleMock->expects($this->once())->method('setSortOrder')->with($sampleContentData['sort_order'])
            ->will($this->returnSelf());
        $sampleMock->expects($this->once())->method('setProductId')->with($productId)
            ->will($this->returnSelf());
        $sampleMock->expects($this->once())->method('setStoreId')->will($this->returnSelf());
        $sampleMock->expects($this->once())->method('save')->will($this->returnSelf());

        $this->assertTrue($this->service->update($productSku, $sampleId, $sampleContentMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Sample title cannot be empty.
     */
    public function testUpdateThrowsExceptionIfTitleIsEmptyAndScopeIsGlobal()
    {
        $sampleId = 1;
        $productSku = 'simple';
        $productId = 1;
        $sampleContentData = array(
            'title' => '',
            'sort_order' => 1,
        );
        $this->repositoryMock->expects($this->any())->method('get')->with($productSku, true)
            ->will($this->returnValue($this->productMock));
        $this->productMock->expects($this->any())->method('getId')->will($this->returnValue($productId));
        $sampleMock = $this->getMock(
            '\Magento\Downloadable\Model\Sample',
            array('__wakeup', 'getId', 'load', 'save', 'getProductId'),
            array(),
            '',
            false
        );
        $sampleMock->expects($this->any())->method('getId')->will($this->returnValue($sampleId));
        $sampleMock->expects($this->once())->method('load')->with($sampleId)->will($this->returnSelf());
        $sampleMock->expects($this->any())->method('getProductId')->will($this->returnValue($productId));
        $this->sampleFactoryMock->expects($this->once())->method('create')->will($this->returnValue($sampleMock));
        $sampleContentMock = $this->getSampleContentMock($sampleContentData);
        $this->contentValidatorMock->expects($this->any())->method('isValid')->with($sampleContentMock)
            ->will($this->returnValue(true));

        $sampleMock->expects($this->never())->method('save');

        $this->service->update($productSku, $sampleId, $sampleContentMock, true);
    }

    public function testDelete()
    {
        $sampleId = 1;
        $sampleMock = $this->getMock(
            '\Magento\Downloadable\Model\Sample',
            array(),
            array(),
            '',
            false
        );
        $this->sampleFactoryMock->expects($this->once())->method('create')->will($this->returnValue($sampleMock));
        $sampleMock->expects($this->once())->method('load')->with($sampleId)->will($this->returnSelf());
        $sampleMock->expects($this->any())->method('getId')->will($this->returnValue($sampleId));
        $sampleMock->expects($this->once())->method('delete');

        $this->assertTrue($this->service->delete($sampleId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage There is no downloadable sample with provided ID.
     */
    public function testDeleteThrowsExceptionIfSampleIdIsNotValid()
    {
        $sampleId = 1;
        $sampleMock = $this->getMock(
            '\Magento\Downloadable\Model\Sample',
            array(),
            array(),
            '',
            false
        );
        $this->sampleFactoryMock->expects($this->once())->method('create')->will($this->returnValue($sampleMock));
        $sampleMock->expects($this->once())->method('load')->with($sampleId)->will($this->returnSelf());
        $sampleMock->expects($this->once())->method('getId');
        $sampleMock->expects($this->never())->method('delete');

        $this->service->delete($sampleId);
    }
}
