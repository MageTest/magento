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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\CatalogRule\Model\Indexer;

class AbstractIndexerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\Indexer\IndexBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexBuilder;

    /**
     * @var \Magento\CatalogRule\Model\Indexer\AbstractIndexer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexer;

    protected function setUp()
    {
        $this->indexBuilder = $this->getMock('Magento\CatalogRule\Model\Indexer\IndexBuilder', [], [], '', false);

        $this->indexer = $this->getMockForAbstractClass(
            'Magento\CatalogRule\Model\Indexer\AbstractIndexer',
            [$this->indexBuilder]
        );
    }

    public function testExecute()
    {
        $ids = [1, 2, 5];
        $this->indexer->expects($this->once())->method('doExecuteList')->with($ids);

        $this->indexer->execute($ids);
    }

    public function testExecuteFull()
    {
        $this->indexBuilder->expects($this->once())->method('reindexFull');

        $this->indexer->executeFull();
    }

    /**
     * @expectedException \Magento\CatalogRule\CatalogRuleException
     * @expectedExceptionMessage Could not rebuild index for empty products array
     */
    public function testExecuteListWithEmptyIds()
    {
        $this->indexer->executeList([]);
    }

    public function testExecuteList()
    {
        $ids = [1, 2, 5];
        $this->indexer->expects($this->once())->method('doExecuteList')->with($ids);

        $this->indexer->executeList($ids);
    }

    /**
     * @expectedException \Magento\CatalogRule\CatalogRuleException
     * @expectedExceptionMessage Could not rebuild index for undefined product
     */
    public function testExecuteRowWithEmptyId()
    {
        $this->indexer->executeRow(null);
    }

    public function testExecuteRow()
    {
        $id = 5;
        $this->indexer->expects($this->once())->method('doExecuteRow')->with($id);

        $this->indexer->executeRow($id);
    }
}
