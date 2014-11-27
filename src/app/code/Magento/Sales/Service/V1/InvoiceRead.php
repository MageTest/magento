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
namespace Magento\Sales\Service\V1;

use Magento\Sales\Service\V1\Action\InvoiceGet;
use Magento\Sales\Service\V1\Action\InvoiceList;
use Magento\Sales\Service\V1\Action\InvoiceCommentsList;
use Magento\Framework\Api\SearchCriteria;

/**
 * Class InvoiceRead
 */
class InvoiceRead implements InvoiceReadInterface
{
    /**
     * @var InvoiceGet
     */
    protected $invoiceGet;

    /**
     * @var InvoiceList
     */
    protected $invoiceList;

    /**
     * @var InvoiceCommentsList
     */
    protected $invoiceCommentsList;

    /**
     * @var InvoiceGetStatus
     */
    protected $invoiceGetStatus;

    /**
     * @param InvoiceGet $invoiceGet
     * @param InvoiceList $invoiceList
     * @param InvoiceCommentsList $invoiceCommentsList
     */
    public function __construct(
        InvoiceGet $invoiceGet,
        InvoiceList $invoiceList,
        InvoiceCommentsList $invoiceCommentsList
    ) {
        $this->invoiceGet = $invoiceGet;
        $this->invoiceList = $invoiceList;
        $this->invoiceCommentsList = $invoiceCommentsList;
    }

    /**
     * @param int $id
     * @return \Magento\Sales\Service\V1\Data\Invoice
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($id)
    {
        return $this->invoiceGet->invoke($id);
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteria $searchCriteria
     * @return \Magento\Framework\Api\SearchResults
     */
    public function search(SearchCriteria $searchCriteria)
    {
        return $this->invoiceList->invoke($searchCriteria);
    }

    /**
     * @param int $id
     * @return \Magento\Sales\Service\V1\Data\CommentSearchResults
     */
    public function commentsList($id)
    {
        return $this->invoiceCommentsList->invoke($id);
    }
}
