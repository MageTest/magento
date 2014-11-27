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
namespace Magento\RecurringPayment\Block\Plugin;

use Magento\Checkout\Model\Session;
use Magento\RecurringPayment\Model\Quote\Filter;

class Payment
{
    /** @var Filter  */
    protected $filter;

    /** @var  Session */
    protected $session;

    /**
     * @param Session $session
     * @param Filter $filter
     */
    public function __construct(Session $session, Filter $filter)
    {
        $this->session = $session;
        $this->filter = $filter;
    }

    /**
     * Add hasRecurringItems option
     *
     * @param \Magento\Checkout\Block\Onepage\Payment $subject
     * @param array $result
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetOptions(\Magento\Checkout\Block\Onepage\Payment $subject, array $result)
    {
        $quote = $this->session->getQuote();
        $result['hasRecurringItems'] = $quote && $this->filter->hasRecurringItems($quote);
        return $result;
    }
}
