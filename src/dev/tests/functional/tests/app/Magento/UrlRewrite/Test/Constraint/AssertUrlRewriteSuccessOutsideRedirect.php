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

namespace Magento\UrlRewrite\Test\Constraint;

use Mtf\Client\Browser;
use Mtf\Constraint\AbstractConstraint;
use Magento\UrlRewrite\Test\Fixture\UrlRewrite;

/**
 * Class AssertUrlRewriteSuccessOutsideRedirect
 * Assert that outside redirect was success
 */
class AssertUrlRewriteSuccessOutsideRedirect extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert that outside redirect was success
     *
     * @param UrlRewrite $urlRewrite
     * @param Browser $browser
     * @param UrlRewrite|null $initialRewrite [optional]
     * @return void
     */
    public function processAssert(UrlRewrite $urlRewrite, Browser $browser, UrlRewrite $initialRewrite = null)
    {
        $urlRequestPath = $urlRewrite->hasData('request_path')
            ? $urlRewrite->getRequestPath()
            : $initialRewrite->getRequestPath();
        $urlTargetPath = $urlRewrite->hasData('target_path')
            ? $urlRewrite->getTargetPath()
            : $initialRewrite->getTargetPath();

        $browser->open($_ENV['app_frontend_url'] . $urlRequestPath);
        $browserUrl = $browser->getUrl();

        \PHPUnit_Framework_Assert::assertEquals(
            $browserUrl,
            $urlTargetPath,
            'URL rewrite redirect false.'
            . "\nExpected: " . $urlTargetPath
            . "\nActual: " . $browserUrl
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Custom outside URL rewrite redirect was success.';
    }
}
