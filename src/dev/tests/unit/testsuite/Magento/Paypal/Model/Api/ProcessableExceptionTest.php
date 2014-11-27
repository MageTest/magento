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

namespace Magento\Paypal\Model\Api;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class ProcessableExceptionTest extends \PHPUnit_Framework_TestCase
{
    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Paypal\Model\Api\ProcessableException */
    protected $model;

    /**
     * @dataProvider getUserMessageDataProvider
     */
    public function testGetUserMessage($code, $msg)
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            'Magento\Paypal\Model\Api\ProcessableException',
            ['message' => $msg, 'code' => $code]
        );
        $this->assertEquals($msg, $this->model->getUserMessage());
    }

    /**
     * @return array
     */
    public function getUserMessageDataProvider()
    {
        return [
            [
                10001,
                "I'm sorry - but we were not able to process your payment. "
                . "Please try another payment method or contact us so we can assist you."
            ],
            [
                10417,
                "I'm sorry - but we were not able to process your payment. "
                . "Please try another payment method or contact us so we can assist you."
            ],
            [
                10537,
                "I'm sorry - but we are not able to complete your transaction. Please contact us so we can assist you."
            ],
            [
                10538,
                "I'm sorry - but we are not able to complete your transaction. Please contact us so we can assist you."
            ],
            [
                10539,
                "I'm sorry - but we are not able to complete your transaction. Please contact us so we can assist you."
            ],
            [10411, "something went wrong"]
        ];
    }
}
